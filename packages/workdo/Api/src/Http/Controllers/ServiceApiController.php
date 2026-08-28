<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\category;
use App\Models\Service;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ServiceApiController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $active_business = $user->active_business;

        $categoryData = [];
        $categories = category::where('business_id', $active_business)->get()->toArray();
        foreach ($categories as $key => $category) {
            $categoryData[$key]['id'] = $category['id'];
            $categoryData[$key]['name'] = $category['name'];
        }

        $serviceData = Service::where('business_id', $active_business)->paginate(10);

        if ($serviceData->isNotEmpty()) {
            $serviceList = $serviceData->map(function ($value) {
                return [
                    'id' => $value->id,
                    'name' => $value->name,
                    'category' => $value->Category->name,
                    'image' => check_file($value->image) ? get_file($value->image) : get_file('uploads/default/avatar.png') ,
                    'price' => currency_format_with_sym($value->price,$value->created_by, $value->business_id),
                    'duration' => $value->duration,
                    'description' => $value->description ? $value->description : ''
                ];
            });

            return $this->success([
                    'category_list' => $categoryData,
                    'service_list' => $serviceList,
                    'total' => $serviceData->total(),
                    'per_page' => $serviceData->perPage(),
                    'current_page' => $serviceData->currentPage(),
                    'last_page' => $serviceData->lastPage(),
                    'next_page_url' => $serviceData->nextPageUrl(),
                    'prev_page_url' => $serviceData->previousPageUrl(),
            ]);
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
        $user = Auth::user();
        $active_business = $user->active_business;

        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'category' => 'required',
                'price' => 'required',
                'duration' => 'required',
                'service_image' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        if ($request->hasFile('service_image')) {
            $filenameWithExt = $request->file('service_image')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('service_image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $uplaod = upload_file($request, 'service_image', $fileNameToStore, 'Service');
            if ($uplaod['flag'] == 1) {
                $url = $uplaod['url'];
            } else {
                return $this->error(['message' => $uplaod['msg']]);
            }
        }

        $service                   = new Service();
        $service->name             = $request->name;
        $service->category_id      = $request->category;
        $service->price            = $request->price;
        $service->duration         = $request->duration;
        $service->description      = !empty($request->description) ? $request->description : '';
        $service->image            = !empty($request->service_image) ? $url : '';
        $service->business_id      = $active_business;
        $service->created_by       = creatorId();
        $service->save();

        return $this->success(['message' => 'Service successfully created.']);
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
        $service = Service::find($id);

        if(!empty($service))
        {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'category' => 'required',
                    'price' => 'required',
                    'duration' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return $this->error([
                    'message' => $messages->first()
                ]);
            }
            $service->name             = $request->name;
            $service->category_id      = $request->category;
            $service->price            = $request->price;
            $service->duration         = $request->duration;
            if($request->description)
            {
                $service->description      = !empty($request->description) ? $request->description : '';
            }
            if ($request->hasFile('service_image')) {
                $filenameWithExt = $request->file('service_image')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('service_image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request, 'service_image', $fileNameToStore, 'Service');
                if ($uplaod['flag'] == 1) {
                    if (!empty($service->image)) {
                        delete_file($service->image);
                    }
                    $url = $uplaod['url'];
                } else {
                    return $this->error(['message' => $uplaod['msg']]);
                }
                $service->image  = !empty($request->service_image) ? $url : '';
            }
            $service->save();
            return $this->success(['message' => 'Service updated successfully.']);

        }else{
            return $this->error(['message' => 'Service not found.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $service = Service::find($id);
        if(!empty($service))
        {
            if (!empty($service->image)) {
                delete_file($service->image);
            }
            $service->delete();
            return $this->success(['message' => 'Service successfully delete.']);
        }else{
            return $this->error(['message' => 'Service not found.']);
        }
    }
}
