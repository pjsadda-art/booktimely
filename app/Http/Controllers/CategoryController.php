<?php

namespace App\Http\Controllers;

use App\Models\category;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * There is no standalone category list page — categories are managed
     * from a tab on the business settings page. Redirect there (tab 5)
     * instead of rendering nothing, so this route is never a dead end.
     */
    public function index()
    {
        return redirect()->route('business.manage', getActiveBusiness())->with('tab', 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if(Auth::user()->isAbleTo('category create'))
        {
            $business = Business::find($request->business_id);
            return view('category.create',compact('business'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('category create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $business = Business::find($request->business_id);

            $category                   = new category();
            $category->name             = $request->name;
            $category->business_id      = !empty($business) ? $business->id : 0;
            $category->created_by       = creatorId();
            $category->save();
            $tab = 5;

            return redirect()->back()->with('success', __('category successfully created.'))->with('tab', $tab);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(category $category)
    {
        if(Auth::user()->isAbleTo('category edit'))
        {
            return view('category.edit',compact('category'));
        }
        else
        {
           return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, category $category)
    {
        if(Auth::user()->isAbleTo('category edit'))
        {

            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $category->name = $request->name;
            $category->save();
            $tab = 5;
            return redirect()->back()->with('success', __('Category updated successfully!'))->with('tab', $tab);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(category $category)
    {
        if(Auth::user()->isAbleTo('category delete'))
        {
            $category->delete();
            $tab = 5;
            return redirect()->back()->with('error', __('Category successfully delete.'))->with('tab', $tab);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
