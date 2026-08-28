<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Industry;
use App\Models\IndustryChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndustryController extends Controller
{
    /**
     * GET /api/admin/industries
     */
    public function adminIndex(Request $request)
    {
        if (!Auth::user()->isAbleTo('industry manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $query = Industry::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('is_active', $request->status === 'active');
        }

        return response()->json(['data' => $query->orderBy('id')->get()]);
    }

    /**
     * POST /api/admin/industries
     */
    public function adminStore(Request $request)
    {
        if (!Auth::user()->isAbleTo('industry manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:industries,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()->first()], 422);
        }

        $industry = Industry::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'is_system_default' => 0,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['message' => __('Industry successfully created.'), 'data' => $industry], 201);
    }

    /**
     * PUT /api/admin/industries/{id}
     */
    public function adminUpdate(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('industry manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $industry = Industry::findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:industries,name,' . $industry->id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()->first()], 422);
        }

        if (!$industry->is_system_default) {
            $industry->name = $request->name;
        }
        $industry->description = $request->description;

        if ($industry->is_system_default) {
            $industry->is_active = true;
        } elseif ($request->has('is_active')) {
            $industry->is_active = $request->boolean('is_active');
        }

        $industry->save();

        return response()->json(['message' => __('Industry updated successfully!'), 'data' => $industry]);
    }

    /**
     * DELETE /api/admin/industries/{id}
     */
    public function adminDestroy($id)
    {
        if (!Auth::user()->isAbleTo('industry manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $industry = Industry::findOrFail($id);

        if ($industry->is_system_default) {
            return response()->json(['error' => __('Default industries cannot be deleted.')], 422);
        }

        if (Business::where('industry_id', $industry->id)->exists()) {
            return response()->json(['error' => __('Cannot delete an industry assigned to active tenants.')], 422);
        }

        $industry->delete();

        return response()->json(['message' => __('Industry successfully deleted.')]);
    }

    /**
     * GET /api/tenant/industry
     */
    public function tenantShow()
    {
        $business = Business::find(getActiveBusiness());

        return response()->json([
            'data' => [
                'industry_id' => $business->industry_id ?? 1,
                'industry' => $business ? $business->industry : null,
            ],
        ]);
    }

    /**
     * PUT /api/tenant/industry
     */
    public function tenantUpdate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'industry_id' => 'required|exists:industries,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()->first()], 422);
        }

        $industry = Industry::find($request->industry_id);
        if (!$industry->is_active) {
            return response()->json(['error' => __('Selected industry is not active.')], 422);
        }

        $business = Business::find(getActiveBusiness());
        if (!$business) {
            return response()->json(['error' => __('Business not found.')], 404);
        }

        $oldIndustryId = $business->industry_id;

        $business->industry_id = $industry->id;
        $business->save();

        IndustryChangeLog::create([
            'business_id' => $business->id,
            'old_industry_id' => $oldIndustryId,
            'new_industry_id' => $industry->id,
            'changed_by' => Auth::id(),
        ]);

        return response()->json(['message' => __('Industry updated successfully!'), 'data' => $business]);
    }
}
