<span style="display: flex;">
    <div class="action-btn me-2">
        <a href="#" class="btn btn-sm align-items-center cp_link bg-primary"
            data-link="{{ route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}"
            data-bs-toggle="tooltip" title="{{ __('Copy') }}"
            data-original-title="{{ __('Click to copy invoice link') }}">
            <i class="ti ti-file text-white"></i>
        </a>
    </div>
    @if (module_is_active('EInvoice'))
        @permission('download invoice')
            @include('einvoice::download.generate_invoice', ['invoice_id' => $invoice->id])
        @endpermission
    @endif
    <div class="action-btn me-2">
        <a href="#" class="btn btn-sm  align-items-center bg-info"
            data-url="{{ route('delivery-form.pdf', \Crypt::encrypt($invoice->id)) }}" data-ajax-popup="true"
            data-size="lg" data-bs-toggle="tooltip" title="{{ __('Invoice Delivery Form') }}"
            data-title="{{ __('Invoice Delivery Form') }}">
            <i class="ti ti-clipboard-list text-white"></i>
        </a>
    </div>

    {{-- Duplicate moved to the invoice view page's top-right action bar
         (modern-view.blade.php), after Copy Payment Link — this slot is now
         Credit Note, since customer complaints on a fully paid invoice are a
         far more frequent action here than duplicating an invoice. --}}
    @php
        $creditNoteCustomer = \App\Models\Customer::find($invoice->customer_id);
        $creditNoteLimit = $invoice->getTotal() - $invoice->invoiceTotalCustomerCreditNote();
    @endphp
    @if ($creditNoteLimit > 0 && $creditNoteCustomer && !$creditNoteCustomer->is_walkin)
        @permission('creditnote create')
            <div class="action-btn me-2">
                <a href="#" class="mx-3 btn btn-sm align-items-center bg-dark" data-bs-toggle="tooltip"
                    title="" data-bs-original-title="{{ __('Issue Credit Note') }}"
                    data-url="{{ route('invoice.credit-note.create', $invoice->id) }}"
                    data-ajax-popup="true" data-size="md" data-title="{{ __('Issue Credit Note') }}">
                    <i class="ti ti-cash text-white"></i>
                </a>
            </div>
        @endpermission
    @endif
    @permission('invoice show')
        <div class="action-btn me-2">
            <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}" class="mx-3 btn btn-sm align-items-center bg-warning"
                data-bs-toggle="tooltip" title="{{ __('View') }}">
                <i class="ti ti-eye  text-white"></i>
            </a>
        </div>
    @endpermission

    @if ($invoice->status != 4)
        @permission('invoice edit')
            <div class="action-btn me-2">
                <a href="{{ route('invoice.edit', \Crypt::encrypt($invoice->id)) }}"
                    class="mx-3 btn btn-sm  align-items-center bg-info" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Edit') }}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            </div>
        @endpermission

        @permission('invoice delete')
            <div class="action-btn">
                @if ($invoice->payments()->exists())
                    {{-- Has recorded payments — InvoiceController::destroy()
                         now rejects this anyway; disabled here so the reason
                         is visible before the click, not after. --}}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-danger disabled" aria-disabled="true"
                        data-bs-toggle="tooltip" title="" data-bs-original-title="{{ __('Has payments recorded — delete every payment first, then this invoice.') }}">
                        <i class="ti ti-trash text-white text-white"></i>
                    </a>
                @else
                    {{ Form::open(['route' => ['invoice.destroy', $invoice->id], 'class' => 'm-0']) }}
                    @method('DELETE')
                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                        data-bs-toggle="tooltip" title="" data-bs-original-title="{{__('Delete')}}" aria-label="{{__('Delete')}}"
                        data-confirm="{{ __('Are You Sure?') }}"
                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                        data-confirm-yes="delete-form-{{ $invoice->id }}">
                        <i class="ti ti-trash text-white text-white"></i>
                    </a>
                    {{ Form::close() }}
                @endif
            </div>
        @endpermission
    @endif

</span>
