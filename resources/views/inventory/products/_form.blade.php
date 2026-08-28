{{-- Shared by create and edit. $product is null when creating. --}}
@php $product = $product ?? null; @endphp

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">{{ __('Name') }}</label>
        <input type="text" name="name" class="form-control" required
            value="{{ old('name', $product->name ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('SKU') }}</label>
        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Category') }}</label>
        <select name="category_id" id="product-category" class="form-control">
            <option value="">{{ __('None') }}</option>
            @foreach ($categories as $id => $name)
                <option value="{{ $id }}" {{ old('category_id', $product->category_id ?? '') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
        @if (empty($categories) || $categories->isEmpty())
            <small class="form-text text-muted">
                {{ __('No inventory categories yet.') }}
                <a href="{{ route('inventory.reference.index', ['type' => 'categories']) }}" target="_blank">{{ __('Add one here') }}</a>
                {{ __('(separate from the appointment/service categories in Business Settings).') }}
            </small>
        @endif
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Sub-category') }}</label>
        {{-- Filtered client-side by the chosen category, so a sub-category can
             never be saved against the wrong parent. --}}
        <select name="sub_category_id" id="product-sub-category" class="form-control">
            <option value="">{{ __('None') }}</option>
            @foreach ($subCategories as $subCategory)
                <option value="{{ $subCategory->id }}" data-parent="{{ $subCategory->category_id }}"
                    {{ old('sub_category_id', $product->sub_category_id ?? '') == $subCategory->id ? 'selected' : '' }}>
                    {{ $subCategory->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Brand') }}</label>
        <select name="brand_id" id="product-brand" class="form-control">
            <option value="">{{ __('None') }}</option>
            @foreach ($brands as $id => $name)
                <option value="{{ $id }}" {{ old('brand_id', $product->brand_id ?? '') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Sub-brand') }}</label>
        <select name="sub_brand_id" id="product-sub-brand" class="form-control">
            <option value="">{{ __('None') }}</option>
            @foreach ($subBrands as $subBrand)
                <option value="{{ $subBrand->id }}" data-parent="{{ $subBrand->brand_id }}"
                    {{ old('sub_brand_id', $product->sub_brand_id ?? '') == $subBrand->id ? 'selected' : '' }}>
                    {{ $subBrand->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">{{ __('Unit') }}</label>
        <select name="unit_id" class="form-control">
            <option value="">{{ __('None') }}</option>
            @foreach ($units as $id => $name)
                <option value="{{ $id }}" {{ old('unit_id', $product->unit_id ?? '') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">{{ __('Cost price') }}</label>
        <input type="number" step="0.0001" min="0" name="cost_price" class="form-control"
            value="{{ old('cost_price', $product->cost_price ?? 0) }}">
        <small class="text-muted">{{ __('Drives the valuation report.') }}</small>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">{{ __('Sale price') }}</label>
        <input type="number" step="0.0001" min="0" name="sale_price" class="form-control"
            value="{{ old('sale_price', $product->sale_price ?? 0) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">{{ __('Reorder level') }}</label>
        <input type="number" step="0.0001" min="0" name="reorder_level" class="form-control"
            value="{{ old('reorder_level', $product->reorder_level ?? 0) }}">
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">{{ __('Description') }}</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div class="col-md-6 mb-3">
        <div class="form-check">
            <input type="checkbox" name="is_openable" value="1" class="form-check-input" id="is_openable"
                {{ old('is_openable', $product->is_openable ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_openable">{{ __('Can be opened for salon use') }}</label>
        </div>
        <small class="text-muted">
            {{ __('A container that is decanted across many clients. It leaves sellable stock when opened, not when empty.') }}
        </small>
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
        </div>
    </div>
</div>

<script>
    // Keep each child dropdown consistent with its parent.
    (function () {
        function bind(parentId, childId) {
            var parent = document.getElementById(parentId);
            var child = document.getElementById(childId);
            if (!parent || !child) { return; }

            function filter() {
                var selected = parent.value;
                Array.prototype.forEach.call(child.options, function (option) {
                    if (!option.value) { return; }
                    var matches = option.getAttribute('data-parent') === selected;
                    option.hidden = !matches;
                    // A child left selected under a different parent would be
                    // submitted as a mismatched pair, so clear it.
                    if (!matches && option.selected) { child.value = ''; }
                });
            }

            parent.addEventListener('change', filter);
            filter();
        }

        bind('product-category', 'product-sub-category');
        bind('product-brand', 'product-sub-brand');
    })();
</script>
