{{ Form::open(array('route' => array('invoice.payment.update', $invoice->id, $payment->id),'method'=>'post','enctype' => 'multipart/form-data', 'class'=>'needs-validation', 'novalidate')) }}
<div class="modal-body">
<div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}
            <div class="form-icon-user">
                {{ Form::date('date', $payment->date, array('class'=>'form-control','required'=>'required','placeholder'=>'Select Date')) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}
            <div class="form-icon-user">
                {{ Form::number('amount', $payment->amount, array('class' => 'form-control','required'=>'required','step'=>'0.01','max' => ($invoice->getDue() + $payment->amount))) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('payment_type', __('Pay Type'),['class'=>'form-label']) }}
            @if(isset($pay_types) && count($pay_types) > 0)
                {{ Form::select('payment_type', $pay_types, $current_pay_type_id ?? null, array('class' => 'form-control','required'=>'required','placeholder'=>'Select Pay Type')) }}
            @else
                <select class="form-control" disabled>
                    <option value="">{{ __('No pay types found') }}</option>
                </select>
                <small class="text-danger d-block mt-1">
                    {{ __('Please create at least one Pay Type from') }}
                    <a href="{{ route('invoice-pay-type.index') }}" target="_blank"><b>{{ __('Invoice Pay Types') }}</b></a>.
                </small>
            @endif
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}
            <div class="form-icon-user">
                {{ Form::tel('reference', $payment->reference, array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Reference')) }}
            </div>
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {{ Form::textarea('description', $payment->description, array('class' => 'form-control','rows'=>3, 'placeholder'=>'Enter Description')) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => 'form-label']) }}
            @if(!empty($payment->add_receipt))
                <div class="mb-2">
                    <a href="{{ get_file($payment->add_receipt) }}" target="_blank" class="btn btn-sm btn-secondary">
                        <i class="ti ti-file"></i> {{ __('View current receipt') }}
                    </a>
                </div>
            @endif
            <div class="choose-files">
                <label for="add_receipt">
                    <div class="bg-primary"><i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                    <input type="file" class="form-control file" name="add_receipt" id="add_receipt"
                        onchange="document.getElementById('blah-edit').src = window.URL.createObjectURL(this.files[0])"
                        data-filename="add_receipt">
                    <img id="blah-edit" width="100" src="" />
                </label>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{ Form::close() }}
