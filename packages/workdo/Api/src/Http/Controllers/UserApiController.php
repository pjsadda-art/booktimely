<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserApiController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $users = User::where('created_by',creatorId())->where('business_id',$request->business_id)->get();
        $users = $users->map(function($user){
            return [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'avatar'        => get_file($user->avatar),
                'type'          => $user->type
            ];
        });
        return $this->success($users);
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
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:120',
                'email' => [
                    'required', 'email',
                    Rule::unique('users')->where(function ($query) {
                        return $query->where('created_by', creatorId())
                                    ->where('business_id', getActiveBusiness());
                    }),
                ],
                'mobile_no' => 'nullable|regex:/^([0-9\\s\\-\\+\\(\\)]*)$/|min:9',
                'password' => 'nullable|min:6',
                'type' => 'required',
            ]
        );

        
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }


        $roles = Role::where('name', 'company')->first();

            $company_settings = getCompanyAllSetting();

            $userpassword               = $request->input('password');
            $user['name']               = $request->input('name');
            $user['email']              = $request->input('email');
            $user['mobile_no']          = $request->input('mobile_no');
            $user['password']           = !empty($userpassword) ? \Hash::make($userpassword) : null;
            $user['lang']               = !empty($company_settings['defult_language']) ? $company_settings['defult_language'] : 'en';
            $user['type']               = $roles->name;
            $user['created_by']         = creatorId();
            $user['business_id']       = getActiveBusiness();
            $user['active_business']   = getActiveBusiness();
            $user = User::create($user);

        // Assign role to user
        $user->addRole($roles);


        return $this->success([$user, 'message' => 'User successfully created']);
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
        $user = User::find($id);

        if (empty($user)) {
            return response()->json(['status' => 'error', 'message' => __('No data available')], 404);
        }

        $roles = Role::where('name', '!=', 'customer')
                    ->where('name', '!=', 'staff')
                    ->where('created_by', \Auth::user()->id)
                    ->pluck('name', 'id');

        return $this->success([$user, 'roles' => $roles, 'message' => 'User successfully created']);
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
          // Check if user exists
        $user = User::find($id);
        
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:120',
                'email' => [
                    'required', 'email',
                    Rule::unique('users')->ignore($id)->where(function ($query) {
                        return $query->where('created_by', creatorId())
                                    ->where('business_id', getActiveBusiness());
                    }),
                ],
                'mobile_no' => 'nullable|regex:/^([0-9\\s\\-\\+\\(\\)]*)$/|min:9',
                'password' => 'nullable|min:6',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        if (!$user) {
            return $this->error(['message' => 'User not found.']);
        }

        $roles = Role::where('name', 'company')->first();


        // Update user fields
        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;
        $user->mobile_no = $request->input('mobile_no', $user->mobile_no);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $company_settings = getCompanyAllSetting();
        $user->lang = $company_settings['defult_language'] ?? 'en';
        $user->type = $roles->name;
        $user->created_by = creatorId();
        $user->business_id = getActiveBusiness();
        $user->active_business = getActiveBusiness();
        $user->save();


        // Update role
        $user->syncRoles([$roles]);

        return $this->success([$user, 'message' => 'User successfully updated']);

        }


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if($user){
            $tables_in_db = \DB::select('SHOW TABLES');
            $db = "Tables_in_" . env('DB_DATABASE');
            foreach ($tables_in_db as $table) {
                if (Schema::hasColumn($table->{$db}, 'created_by')) {
                    \DB::table($table->{$db})->where('created_by', $user->id)->delete();
                }
            }
            $user->delete();
            return $this->success(['message' => 'User Successfully Delete.']);

        }
        else{
            return $this->error(['message' => 'You can\'t delete User!']);
        }
    }

    public function userPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'password' => 'required|confirmed|same:password_confirmation|min:6',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $user                 = User::where('id', $id)->first();

        if($user){

            if (isset($request->login_enable)) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'is_enable_login' => 1,
                ])->save();
            } else {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();
            }

            return $this->success(['message' => 'User Password Reset Successfully!']);
        }
        else{
            return $this->error(['message' => 'Use Not Found!']);
        }
    }
}
