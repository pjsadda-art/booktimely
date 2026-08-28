<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndustryController extends Controller
{
    /**
     * Display a listing of the resource, with optional search/status filter.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('industry manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = Industry::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('is_active', $request->status === 'active');
        }

        $industries = $query->orderBy('id')->get();

        return view('super-admin.industries.index', compact('industries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('industry manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:industries,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        Industry::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'is_system_default' => 0,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', __('Industry successfully created.'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Industry $industry)
    {
        if (!Auth::user()->isAbleTo('industry manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:industries,name,' . $industry->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        // Super Admin may edit descriptions but cannot rename the default three industries.
        if (!$industry->is_system_default) {
            $industry->name = $request->name;
        }
        $industry->description = $request->description;

        // A default industry cannot be deactivated; others follow the checkbox.
        if ($industry->is_system_default) {
            $industry->is_active = true;
        } else {
            $industry->is_active = $request->has('is_active') ? 1 : 0;
        }

        $industry->save();

        return redirect()->back()->with('success', __('Industry updated successfully!'));
    }

    /**
     * Toggle active/inactive state.
     */
    public function toggleStatus(Industry $industry)
    {
        if (!Auth::user()->isAbleTo('industry manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($industry->is_system_default) {
            return redirect()->back()->with('error', __('Default industries cannot be deactivated.'));
        }

        if ($industry->is_active && Business::where('industry_id', $industry->id)->exists()) {
            return redirect()->back()->with('error', __('Cannot deactivate an industry assigned to active tenants.'));
        }

        $industry->is_active = !$industry->is_active;
        $industry->save();

        return redirect()->back()->with('success', __('Industry status updated successfully!'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Industry $industry)
    {
        if (!Auth::user()->isAbleTo('industry manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($industry->is_system_default) {
            return redirect()->back()->with('error', __('Default industries cannot be deleted.'));
        }

        if (Business::where('industry_id', $industry->id)->exists()) {
            return redirect()->back()->with('error', __('Cannot delete an industry assigned to active tenants.'));
        }

        $industry->delete();

        return redirect()->back()->with('success', __('Industry successfully deleted.'));
    }
}
