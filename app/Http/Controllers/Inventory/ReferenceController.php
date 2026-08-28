<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Brand;
use App\Models\Inventory\Category;
use App\Models\Inventory\SubBrand;
use App\Models\Inventory\SubCategory;
use App\Models\Inventory\Unit;
use App\Models\Inventory\Vendor;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The module's reference data: categories, sub-categories, brands, sub-brands,
 * units, warehouses and vendors.
 *
 * Seven near-identical CRUDs, so they share one controller driven by the
 * registry below rather than being copied seven times. Adding an eighth lookup
 * table is a new entry in TYPES and a form partial — no new controller, no new
 * routes.
 */
class ReferenceController extends Controller
{
    /**
     * Parent slug, label and foreign-key column are all stated explicitly rather
     * than derived from the type slug. Deriving them looked tidier but was
     * wrong: "categories" singularised by trimming a trailing "s" gives
     * "categorie", so the sub-category form would have posted `categorie_id`
     * and silently saved nothing.
     *
     * @var array<string,array{model:class-string,label:string,plural:string,fields:array<int,string>,parent:?string,parent_label:?string,parent_column:?string}>
     */
    public const TYPES = [
        'categories' => [
            'model' => Category::class,
            'label' => 'Category',
            'plural' => 'Categories',
            'fields' => ['name', 'description'],
            'parent' => null,
            'parent_label' => null,
            'parent_column' => null,
        ],
        'sub-categories' => [
            'model' => SubCategory::class,
            'label' => 'Sub-category',
            'plural' => 'Sub-categories',
            'fields' => ['name'],
            'parent' => 'categories',
            'parent_label' => 'Category',
            'parent_column' => 'category_id',
        ],
        'brands' => [
            'model' => Brand::class,
            'label' => 'Brand',
            'plural' => 'Brands',
            'fields' => ['name'],
            'parent' => null,
            'parent_label' => null,
            'parent_column' => null,
        ],
        'sub-brands' => [
            'model' => SubBrand::class,
            'label' => 'Sub-brand',
            'plural' => 'Sub-brands',
            'fields' => ['name'],
            'parent' => 'brands',
            'parent_label' => 'Brand',
            'parent_column' => 'brand_id',
        ],
        'units' => [
            'model' => Unit::class,
            'label' => 'Unit',
            'plural' => 'Units',
            'fields' => ['name', 'short_name'],
            'parent' => null,
            'parent_label' => null,
            'parent_column' => null,
        ],
        'warehouses' => [
            'model' => Warehouse::class,
            'label' => 'Warehouse',
            'plural' => 'Warehouses',
            'fields' => ['name', 'address', 'city', 'postcode'],
            'parent' => null,
            'parent_label' => null,
            'parent_column' => null,
        ],
        'vendors' => [
            'model' => Vendor::class,
            'label' => 'Vendor',
            'plural' => 'Vendors',
            'fields' => ['name', 'contact_name', 'email', 'phone', 'address'],
            'parent' => null,
            'parent_label' => null,
            'parent_column' => null,
        ],
    ];

    public function index($type)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $definition = $this->definition($type);

        if (empty($definition)) {
            return redirect()->route('inventory.products.index')->with('error', __('Unknown reference type.'));
        }

        $model = $definition['model'];
        $rows = $model::forTenant()->orderBy('name')->paginate(25);
        $parents = $this->parentOptions($definition);

