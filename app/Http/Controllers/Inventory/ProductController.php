<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Brand;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\SubBrand;
use App\Models\Inventory\SubCategory;
use App\Models\Inventory\Unit;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
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

        $products = Product::with(['category', 'brand', 'unit'])
            ->forTenant()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->input('q');
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', '%' . $term . '%')
                        ->orWhere('sku', 'like', '%' . $term . '%');
                });
            })
            ->when($request->filled('category_id'), fn($query) => $query->where('category_id', $request->input('category_id')))
            ->when($request->filled('brand_id'), fn($query) => $query->where('brand_id', $request->input('brand_id')))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        // One query for every product's balance rather than one per row.
        $balances = ProductStock::forTenant()
            ->whereIn('product_id', $products->pluck('id')->all())
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id')
            ->all();

        $categories = Category::forTenant()->orderBy('name')->pluck('name', 'id');
        $brands = Brand::forTenant()->orderBy('name')->pluck('name', 'id');

        return view('inventory.products.index', compact('products', 'balances', 'categories', 'brands'));
    }

    public function create()
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return view('inventory.products.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->errors()->first());
        }

        $product = new Product($this->payload($request));
        $product->applyTenant()->save();

        return redirect()->route('inventory.products.index')->with('success', __('Product created.'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = Product::forTenant()->find($id);

        if (empty($product)) {
            return redirect()->route('inventory.products.index')->with('error', __('Product not found.'));
        }

        return view('inventory.products.edit', array_merge($this->formOptions(), compact('product')));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = Product::forTenant()->find($id);

        if (empty($product)) {
            return redirect()->route('inventory.products.index')->with('error', __('Product not found.'));
        }

        $validator = \Validator::make($request->all(), $this->rules($id));

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->errors()->first());
        }

        $product->fill($this->payload($request));
        $product->save();

        return redirect()->route('inventory.products.index')->with('success', __('Product updated.'));
    }

    /**
     * One product's balances and its movement history.
     */
    public function show($id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = Product::with(['category', 'brand', 'unit'])->forTenant()->find($id);

        if (empty($product)) {
            return redirect()->route('inventory.products.index')->with('error', __('Product not found.'));
        }

        $stocks = ProductStock::forTenant()->where('product_id', $product->id)->get();

        $transactions = StockTransaction::with('performer')
            ->forTenant()
            ->where('product_id', $product->id)
            ->orderByDesc('id')
            ->paginate(50);

        return view('inventory.products.show', compact('product', 'stocks', 'transactions'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = Product::forTenant()->find($id);

        if (empty($product)) {
            return redirect()->back()->with('error', __('Product not found.'));
        }

        // Deleting a product with movement history would orphan the ledger, so
        // it is deactivated instead — the history stays explicable.
        if (StockTransaction::forTenant()->where('product_id', $product->id)->exists()) {
            $product->is_active = 0;
            $product->save();

            return redirect()->back()->with(
                'success',
                __('This product has stock movements, so it has been deactivated rather than deleted. Its history is intact.')
            );
        }

        ProductStock::forTenant()->where('product_id', $product->id)->delete();
        $product->delete();

        return redirect()->back()->with('success', __('Product deleted.'));
    }

    /**
     * Rebuild a product's balances from the ledger — the answer to "this number
     * looks wrong". Deliberately a manual action, never automatic.
     */
    public function rebuild($id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = Product::forTenant()->find($id);

        if (empty($product)) {
            return redirect()->back()->with('error', __('Product not found.'));
        }

        $places = $this->stock->rebuildBalances($product);

        return redirect()->back()->with('success', __('Balances rebuilt from the ledger across :count places.', ['count' => $places]));
    }

    /* --------------------------------------------------------------------- */

    protected function formOptions(): array
    {
        return [
            'categories' => Category::forTenant()->orderBy('name')->pluck('name', 'id'),
            'subCategories' => SubCategory::forTenant()->orderBy('name')->get(),
            'brands' => Brand::forTenant()->orderBy('name')->pluck('name', 'id'),
            'subBrands' => SubBrand::forTenant()->orderBy('name')->get(),
            'units' => Unit::forTenant()->orderBy('name')->pluck('name', 'id'),
        ];
    }

    protected function rules($ignoreId = null): array
    {
        return [
            'name' => 'required|string|max:191',
            'sku' => 'nullable|string|max:64',
            'category_id' => 'nullable|numeric',
            'sub_category_id' => 'nullable|numeric',
            'brand_id' => 'nullable|numeric',
            'sub_brand_id' => 'nullable|numeric',
            'unit_id' => 'nullable|numeric',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ];
    }

    protected function payload(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'sku' => $request->input('sku'),
            'category_id' => $request->input('category_id') ?: null,
            'sub_category_id' => $request->input('sub_category_id') ?: null,
            'brand_id' => $request->input('brand_id') ?: null,
            'sub_brand_id' => $request->input('sub_brand_id') ?: null,
            'unit_id' => $request->input('unit_id') ?: null,
            'cost_price' => $request->input('cost_price') ?: 0,
            'sale_price' => $request->input('sale_price') ?: 0,
            'reorder_level' => $request->input('reorder_level') ?: 0,
            'is_openable' => $request->boolean('is_openable'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            'description' => $request->input('description'),
        ];
    }
}
