<?php

namespace App\Http\Controllers\Auth;

use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Models\Business;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Workdo\GoogleCaptcha\Events\VerifyReCaptchaToken;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public $admin_settings;

    public function setting(){
        $this->admin_settings = getAdminAllSetting();

    }
    public function __construct()
    {
        $this->setting();

        if(!file_exists(storage_path() . "/installed"))
        {
            header('location:install');
            die;
        }
        if(module_is_active('GoogleCaptcha') && (isset($this->admin_settings['google_recaptcha_is_on']) ? $this->admin_settings['google_recaptcha_is_on'] : 'off') == 'on' )
        {
            config(['captcha.secret' => isset($this->admin_settings['google_recaptcha_secret']) ? $this->admin_settings['google_recaptcha_secret'] : '']);
            config(['captcha.sitekey' => isset($this->admin_settings['google_recaptcha_key']) ? $this->admin_settings['google_recaptcha_key'] : '']);
        }
    }
    public function create($lang = '')
    {
        if (empty( $this->admin_settings['signup']) ||  (isset($this->admin_settings['signup']) ? $this->admin_settings['signup'] : 'off') == "on")
        {
            if($lang == '')
            {
                $lang = getActiveLanguage();
            }
            else
            {
                $lang = array_key_exists($lang, languages()) ? $lang : 'en';
            }
            \App::setLocale($lang);
            return view('auth.register',compact('lang'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        if(module_is_active('GoogleCaptcha') && admin_setting('google_recaptcha_is_on') == 'on' )
        {
            if(admin_setting('google_recaptcha_version') == 'v2'){
                $validation['g-recaptcha-response'] = 'required|captcha';

            }elseif(admin_setting('google_recaptcha_version') == 'v3'){
                $result = event(new VerifyReCaptchaToken($request));
                if (!isset($result[0]['status']) || $result[0]['status'] != true) {
                    $key = 'g-recaptcha-response';
                    $request->merge([$key => null]); // Set the key to null

                }
                $validation['g-recaptcha-response'] = 'required';

            }else{
                $validation = [];
            }
        }else{
            $validation = [];
        }
        $this->validate($request, $validation);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        Auth::login($user);

        $role_r = Role::where('name','company')->first();
        if(!empty($user))
        {
            $user->addRole($role_r);
            // business slug create on business Model
            $business = new Business();
            $business->name = $request->business_name;
            $business->form_type    = !empty($request->form_type) ? $request->form_type : 'form-layout';
            $business->layouts      = !empty($request->layouts) ? $request->layouts : 'Formlayout1';
            $business->theme_color  = !empty($request->theme_color) ? $request->theme_color : 'color1-Formlayout1';
            $business->created_by = $user->id;
            $business->save();

            $user_work = User::find($user->id);
            $user_work->active_business = $business->id;
            $user_work->business_id = $business->id;
            $user_work->save();

            User::CompanySetting($user->id);

            $user->MakeRole();


            if(!empty($request->type) && $request->type != "pricing")
            {
                $plan = Plan::where('is_free_plan',1)->first();
                if($plan)
                {
                    $user->assignPlan($plan->id,'Month',$plan->modules,0,$user->id);
                }
            }

            if ( admin_setting('email_verification') == 'on')
            {
                try
                {
                    $uArr = [
                        'email'=> $request->email,
                        'password'=> $request->password,
                        'company_name'=>$request->name,
                    ];

                    $admin_user = User::where('type','super admin')->first();
                    SetConfigEmail(!empty($admin_user->id) ? $admin_user->id : null);
                    $resp = EmailTemplate::sendEmailTemplate('New User', [$user->email], $uArr,$admin_user->id);
                    $user->sendEmailVerificationNotification(); 
                    // event(new Registered($user));
                }
                catch(\Exception $e)
                {
                    $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                }
            }
            else
            {
                $user_work = User::find($user->id);
                $user_work->email_verified_at = date('Y-m-d h:i:s');
                $user_work->save();
            }

        }

        return redirect()->route('plans.index',['type'=>'subscription']);
    }
}
