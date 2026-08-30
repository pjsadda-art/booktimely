<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\InvoicePayTypeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoicePayTypeGroupController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('invoice pay type group manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = InvoicePayTypeGroup::withCount('payTypes');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('is_active', $request->status === 'active');
        }

        $payTypeGroups = $query->orderBy('id')->get();

        return view('super-admin.invoice-pay-type-groups.index', compact('payTypeGroups'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('invoice pay type group manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:invoice_pay_type_groups,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        InvoicePayTypeGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'is_system_default' => 0,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', __('Pay type group successfully created.'));
    }

    public function update(Request $request, InvoicePayTypeGroup $invoicePayTypeGroup)
    {
        if (!Auth::user()->isAbleTo('invoice pay type group manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:invoice_pay_type_groups,name,' . $invoicePayTypeGroup->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        // Super Admin may edit descriptions but cannot rename the default groups.
        if (!$invoicePayTypeGroup->is_system_default) {
            $invoicePayTypeGroup->name = $request->name;
        }
        $invoicePayTypeGroup->description = $request->description;

        if ($invoicePayTypeGroup->is_system_default) {
            $invoicePayTypeGroup->is_active = true;
        } else {
            $invoicePayTypeGroup->is_active = $request->has('is_active') ? 1 : 0;
        }

        $invoicePayTypeGroup->save();

        return redirect()->back()->with('success', __('Pay type group updated successfully!'));
    }

    public function toggleStatus(InvoicePayTypeGroup $invoicePayTypeGroup)
    {
        if (!Auth::user()->isAbleTo('invoice pay type group manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($invoicePayTypeGroup->is_system_default) {
            return redirect()->back()->with('error', __('Default pay type groups cannot be deactivated.'));
        }

        if ($invoicePayTypeGroup->is_active && $invoicePayTypeGroup->payTypes()->exists()) {
            return redirect()->back()->with('error', __('Cannot deactivate a group assigned to existing pay types.'));
        }

        $invoicePayTypeGroup->is_active = !$invoicePayTypeGroup->is_active;
        $invoicePayTypeGroup->save();

        return redirect()->back()->with('success', __('Pay type group status updated successfully!'));
    }

    public function destroy(InvoicePayTypeGroup $invoicePayTypeGroup)
    {
        if (!Auth::user()->isAbleTo('invoice pay type group manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($invoicePayTypeGroup->is_system_default) {
            return redirect()->back()->with('error', __('Default pay type groups cannot be deleted.'));
        }

        if ($invoicePayTypeGroup->payTypes()->exists()) {
            return redirect()->back()->with('error', __('Cannot delete a group assigned to existing pay types.'));
        }

        $invoicePayTypeGroup->delete();

        return redirect()->back()->with('success', __('Pay type group successfully deleted.'));
    }
}