        return view('inventory.reference.index', compact('type', 'definition', 'rows', 'parents'));
    }

    public function store(Request $request, $type)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $definition = $this->definition($type);

        if (empty($definition)) {
            return redirect()->back()->with('error', __('Unknown reference type.'));
        }

        $validator = \Validator::make($request->all(), $this->rules($definition));

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $model = $definition['model'];
        $row = new $model($this->payload($request, $definition));
        $row->applyTenant()->save();

        return redirect()->back()->with('success', __(':label created.', ['label' => __($definition['label'])]));
    }

    public function update(Request $request, $type, $id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $definition = $this->definition($type);

        if (empty($definition)) {
            return redirect()->back()->with('error', __('Unknown reference type.'));
        }

        $model = $definition['model'];
        $row = $model::forTenant()->find($id);

        if (empty($row)) {
            return redirect()->back()->with('error', __('Record not found.'));
        }

        $validator = \Validator::make($request->all(), $this->rules($definition));

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $row->fill($this->payload($request, $definition));
        $row->save();

        return redirect()->back()->with('success', __(':label updated.', ['label' => __($definition['label'])]));
    }

    public function destroy($type, $id)
    {
        if (!Auth::user()->isAbleTo('inventory manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $definition = $this->definition($type);

        if (empty($definition)) {
            return redirect()->back()->with('error', __('Unknown reference type.'));
        }

        $model = $definition['model'];
        $row = $model::forTenant()->find($id);

        if (empty($row)) {
            return redirect()->back()->with('error', __('Record not found.'));
        }

        // Refuse rather than orphan: a product pointing at a deleted category
        // renders as a blank column that nobody can explain.
        if ($this->isReferenced($type, $id)) {
            return redirect()->back()->with(
                'error',
                __('That :label is still in use and cannot be deleted.', ['label' => strtolower(__($definition['label']))])
            );
        }

        $row->delete();

        return redirect()->back()->with('success', __(':label deleted.', ['label' => __($definition['label'])]));
    }

    /* --------------------------------------------------------------------- */
    /* Internals                                                             */
    /* --------------------------------------------------------------------- */

    protected function definition($type): ?array
    {
        return self::TYPES[$type] ?? null;
    }

    protected function rules(array $definition): array
    {
        $rules = ['name' => 'required|string|max:191'];

        if (!empty($definition['parent'])) {
            $rules[$this->parentColumn($definition)] = 'required|numeric';
        }

        if (in_array('email', $definition['fields'], true)) {
            $rules['email'] = 'nullable|email|max:191';
        }

        return $rules;
    }

    protected function payload(Request $request, array $definition): array
    {
        $payload = $request->only($definition['fields']);

        if (!empty($definition['parent'])) {
            $column = $this->parentColumn($definition);
            $payload[$column] = $request->input($column);
        }

        return $payload;
    }

    protected function parentColumn(array $definition): string
    {
        return $definition['parent_column'];
    }

    protected function parentOptions(array $definition): array
    {
        if (empty($definition['parent'])) {
            return [];
        }

        $parentModel = self::TYPES[$definition['parent']]['model'];

        return $parentModel::forTenant()->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * Whether anything still points at this row.
     */
    protected function isReferenced($type, $id): bool
    {
        $productColumns = [
            'categories' => 'category_id',
            'sub-categories' => 'sub_category_id',
            'brands' => 'brand_id',
            'sub-brands' => 'sub_brand_id',
            'units' => 'unit_id',
        ];

        if (isset($productColumns[$type])
            && \App\Models\Inventory\Product::forTenant()->where($productColumns[$type], $id)->exists()) {
            return true;
        }

        // A parent is also blocked by its own children, not only by products.
        if ($type === 'categories') {
            return SubCategory::forTenant()->where('category_id', $id)->exists();
        }

        if ($type === 'brands') {
            return SubBrand::forTenant()->where('brand_id', $id)->exists();
        }

        if ($type === 'warehouses') {
            return \App\Models\Inventory\ProductStock::forTenant()
                ->where('entity_type', \App\Models\Inventory\ProductStock::WAREHOUSE)
                ->where('entity_id', $id)
                ->where('quantity', '>', 0)
                ->exists();
        }

        if ($type === 'vendors') {
            return \App\Models\Inventory\Purchase::forTenant()->where('vendor_id', $id)->exists();
        }

        return false;
    }
}
