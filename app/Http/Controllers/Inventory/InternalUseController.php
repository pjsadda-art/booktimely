<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\OpenUnit;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductStock;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Open units and samples.
 *
 * The domain-specific corner of the module: a salon opens a bottle to use
 * across many clients, and that bottle leaves sellable stock the moment it is
 * opened even though it is not empty for weeks. So opening deducts stock and
 * starts a log row; closing just records that it ran out.
 *
 * Samples work identically and share the table, differing only in that they are
 * closed on creation — a sample handed to a customer has no second step.
 */
class InternalUseController extends Controller
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

        $kind = $request->input('kind', OpenUnit::OPEN_UNIT);

        if (!in_array($kind, [OpenUnit::OPEN_UNIT, OpenUnit::SAMPLE], true)) {
            $kind = OpenUnit::OPEN_UNIT;
        }

        $units = OpenUnit::with(['product', 'openedBy'])
            ->forTenant()
            ->where('kind', $kind)
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        // Only products flagged as decantable are offered for opening — a box of
        // gloves is not something you "open" in this sense.
        $products = Product::forTenant()
            ->where('is_active', 1)
            ->when($kind === OpenUnit::OPEN_UNIT, fn($query) => $query->where('is_openable', 1))
            ->orderBy('name')
            ->pluck('name', 'id');

        $places = ProductStock::placeOptions();

        return view('inventory.internal-use.index', compact('units', 'products', 'places', 'kind'));
    }

    /**
     * Open a container, or hand out a sample. Deducts stock now.
     */
    public function open(Request $request)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'product_id' => 'required|numeric',
            'place' => 'required|string',
            'kind' => 'required|in:' . OpenUnit::OPEN_UNIT . ',' . OpenUnit::SAMPLE,
            'quantity' => 'required|numeric|min:0.0001',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->errors()->first());
        }

        $product = Product::forTenant()->find($request->input('product_id'));

        if (empty($product)) {
            return redirect()->back()->with('error', __('Product not found.'));
        }

        $place = ProductStock::parsePlace($request->input('place'));

        if (empty($place)) {
            return redirect()->back()->with('error', __('Choose a valid location or warehouse.'));
        }

        try {
            $this->stock->open(
                $product,
                $place[0],
                $place[1],
                $request->input('kind'),
                (float) $request->input('quantity'),
                $request->input('remarks')
            );
        } catch (\RuntimeException $e) {
            // Covers the "not enough stock" case, whose message already names
            // the product, the place and both quantities.
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()->with('error', __('That could not be recorded.'));
        }

        return redirect()->back()->with('success', $request->input('kind') === OpenUnit::SAMPLE
            ? __('Sample recorded and stock deducted.')
            : __('Container opened and stock deducted.'));
    }

    /**
     * Mark an opened container empty. Moves no stock.
     */
    public function close($id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $unit = OpenUnit::forTenant()->find($id);

        if (empty($unit)) {
            return redirect()->back()->with('error', __('Record not found.'));
        }

        try {
            $this->stock->close($unit);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('Marked as finished. Stock was already deducted when it was opened.'));
    }
}
