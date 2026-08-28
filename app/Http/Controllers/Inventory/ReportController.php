<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\StockTransaction;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The three reports, all straightforward derivations of the two stock tables:
 *
 *   summary   — current balance per product per place (from the balance cache)
 *   ledger    — the transaction list, filtered (from the ledger)
 *   valuation — balance × cost
 *
 * The ledger report is a plain select precisely because a transfer is one row
 * rather than a matched pair, so nothing has to be reconciled to read it.
 */
class ReportController extends Controller
{
    public function summary(Request $request)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $place = ProductStock::parsePlace($request->input('place'));

        $rows = ProductStock::with('product')
            ->forTenant()
            ->when(!empty($place), function ($query) use ($place) {
                $query->where('entity_type', $place[0])->where('entity_id', $place[1]);
            })
            ->when(!$request->boolean('include_zero'), fn($query) => $query->where('quantity', '!=', 0))
            ->get()
            ->sortBy(fn($stock) => $stock->product->name ?? '');

        return view('inventory.reports.summary', [
            'rows' => $rows,
            'places' => ProductStock::placeOptions(),
        ]);
    }

    public function ledger(Request $request)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $place = ProductStock::parsePlace($request->input('place'));

        $transactions = StockTransaction::with(['product', 'performer'])
            ->forTenant()
            ->when($request->filled('product_id'), fn($query) => $query->where('product_id', $request->input('product_id')))
            ->when($request->filled('type'), fn($query) => $query->where('transaction_type', $request->input('type')))
            ->when($request->filled('from'), fn($query) => $query->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn($query) => $query->whereDate('created_at', '<=', $request->input('to')))
            ->when(!empty($place), function ($query) use ($place) {
                // A place matches when it is either end of the movement, so a
                // transfer shows up in both places' ledgers.
                $query->where(function ($inner) use ($place) {
                    $inner->where(function ($source) use ($place) {
                        $source->where('source_entity_type', $place[0])->where('source_entity_id', $place[1]);
                    })->orWhere(function ($destination) use ($place) {
                        $destination->where('destination_entity_type', $place[0])
                            ->where('destination_entity_id', $place[1]);
                    });
                });
            })
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('inventory.reports.ledger', [
            'transactions' => $transactions,
            'products' => Product::forTenant()->orderBy('name')->pluck('name', 'id'),
            'types' => StockService::TRANSACTION_TYPES,
            'places' => ProductStock::placeOptions(),
        ]);
    }

    public function valuation(Request $request)
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
            ->where('quantity', '!=', 0)
            ->get();

        // Valued at the product's current cost price. That is a deliberate
        // simplification over per-batch costing: the ledger records the unit
        // cost of each receipt, so a weighted-average or FIFO valuation can be
        // built on the same data later without a schema change.
        $rows = [];
        $total = 0.0;

        foreach ($stocks as $stock) {
            $product = $stock->product;

            if (empty($product)) {
                continue;
            }

            $value = round((float) $stock->quantity * (float) $product->cost_price, 2);
            $total += $value;

            $rows[] = [
                'product' => $product,
                'place' => $stock->placeName(),
                'quantity' => (float) $stock->quantity,
                'cost_price' => (float) $product->cost_price,
                'value' => $value,
            ];
        }

        usort($rows, fn($a, $b) => $b['value'] <=> $a['value']);

        return view('inventory.reports.valuation', [
            'rows' => $rows,
            'total' => round($total, 2),
            'places' => ProductStock::placeOptions(),
        ]);
    }
}
