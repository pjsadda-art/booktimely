<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\Role;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;

class CustomerApiController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $customers = Customer::where('created_by',creatorId())->where('business_id',getActiveBusiness())->get();
        if (!empty($customers)) {
            return $this->success($customers);
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
            $request->all(), [
                    'name' => 'required',
                    'email' => 'required',
                    'gender' => 'required',
                    'dob' => 'required',
                    'mobile_no' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return $this->error([
                    'message' => $messages->first()
                ]);
            }

            $roles = Role::where('name','customer')->where('created_by',creatorId())->first();
            if($roles)
            {
                if ($request->hasFile('image'))
                {
                    $filenameWithExt = $request->file('image')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('image')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $uplaod = upload_file($request,'image',$fileNameToStore,'Customer');
                    if($uplaod['flag'] == 1)
                    {
                        $url = $uplaod['url'];
                    }
                    else
                    {
                        return $this->error(['message' => $uplaod['msg']]);
                    }
                }

                $user = User::create(
                [
                    'name' => !empty($request->name) ? $request->name : null,
                    'email' => !empty($request->email) ? $request->email : null,
                    'mobile_no' => !empty($request->mobile_no) ? $request->mobile_no : null,
                    'email_verified_at' => date('Y-m-d h:i:s'),
                    'password' => !empty($request->password) ? Hash::make($request->password) : null,
                    'avatar' => !empty($request->image) ? $url : 'uploads/users-avatar/avatar.png',
                    'type' => $roles->name,
                    'lang' => 'en',
                    'business_id' => getActiveBusiness(),
                    'created_by' => creatorId(),
                ]);
                $user->save();
                $user->addRole($roles);

                $customer                           = new Customer();
                $customer->name                     = $request->name;
                $customer->user_id                  = $user->id;
                $customer->gender                   = $request->gender;
                $customer->dob                      = $request->dob;
                $customer->description              = !empty($request->description) ? $request->description : '';
                $customer->business_id              = $user->business_id;
                $customer->created_by                = creatorId();
                $customer->save();

                return $this->success(['message' => 'Customer successfully created.']);
            }
            else
            {
                return $this->error(['message' => 'Please create customer role.']);
            }
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
        $customer = Customer::find($id);
        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required',
                'email' => 'required',
                'gender' => 'required',
                'dob' => 'required',
                'mobile_no' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        $roles = Role::where('name','customer')->where('created_by',creatorId())->first();
        if($roles)
        {
            $customer->name         = $request->name;
            $customer->gender  = $request->gender ?? '';
            $customer->dob   = $request->dob;
            $customer->description  = !empty($request->description) ? $request->description : '';
            $customer->save();

            $user = User::where('id',$customer->user_id)->first();
            if($user)
            {
                if ($request->hasFile('image')) {

                    $filenameWithExt = $request->file('image')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('image')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                    $path = upload_file($request, 'image', $fileNameToStore, 'customer');
                    if($path['flag'] == 1){
                        // old img delete
                        if (!empty($user->avatar) && strpos($user->avatar, 'avatar.png') == false && check_file($user->avatar)) {
                            delete_file($user->avatar);
                        }
                        if (!empty($request->image) && isset($path['url'])) {
                            $user->avatar =  $path['url'];
                        }
                    }else{
                        return $this->error(['message' => $path['msg']]);
                    }
                }

                $user->name                     = $request->name;
                $user->email                     = $request->email;
                $user->mobile_no                     = $request->mobile_no;
                $user->password                     = !empty($request->password) ? Hash::make($request->password) : null;
                $user->type = $roles->name;
                $user->save();
            }

            return $this->success(['message' => 'Customer successfully Updated.']);
        }
        else
        {
            return $this->error(['message' => 'Please create customer role.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);
        $user = User::find($customer->user_id);
        if($user)
        {
            if (!empty($user->avatar)) {
                delete_file($user->avatar);
            }

            $user->delete();
            $customer->delete();
            return $this->success(['message' => 'Customer successfully delete.']);
        }
        else{
            return $this->error(['message' => 'User not found.']);
        }
    }
}
