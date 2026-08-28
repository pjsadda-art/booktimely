<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\Business;
use App\Models\EmailTemplate;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }
    public function login(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return response()->json(['status'=>'error', 'message'=>$messages->first()]);
        }

        $credentials = $request->only('email', 'password');

        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
                'status' => 'success',
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ],200);
    }



    public function register(Request $request)
    {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|string|max:255',
                    'business_name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required', 'confirmed',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return response()->json(['status'=>'error', 'message'=>$messages->first()]);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Auth::login($user);

            $role = Role::where('name', 'company')->first();
            if ($role) {
                $user->addRole($role);
            }

            $business = new Business();
            $business->name = $request->business_name;
            $business->form_type = $request->form_type ?? 'form-layout';
            $business->layouts = $request->layouts ?? 'Formlayout1';
            $business->theme_color = $request->theme_color ?? 'color1-Formlayout1';
            $business->created_by = $user->id;

            $business->save();

            $user->update([
                'active_business' => $business->id,
                'business_id' => $business->id,
            ]);

            User::CompanySetting($user->id);

            if (!empty($request->type) && $request->type != "pricing") {
                $plan = Plan::where('is_free_plan', 1)->first();
                if ($plan) {
                    $user->assignPlan($plan->id, 'Month', $plan->modules, 0, $user->id);
                }
            }

            if (admin_setting('email_verification') == 'on') {
                try {
                    $uArr = [
                        'email' => $request->email,
                        'password' => $request->password,
                        'company_name' => $request->name,
                    ];
                    $admin_user = User::where('type', 'super admin')->first();
                    SetConfigEmail($admin_user->id ?? null);
                    EmailTemplate::sendEmailTemplate('New User', [$user->email], $uArr, $admin_user->id ?? null);
                    $user->sendEmailVerificationNotification();
                    // event(new Registered($user));
                } catch (\Exception $e) {
                    $smtp_error = __('E-Mail has not been sent due to SMTP configuration issues.');
                }
            } else {
                $user->update(['email_verified_at' => now()]);
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ],
            ], 200);
    }


    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        $current_password = $user->password;
        if (Hash::check($request->current_password, $current_password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();

            return $this->success(['message' => 'User Password updated successfully']);

        } else {
            return $this->error(['message' => 'Please enter correct current password!']);
        }

    }


    public function logout()
    {
        Auth::logout();
        return $this->success(['message' => 'Successfully logged out']);
    }


}
