<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\Business;
use App\Models\Location;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffApiController extends Controller
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

        // Fetch locations
        $locationData = [];
        $locations = Location::where('business_id', $active_business)->get();
        foreach ($locations as $location) {
            $locationData[] = [
                'id' => $location->id,
                'name' => $location->name,
            ];
        }

        // Fetch staff data
        $staffData = Staff::where('business_id', $active_business)->paginate(10);

        if ($staffData->isNotEmpty()) {
            $staffList = $staffData->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'location_name' => $staff->location_id ? $staff->Location()->pluck('name')->implode(', ') : null,
                    'service_name' => $staff->service_id ? $staff->Service()->pluck('name')->implode(', ') : null,
                    'image' => check_file($staff->image) ? get_file($staff->image) : get_file('uploads/default/avatar.png'),
                    'description' => $staff->description ?: '',
                ];
            });

            return $this->success([
                'location_list' => $locationData,
                'staff_list' => $staffList,
                'total' => $staffData->total(),
                'per_page' => $staffData->perPage(),
                'current_page' => $staffData->currentPage(),
                'last_page' => $staffData->lastPage(),
                'next_page_url' => $staffData->nextPageUrl(),
                'prev_page_url' => $staffData->previousPageUrl(),
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

        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required',
                'location' => 'required',
                'service' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }
        $business = Business::find($request->business_id);
        $roles = Role::where('name', 'staff')->where('created_by', creatorId())->first();
        if ($request->hasFile('staff_image')) {
            $filenameWithExt = $request->file('staff_image')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('staff_image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $uplaod = upload_file($request, 'staff_image', $fileNameToStore, 'Staff');
            if ($uplaod['flag'] == 1) {
                $url = $uplaod['url'];
            } else {
                return $this->error(['message' => $uplaod['msg']]);
            }
        }

        $user = User::create(
            [
                'name' => !empty($request->name) ? $request->name : null,
                'email' => !empty($request->email) ? $request->email : null,
                'email_verified_at' => date('Y-m-d h:i:s'),
                'password' => !empty($request->password) ? Hash::make($request->password) : null,
                'avatar' => !empty($request->staff_image) ? $url : 'uploads/users-avatar/avatar.png',
                'type' => $roles->name,
                'lang' => 'en',
                'business_id' => $business->id,
                'created_by' => creatorId(),
            ]
        );

        $user->save();
        $user->addRole($roles);

        $staff                           = new Staff();
        $staff->name                     = $request->name;
        $staff->user_id                  = $user->id;
        $staff->location_id              = !empty($request->location) ? $request->location : '';
        $staff->service_id               = !empty($request->service) ? $request->service : '';
        $staff->description              = !empty($request->description) ? $request->description : '';
        $staff->business_id              = $business->id;
        $staff->created_by               = creatorId();
        $staff->save();


        return $this->success(['message' => 'Staff successfully created.']);
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
        $staff = Staff::find($id);

        if(!empty($staff))
        {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required',
                    'location' => 'required',
                    'service' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return $this->error([
                    'message' => $messages->first()
                ]);
            }
            $roles = Role::where('name', 'staff')->where('created_by', creatorId())->first();

            if ($roles) {

            $staff->name = $request->name;
            $staff->location_id = !empty($request->location) ? $request->location : '';
            $staff->service_id = !empty($request->service) ? $request->service : '';
            $staff->description = !empty($request->description) ? $request->description : '';
            $staff->save();

            $user = User::where('id', $staff->user_id)->first();

            if ($request->hasFile('staff_image')) {
                $filenameWithExt = $request->file('staff_image')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('staff_image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request, 'staff_image', $fileNameToStore, 'Staff');
                if ($uplaod['flag'] == 1) {
                    if (!empty($user->image)) {
                        delete_file($user->image);
                    }
                    $url = $uplaod['url'];
                } else {
                    return $this->error(['message' => $uplaod['msg']]);
                }
                $user->avatar  = !empty($request->staff_image) ? $url : '';
            }
            if ($user) {
                $user->name = $request->name;
                $user->type = $roles->name;
                $user->save();
            }
            return $this->success(['message' => 'Staff updated successfully.']);
        } else {
            return $this->error(['message' => 'Please create staff role.']);
        }
        }else{
            return $this->error(['message' => 'Staff not found.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $staff = staff::find($id);
        if(!empty($staff))
        {
            if (!empty($staff->image)) {
                delete_file($staff->image);
            }
            $staff->delete();
            return $this->success(['message' => 'Staff successfully delete.']);
        }else{
            return $this->error(['message' => 'Staff not found.']);
        }
    }
}
