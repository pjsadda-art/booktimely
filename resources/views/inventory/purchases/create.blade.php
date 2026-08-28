@extends('layouts.main')

@section('page-title'){{ __('New purchase') }}@endsection
@section('page-breadcrumb')
    {{ __('Purchases') }},{{ __('New') }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('inventory.purchases.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('Vendor') }}</label>
                            <select name="vendor_id" class="form-control">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($vendors as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('Deliver to') }}</label>
                            <select name="place" class="form-control" required>
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($places as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">{{ __('Order date') }}</label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">{{ __('Expected') }}</label>
                            <input type="date" name="expected_date" class="form-control">
                        </div>
                    </div>

                    <h6 class="mb-2">{{ __('Lines') }}</h6>
                    <div class="table-responsive mb-2">
                        <table class="table table-sm align-middle" id="purchase-lines">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th style="width:140px;">{{ __('Quantity') }}</th>
                                    <th style="width:140px;">{{ __('Unit cost') }}</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="add-line">
                        <i class="ti ti-plus"></i> {{ __('Add line') }}
                    </button>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Remarks') }}</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="alert alert-info">
                        {{ __('A new purchase is saved as a draft. It does not affect stock until you confirm it and then receive the goods — receiving can be partial.') }}
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.purchases.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Save draft') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        // Cost defaults to the product's own cost price, which is right far more
        // often than blank and is still editable per line.
        var products = @json($products);

        var body = document.querySelector('#purchase-lines tbody');
        var index = 0;

        function addLine() {
            var row = document.createElement('tr');
            var options = products.map(function (product) {
                return '<option value="' + product.id + '" data-cost="' + product.cost + '">' +
                    product.name.replace(/</g, '&lt;') + '</option>';
            }).join('');

            row.innerHTML =
                '<td><select name="items[' + index + '][product_id]" class="form-control line-product" required>' +
                '<option value="">' + @json(__('Select…')) + '</option>' + options + '</select></td>' +
                '<td><input type="number" step="0.0001" min="0.0001" name="items[' + index + '][quantity]" class="form-control" required></td>' +
                '<td><input type="number" step="0.0001" min="0" name="items[' + index + '][unit_cost]" class="form-control line-cost" value="0"></td>' +
                '<td><button type="button" class="btn btn-sm btn-outline-danger remove-line"><i class="ti ti-x"></i></button></td>';

            body.appendChild(row);
            index++;

            row.querySelector('.line-product').addEventListener('change', function (event) {
                var option = event.target.selectedOptions[0];
                row.querySelector('.line-cost').value = option ? (option.getAttribute('data-cost') || 0) : 0;
            });

            row.querySelector('.remove-line').addEventListener('click', function () {
                row.remove();
            });
        }

        document.getElementById('add-line').addEventListener('click', addLine);
        addLine();
    })();
</script>
@endpush
