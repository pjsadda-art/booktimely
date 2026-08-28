<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\Business;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Models\category;
use Illuminate\Routing\Controller;

class CategoryApiController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {

        $business = Business::find(getActiveBusiness());
        $categories = category::where('business_id', $business->id)->where('created_by', creatorId())->get();
        if ($categories->isNotEmpty()) {
            $categoryList = $categories->map(function ($value) {
                return [
                    'id' => $value->id,
                    'name' => $value->name,
                ];
            });

            return $this->success($categoryList);
        } else {
            return $this->error(['message' => 'Record not found!']);
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('api::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
               'name' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        $business = Business::find($request->business_id);

        $category                   = new category();
        $category->name             = $request->name;
        $category->business_id      = !empty($business) ? $business->id : 0;
        $category->created_by       = creatorId();
        $category->save();

        return $this->success(['message' => 'Category successfully created.']);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('api::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('api::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $category = category::find($id);

        $validator = \Validator::make(
            $request->all(),
            [
               'name' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        $category->name = $request->name;
        $category->save();
       
        return $this->success(['message' => 'Category successfully Updated.']);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $category = category::find($id);
        if(!empty($category))
        {
            $category->delete();
            return $this->success(['message' => 'Category successfully delete.']);
        }else{
            return $this->error(['message' => 'Category not found.']);
        }
    }
}
