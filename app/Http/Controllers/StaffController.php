<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Business;
use App\Models\Location;
use App\Models\Service;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Appointment;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (Auth::user()->isAbleTo('staff create')) {
            $business = Business::find($request->business_id);
            $location = Location::where('created_by', creatorId())->where('business_id', $business->id)->get()->pluck('name', 'id');
            $service = Service::where('created_by', creatorId())->where('business_id', $business->id)->get()->pluck('name', 'id');

            return view('staff.create', compact('business', 'location', 'service'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('staff create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => [
                        'required',
                        Rule::unique('users')->where(function ($query) {
                            return $query->where('type', '=', 'staff')->where('created_by', creatorId())->where('business_id', getActiveBusiness());
                        })
                    ],
                    'location' => 'required',
                    'service' => 'required',
                    'staff_image' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $business = Business::find($request->business_id);
            $roles = Role::where('name', 'staff')->where('created_by', creatorId())->first();
            if ($roles) {
                if ($request->hasFile('staff_image')) {
                    $filenameWithExt = $request->file('staff_image')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('staff_image')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $uplaod = upload_file($request, 'staff_image', $fileNameToStore, 'Staff');
                    if ($uplaod['flag'] == 1) {
                        $url = $uplaod['url'];
                    } else {
                        return redirect()->back()->with('error', $uplaod['msg']);
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
                $staff->location_id              = implode(',', $request->location);
                $staff->service_id               = !empty(implode(',', $request->service)) ? implode(',', $request->service) : '';
                $staff->description              = !empty($request->description) ? $request->description : '';
                $staff->business_id              = $business->id;
                $staff->created_by               = creatorId();
                $staff->save();
                $staff->locations()->sync($this->locationPivotData($request->location, $business->id));
                $tab = 3;

                return redirect()->back()->with('success', __('Staff successfully created.'))->with('tab', $tab);
            } else {
                return redirect()->back()->with('error', __('Please create staff role.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        if (Auth::user()->isAbleTo('staff edit')) {
            $location = Location::where('created_by', creatorId())->where('business_id', $staff->business_id)->get()->pluck('name', 'id');
            $service = Service::where('created_by', creatorId())->where('business_id', $staff->business_id)->get()->pluck('name', 'id');

            return view('staff.edit', compact('staff', 'location', 'service'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        if (Auth::user()->isAbleTo('staff edit')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => [
                        'required',
                        Rule::unique('users')->where(function ($query)  use ($staff) {
                            return $query->whereNotIn('id', [$staff->user->id])->where('type', '=', 'staff')->where('created_by', creatorId())->where('business_id', getActiveBusiness());
                        })
                    ],
                    'location' => 'required',
                    'service' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $roles = Role::where('name', 'staff')->where('created_by', creatorId())->first();
            if ($roles) {
                $staff->name = $request->name;
                $staff->location_id = implode(',', $request->location);
                $staff->service_id = implode(',', $request->service);
                $staff->description = !empty($request->description) ? $request->description : '';
                $staff->save();
                $staff->locations()->sync($this->locationPivotData($request->location, $staff->business_id));

                $user = User::where('id', $staff->user_id)->first();
                if ($request->hasFile('staff_image')) {
                    $filenameWithExt = $request->file('staff_image')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('staff_image')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $uplaod = upload_file($request, 'staff_image', $fileNameToStore, 'Staff');
                    if ($uplaod['flag'] == 1) {
                        if (!empty($user->avatar)) {
                            delete_file($user->avatar);
                        }
                        $url = $uplaod['url'];
                    } else {
                        $tab = 3;
                        return redirect()->back()->with('error', $uplaod['msg'])->with('tab', $tab);
                    }
                    $user->avatar  = !empty($request->staff_image) ? $url : '';
                }
                if ($user) {
                    $user->name = $request->name;
                    $user->email = $request->email;
                    $user->type = $roles->name;
                    $user->save();
                }
                $tab = 3;
                return redirect()->back()->with('success', __('Staff updated successfully!'))->with('tab', $tab);
            } else {
                return redirect()->back()->with('error', __('Please create staff role.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        if(Auth::user()->isAbleTo('staff delete'))
        {
            $user = User::find($staff->user_id);
            if($user)
            {
                if(!empty($user->avatar))
                {
                    delete_file($user->avatar);
                }
                $user->delete();
                $staff->delete();
            }
            $tab = 3;
            return redirect()->back()->with('error', __('Staff successfully delete.'))->with('tab', $tab);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Pivot rows for Staff::locations()->sync(), keyed by location id, so the
     * required business_id/created_by pivot columns are always populated.
     */
    protected function locationPivotData(array $locationIds, $businessId): array
    {
        $data = [];

        foreach ($locationIds as $locationId) {
            $data[$locationId] = ['business_id' => $businessId, 'created_by' => creatorId()];
        }

        return $data;
    }

    public function getStaffData(Request $request)
    {
        if (!empty($request->service) && !empty($request->location)) {
            $serviceId = $request->service;
            $locationId = $request->location;
            $date = $request->date;
            $staffData = Staff::with('user')->whereRaw("FIND_IN_SET(?, service_id) > 0", [$serviceId])
                ->whereHas('locations', function ($query) use ($locationId) {
                    $query->where('locations.id', $locationId);
                })
                ->get();
            $staffids = $staffData->pluck('user_id')->toArray();
            $appointmentData = [];
            if (!empty($date)) {
                $appointmentData = Appointment::where('date', $date)
                    ->whereIn('staff_id', $staffids)
                    ->get();
                $appointedStaffIds = $appointmentData->pluck('staff_id')->toArray();
            }
            $staffid = array_diff($staffids, $appointedStaffIds ?? []);
            // echo "<pre>";
            // print_r($staffid);
            // die;
            return response()->json(['staffData' => $staffData, 'staffid' => array_values($staffid)]);
        } else {
            return response()->json([], 400);
        }
    }

    public function getServicesByCategory(Request $request)
    {
        if (!empty($request->categoryValue)) {
            $categoryId = $request->categoryValue;

            $services = Service::where('category_id', $categoryId)->where('is_online', 1)->get();

            return response()->json($services);
        } else {
            return response()->json([], 400);
        }
    }
}
