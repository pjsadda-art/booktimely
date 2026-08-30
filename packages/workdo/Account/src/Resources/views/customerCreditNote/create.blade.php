
{{-- Loaded into #commonModal via data-ajax-popup (see public/js/custom.js).
     Posts to the working CustomerCreditNotesController::store() route —
     NOT invoice.credit.storenote (CreditNoteController), which reads
     customers.customer_id / customers.credit_note_balance columns that
     were never created and always fails. --}}
{{ Form::open(['route' => ['invoice.credit-note.issue', $invoice_id], 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12 mb-2">
            {{ Form::label('invoice_display', __('Invoice'), ['class' => 'form-label']) }}
            <input type="text" class="form-control" disabled
                value="{{ !empty($invoiceDue) ? \Workdo\Invoice\Entities\Invoice::invoiceNumberFormat($invoiceDue->invoice_id, $invoiceDue->created_by, $invoiceDue->workspace ?? null) : '' }}">
        </div>
        <div class="form-group col-md-6">
            <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
            {{ Form::date('date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
            {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0.01', 'max' => number_format($creditLimit, 2, '.', '')]) }}
            <small class="text-muted">{{ __('Max') }}: {{ currency_format_with_sym($creditLimit) }}</small>
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description (Reason)'), ['class' => 'form-label']) }}
            {!! Form::textarea('description', '', ['class'=>'form-control','rows'=>'3',
            'placeholder'=>__('Enter Description')]) !!}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Issue Credit Note')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
