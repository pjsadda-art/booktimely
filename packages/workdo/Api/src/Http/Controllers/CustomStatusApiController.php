<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\CustomStatus;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class CustomStatusApiController extends Controller
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

        $statusData = [];
        $statuses = CustomStatus::where('business_id', $active_business)->get()->toArray();
        foreach ($statuses as $key => $status) {
            $statusData[$key]['id'] = $status['id'];
            $statusData[$key]['title'] = $status['title'];
            $statusData[$key]['status_color'] = $status['status_color'];
        }

        if ($statuses) {
            return $this->success($statusData);
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
                'title' => 'required',
                'status_color' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        $customstatus                   = new CustomStatus();
        $customstatus->title            = $request->title;
        $customstatus->status_color     = $request->status_color;
        $customstatus->business_id      = $active_business;
        $customstatus->created_by       = creatorId();
        $customstatus->save();

        return $this->success(['message' => 'Custom Status successfully created.']);
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
        $customstatus = CustomStatus::find($id);

        if(!empty($customstatus))
        {
            $validator = \Validator::make(
                $request->all(),
                rules: [
                    'title' => 'required',
                    'status_color' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return $this->error([
                    'message' => $messages->first()
                ]);
            }
            $customstatus->title             = $request->title;
            $customstatus->status_color      = $request->status_color;
            $customstatus->save();
            return $this->success(['message' => 'Custom Status updated successfully.']);

        }else{
            return $this->error(['message' => 'Custom Status not found.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $status = CustomStatus::find($id);
        if(!empty($status))
        {
            $status->delete();
            return $this->success(['message' => 'Custom Status successfully delete.']);
        }else{
            return $this->error(['message' => 'Custom Status not found.']);
        }
    }
}
