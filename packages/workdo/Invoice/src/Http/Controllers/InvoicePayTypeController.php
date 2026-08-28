<?php

namespace Workdo\Invoice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Invoice\Entities\InvoicePayType;

class InvoicePayTypeController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAbleTo('invoice pay type manage')) {
            $pay_types = InvoicePayType::where('business_id', getActiveBusiness())
                ->orderBy('id', 'desc')
                ->get();

            return view('invoice::invoice_pay_type.index', compact('pay_types'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function create()
    {
        if (Auth::user()->isAbleTo('invoice pay type create')) {
            return view('invoice::invoice_pay_type.create');
        }

        return response()->json(['error' => __('Permission denied.')], 401);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('invoice pay type create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $payType              = new InvoicePayType();
        $payType->name        = $request->name;
        $payType->description = $request->description;
        $payType->is_active   = $request->has('is_active') ? 1 : 0;
        $payType->created_by  = creatorId();
        $payType->business_id = getActiveBusiness();
        $payType->save();

        return redirect()->route('invoice-pay-type.index')
            ->with('success', __('Pay type successfully created.'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isAbleTo('invoice pay type edit')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $pay_type = InvoicePayType::where('business_id', getActiveBusiness())->findOrFail($id);

        return view('invoice::invoice_pay_type.edit', compact('pay_type'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('invoice pay type edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $pay_type = InvoicePayType::where('business_id', getActiveBusiness())->findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $pay_type->name        = $request->name;
        $pay_type->description = $request->description;
        $pay_type->is_active   = $request->has('is_active') ? 1 : 0;
        $pay_type->save();

        return redirect()->route('invoice-pay-type.index')
            ->with('success', __('Pay type successfully updated.'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('invoice pay type delete')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $pay_type = InvoicePayType::where('business_id', getActiveBusiness())->findOrFail($id);
        $pay_type->delete();

        return redirect()->route('invoice-pay-type.index')
            ->with('success', __('Pay type successfully deleted.'));
    }
}
