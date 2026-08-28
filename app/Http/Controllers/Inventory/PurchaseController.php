<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchaseItem;
use App\Models\Inventory\Vendor;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Purchasing: draft → confirm → receive.
 *
 * Only the receive step touches stock, and it may be partial. Confirm and
 * receive are kept as separate steps because collapsing them is what makes a
 * partial delivery impossible to represent.
 */
class PurchaseController extends Controller
{
    protected StockService $stock;

    public function __construct(StockService $stock)
    {
        $this->stock = $stock;
    }

    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $purchases = Purchase::with('vendor')
            ->forTenant()
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('inventory.purchases.index', [
            'purchases' => $purchases,
            'statuses' => Purchase::statuses(),
        ]);
    }

    public function create()
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('inventory.purchases.create', [
            'vendors' => Vendor::forTenant()->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'places' => ProductStock::placeOptions(),
            // Shaped here rather than in the view: the line editor needs each
            // product's cost price to prefill the unit cost, and building that
            // in Blade would mean an expression the @json directive cannot parse.
            'products' => Product::forTenant()
                ->where('is_active', 1)
                ->orderBy('name')
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'cost' => (float) $product->cost_price,
                    ];
                })
                ->values()
                ->all(),
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'vendor_id' => 'nullable|numeric',
            'place' => 'required|string',
            'purchase_date' => 'nullable|date',
            'expected_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|numeric',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->errors()->first());
        }

        $place = ProductStock::parsePlace($request->input('place'));

        if (empty($place)) {
            return redirect()->back()->withInput()->with('error', __('Choose a valid location or warehouse.'));
        }

        try {
            $purchase = DB::transaction(function () use ($request, $place) {
                $purchase = new Purchase([
                    'purchase_number' => $this->nextNumber(),
                    'vendor_id' => $request->input('vendor_id') ?: null,
                    'entity_type' => $place[0],
                    'entity_id' => $place[1],
                    'status' => Purchase::DRAFT,
                    'purchase_date' => $request->input('purchase_date') ?: now()->toDateString(),
                    'expected_date' => $request->input('expected_date'),
                    'remarks' => $request->input('remarks'),
                ]);

                $purchase->applyTenant()->save();

                $this->syncItems($purchase, $request->input('items'));

                return $purchase;
            });
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()->withInput()->with('error', __('The purchase could not be saved.'));
        }

        return redirect()->route('inventory.purchases.show', $purchase->id)
            ->with('success', __('Purchase created as a draft.'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $purchase = Purchase::with(['items.product', 'vendor'])->forTenant()->find($id);

        if (empty($purchase)) {
            return redirect()->route('inventory.purchases.index')->with('error', __('Purchase not found.'));
        }

        return view('inventory.purchases.show', compact('purchase'));
    }

    /**
     * Lock the order. No stock moves — that is the receive step.
     */
    public function confirm($id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $purchase = Purchase::forTenant()->find($id);

        if (empty($purchase)) {
            return redirect()->back()->with('error', __('Purchase not found.'));
        }

        if ($purchase->status !== Purchase::DRAFT) {
            return redirect()->back()->with('error', __('Only a draft purchase can be confirmed.'));
        }

        $purchase->status = Purchase::CONFIRMED;
        $purchase->save();

        return redirect()->back()->with('success', __('Purchase confirmed. Receive the goods when they arrive.'));
    }

    /**
     * Receive some or all of a confirmed order.
     *
     * The quantities posted are per line, so a delivery that brings four of six
     * boxes is recorded as exactly that and the order stays open.
     */
    public function receive(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $purchase = Purchase::with('items')->forTenant()->find($id);

        if (empty($purchase)) {
            return redirect()->back()->with('error', __('Purchase not found.'));
        }

        if ($purchase->status === Purchase::DRAFT) {
            return redirect()->back()->with('error', __('Confirm the purchase before receiving it.'));
        }

        if ($purchase->status === Purchase::RECEIVED) {
            return redirect()->back()->with('error', __('This purchase has already been fully received.'));
        }

        $received = (array) $request->input('received', []);
        $moved = 0;

        try {
            DB::transaction(function () use ($purchase, $received, &$moved) {
                foreach ($purchase->items as $item) {
                    $quantity = (float) ($received[$item->id] ?? 0);

                    if ($quantity <= 0) {
                        continue;
                    }

                    $this->stock->receivePurchaseLine($purchase, $item, $quantity);
                    $moved++;
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()->with('error', __('The goods could not be received.'));
        }

        if ($moved === 0) {
            return redirect()->back()->with('error', __('Enter a quantity against at least one line.'));
        }

        $purchase->refresh();

        return redirect()->back()->with('success', $purchase->status === Purchase::RECEIVED
            ? __('Purchase fully received and stock updated.')
            : __('Partial delivery received. The remaining lines are still outstanding.'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $purchase = Purchase::forTenant()->find($id);

        if (empty($purchase)) {
            return redirect()->back()->with('error', __('Purchase not found.'));
        }

        // A confirmed or received order has either been sent to a vendor or has
        // already moved stock, so neither can be deleted away.
        if ($purchase->status !== Purchase::DRAFT) {
            return redirect()->back()->with('error', __('Only a draft purchase can be deleted.'));
        }

        $purchase->items()->delete();
        $purchase->delete();

        return redirect()->route('inventory.purchases.index')->with('success', __('Draft purchase deleted.'));
    }

    /* --------------------------------------------------------------------- */

    protected function syncItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $line) {
            if (empty($line['product_id']) || empty($line['quantity'])) {
                continue;
            }

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $line['product_id'],
                'quantity' => (float) $line['quantity'],
                'received_quantity' => 0,
                'unit_cost' => (float) ($line['unit_cost'] ?? 0),
            ]);
        }

        $purchase->load('items');
        $purchase->recalculateTotal();
    }

    protected function nextNumber(): string
    {
        $prefix = company_setting('purchase_prefix', null, getActiveBusiness()) ?: '#PUR';

        return $prefix . str_pad((string) (Purchase::forTenant()->count() + 1), 5, '0', STR_PAD_LEFT);
    }
}
