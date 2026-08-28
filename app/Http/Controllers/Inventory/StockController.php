<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductStock;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The three manual stock movements: opening balance, adjustment and transfer.
 *
 * Each is a thin wrapper — validate, resolve the place, hand off to
 * StockService, report what happened. Nothing here touches a balance directly.
 */
class StockController extends Controller
{
    protected StockService $stock;

    public function __construct(StockService $stock)
    {
        $this->stock = $stock;
    }

    /**
     * Current balances across every product and place.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $place = ProductStock::parsePlace($request->input('place'));

        $stocks = ProductStock::with('product')
            ->forTenant()
            ->when(!empty($place), function ($query) use ($place) {
                $query->where('entity_type', $place[0])->where('entity_id', $place[1]);
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->input('q');
                $query->whereHas('product', function ($inner) use ($term) {
                    $inner->where('name', 'like', '%' . $term . '%')
                        ->orWhere('sku', 'like', '%' . $term . '%');
                });
            })
            ->orderByDesc('quantity')
            ->paginate(50)
            ->withQueryString();

        $places = ProductStock::placeOptions();

        return view('inventory.stock.index', compact('stocks', 'places'));
    }

    /**
     * The movement form. `action` selects which of the three is shown.
     */
    public function form(Request $request, $action)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (!in_array($action, ['opening', 'adjust', 'transfer'], true)) {
            return redirect()->route('inventory.stock.index')->with('error', __('Unknown stock action.'));
        }

        $products = Product::forTenant()->where('is_active', 1)->orderBy('name')->pluck('name', 'id');
        $places = ProductStock::placeOptions();

        return view('inventory.stock.form', compact('action', 'products', 'places'));
    }

    public function openingBalance(Request $request)
    {
        return $this->run($request, function (Product $product) use ($request) {
            $place = $this->place($request->input('place'));

            $this->stock->openingBalance(
                $product,
                $place[0],
                $place[1],
                (float) $request->input('quantity'),
                $request->input('remarks')
            );

            return __('Opening balance recorded.');
        }, [
            'place' => 'required|string',
            'quantity' => 'required|numeric|min:0.0001',
        ]);
    }

    public function adjust(Request $request)
    {
        return $this->run($request, function (Product $product) use ($request) {
            $place = $this->place($request->input('place'));

            // The form asks for a direction and a magnitude; the service takes a
            // signed delta. Converting here keeps "minus 3" out of the UI.
            $quantity = (float) $request->input('quantity');
            $delta = $request->input('direction') === 'decrease' ? -$quantity : $quantity;

            $this->stock->adjust($product, $place[0], $place[1], $delta, (string) $request->input('reason'));

            return __('Stock adjusted.');
        }, [
            'place' => 'required|string',
            'quantity' => 'required|numeric|min:0.0001',
            'direction' => 'required|in:increase,decrease',
            'reason' => 'required|string|max:500',
        ]);
    }

    public function transfer(Request $request)
    {
        return $this->run($request, function (Product $product) use ($request) {
            $source = $this->place($request->input('source'));
            $destination = $this->place($request->input('destination'));

            $this->stock->transfer(
                $product,
                $source[0],
                $source[1],
                $destination[0],
                $destination[1],
                (float) $request->input('quantity'),
                $request->input('remarks')
            );

            return __('Stock transferred.');
        }, [
            'source' => 'required|string',
            'destination' => 'required|string',
            'quantity' => 'required|numeric|min:0.0001',
        ]);
    }

    /* --------------------------------------------------------------------- */

    /**
     * Shared plumbing: permission, validation, product lookup, error reporting.
     *
     * StockService throws RuntimeException for every rule it enforces — not
     * enough stock, same source and destination, unknown movement type — so
     * those messages are already written for a human and are shown as-is.
     */
    protected function run(Request $request, callable $operation, array $rules)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), array_merge(['product_id' => 'required|numeric'], $rules));

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->errors()->first());
        }

        $product = Product::forTenant()->find($request->input('product_id'));

        if (empty($product)) {
            return redirect()->back()->withInput()->with('error', __('Product not found.'));
        }

        try {
            $message = $operation($product);
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()->withInput()->with('error', __('The stock movement could not be saved.'));
        }

        return redirect()->route('inventory.stock.index')->with('success', $message);
    }

    /**
     * Resolve a "type:id" place key, rejecting anything that is not a real place.
     *
     * @return array{0:string,1:int}
     */
    protected function place($key): array
    {
        $place = ProductStock::parsePlace($key);

        if (empty($place)) {
            throw new \RuntimeException(__('Choose a valid location or warehouse.'));
        }

        return $place;
    }
}
