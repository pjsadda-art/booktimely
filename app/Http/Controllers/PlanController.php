<?php

namespace App\Http\Controllers;

use App\DataTables\OrderDataTable;
use App\Models\AddOn;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PlanField;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Facades\ModuleFacade as Module;
use PDO;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('plan manage')) {
            $plan = Plan::where('custom_plan', 1)->first();

            if (Auth::user()->type == 'super admin') {
                $modules = Module::all();
                return view('plans.index', compact('modules', 'plan'));
            } else {
                $subscription = Session::get('Subscription');
                $admin_settings = getAdminAllSetting();
                if ((isset($admin_settings['plan_package']) ? $admin_settings['plan_package'] : 'off') == 'on'  && $request['type'] != 'subscription' && $subscription != 'custom_subscription') {
                    return redirect()->route('active.plans');
                }
                // Pre selected module, user,and time period on pricing page
                $session = Session::get('user-module-selection');
                $modules = Module::all();
                $moduleNameArray = array_column($modules, 'name');
                $purchaseds = [];
                $active_module = ActivatedModule();

                if (count($active_module) > 0) {
                    foreach ($active_module as $key => $value) {
                        if (in_array($value, $moduleNameArray)) {
                            $module = Module::find($value);
                            if (!isset($module->display) || $module->display == true) {
                                array_push($purchaseds, $module);
                            }
                            $modules = array_filter($modules, function ($module) use ($value) {
                                return $module->name != $value;
                            });
                        }
                    }
                }
                if (((isset($admin_settings['custome_package']) ? $admin_settings['custome_package'] : 'off') == 'on' && $request['type'] == 'subscription') || ((isset($admin_settings['custome_package']) ? $admin_settings['custome_package'] : 'off') == 'on' && empty($request->all()))) {
                    return view('plans.marketplace', compact('plan', 'modules', 'purchaseds', 'session'));
                } else {
                    return redirect()->back()->with('error', __('Something went wrong please try again.'));
                }
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function PlanList()
    {
        if (Auth::user()->isAbleTo('plan manage')) {
            if (admin_setting('plan_package') != 'on') {
                return redirect()->route('plans.index');
            }
            $plan = Plan::where('custom_plan', 0)->get();
            $modules = Module::all();

            return view('plans.planslist', compact('plan', 'modules'));
        } else {
            return redirect()->back()->with('error', __('Something went wrong please try again.'));
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('plan create')) {
            if (Auth::user()->type == 'super admin') {
                $plan_type = Plan::$plan_type;
                $modules = Module::all();
                return view('plans.create', compact('modules', 'plan_type'));
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('plan create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'package_price_monthly' => 'required|min:0',
                    'package_price_yearly' => 'required|min:0',
                    'price_per_user_monthly' => 'required|min:0',
                    'price_per_user_yearly' => 'required|min:0',
                    'price_per_business_monthly' => 'required|min:0',
                    'price_per_business_yearly' => 'required|min:0',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $plan = Plan::where('custom_plan', 1)->first();
            $plan->package_price_monthly = !empty($request->package_price_monthly) ? $request->package_price_monthly : 0;
            $plan->package_price_yearly = !empty($request->package_price_yearly) ? $request->package_price_yearly : 0;
            $plan->price_per_user_monthly = !empty($request->price_per_user_monthly) ? $request->price_per_user_monthly : 0;
            $plan->price_per_user_yearly = !empty($request->price_per_user_yearly) ? $request->price_per_user_yearly : 0;
            $plan->price_per_business_monthly = !empty($request->price_per_business_monthly) ? $request->price_per_business_monthly : 0;
            $plan->price_per_business_yearly = !empty($request->price_per_business_yearly) ? $request->price_per_business_yearly : 0;
            $plan->save();

            return redirect()->route('plans.index')->with('success', 'Details Saved Successfully.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function show(Plan $plan)
    {
        return redirect()->route('plans.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function edit(Plan $plan)
    {
        if (Auth::user()->isAbleTo('plan edit')) {
            $plan_type = Plan::$plan_type;
            $modules = Module::all();

            return view('plans.edit', compact('plan', 'modules', 'plan_type'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Plan $plan)
    {
        if (Auth::user()->isAbleTo('plan edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'number_of_user' => 'required|not_in:0',
                    'number_of_business' => 'required|not_in:0',
                    'modules' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $plan = Plan::find($plan->id);
            if ($plan->is_free_plan == 1) {
                $freeplan = Plan::where('is_free_plan', 1)->whereNotIn('id', [$plan->id])->count();
                if ($freeplan == 0) {
                    return redirect()->back()->with('error','One plan is compulsory free so you can`t edit this plan.');
                }
            }
            
            $plan->name = !empty($request->name) ? $request->name : '';
            $plan->is_free_plan = !empty($request->is_free_plan) ? $request->is_free_plan : 0;
            $plan->number_of_user = !empty($request->number_of_user) ? $request->number_of_user : 0;
            $plan->number_of_business = !empty($request->number_of_business) ? $request->number_of_business : 0;
            if (!empty($request->is_free_plan)) {

                $plan->package_price_monthly =  0;
                $plan->package_price_yearly =  0;
            } else {
                $plan->package_price_monthly = !empty($request->package_price_monthly) ? $request->package_price_monthly : 0;
                $plan->package_price_yearly = !empty($request->package_price_yearly) ? $request->package_price_yearly : 0;
            }
            $plan->trial = !empty($request->trial) ? $request->trial : 0;
            if ($request->trial == 1) {

                $plan->trial_days = !empty($request->trial_days) ? $request->trial_days : 0;
            }
            $plan->modules = !empty($request->modules) ? implode(',', $request->modules) : '';
            $plan->save();
            return redirect()->back()->with('success', 'Plan Update Successfully.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Plan $plan)
    {
        if ($plan->is_free_plan == 1) {
            $freeplan = Plan::where('is_free_plan', 1)->whereNotIn('id', [$plan->id])->count();
            if ($freeplan == 0) {
                return redirect()->back()->with('error', __('One plan is compulsory free so you can`t delete this plan'));
            }
        }

        $purchase_count = User::where('type', 'company')->where('active_plan', '=', $plan->id)->count();

        if ($purchase_count > 0) {
            return redirect()->back()->with('error', 'The company has subscribed to this plan, so it cannot be deleted.');
        } else {
            $plan->delete();

            return redirect()->back()->with('success', 'Plan Deleted Successfully.');
        }
    }

    public function PlanStore(Request $request)
    {
        if (Auth::user()->isAbleTo('plan create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'number_of_user' => 'required|not_in:0',
                    'number_of_business' => 'required|not_in:0',
                    'modules' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $plan = new Plan();
            $plan->name = !empty($request->name) ? $request->name : '';
            $plan->is_free_plan = !empty($request->is_free_plan) ? $request->is_free_plan : 0;
            $plan->number_of_user = !empty($request->number_of_user) ? $request->number_of_user : 0;
            $plan->number_of_business = !empty($request->number_of_business) ? $request->number_of_business : 0;
            $plan->package_price_monthly = !empty($request->package_price_monthly) ? $request->package_price_monthly : 0;
            $plan->package_price_yearly = !empty($request->package_price_yearly) ? $request->package_price_yearly : 0;
            $plan->trial = !empty($request->trial) ? $request->trial : 0;
            if ($request->trial == 1) {
                $plan->trial_days = !empty($request->trial_days) ? $request->trial_days : 0;
            }
            $plan->modules = !empty($request->modules) ? implode(',', $request->modules) : '';
            $plan->save();

            return redirect()->back()->with('success', 'Plan Saved Successfully.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function orders(OrderDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('plan orders')) {
            return $dataTable->render('plan_order.index');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function AddOneDetail($module = null)
    {
        if (Auth::user()->isAbleTo('module edit') && !empty($module)) {
            $addon = AddOn::where('module', $module)->first();
            if (!empty($addon)) {
                return view('plans.module_detail', compact('addon'));
            } else {
                return response()->json(['error' => __('Something went wrong, Data not found.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
    public function AddOneDetailSave(Request $request, $id = null)
    {
        if (Auth::user()->isAbleTo('module edit') && !empty($id)) {
            $addon = AddOn::find($id);

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|unique:add_ons,name,' . $addon->id,
                    'monthly_price' => 'required|min:0',
                    'yearly_price' => 'required|min:0',
                    'module_logo' => 'mimes:jpg,jpeg,png',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $addon->name = $request->name;
            $addon->monthly_price = $request->monthly_price;
            $addon->yearly_price = $request->yearly_price;
            $addon->save();

            if (!empty($request->module_logo)) {
                $module = Module::find($addon->module);
                if (!empty($module)) {
                    $module->getPath();
                    $fileNameToStore = 'favicon.png';
                    $dir             = $module->getPath();
                    $request->module_logo->move($dir, $fileNameToStore);
                }
            }
            $module = Module::find($addon->module);
            $module->moduleCacheForget();
            return redirect()->back()->with('success', 'Module Setting Save Successfully.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function PackageData(Request $request)
    {
        if ($request->has('plan_package')  && $request->plan_package != null && ($request->plan_package == "on" || admin_setting('custome_package') == "on")) {
            // Check if the record exists, and update or insert accordingly
            Setting::updateOrInsert([
                'key' => 'plan_package',
                'business' => getActiveBusiness(),
                'created_by' => \Auth::user()->id
            ], ['value' => $request->plan_package]);
            // Settings Cache forget
            AdminSettingCacheForget();
            return response()->json(['plan_package' => admin_setting('plan_package')]);
        } elseif ($request->has('custome_package')  && $request->custome_package != null && ($request->custome_package == "on" || admin_setting('plan_package') == "on")) {
            Setting::updateOrInsert([
                'key' => 'custome_package',
                'business' => getActiveBusiness(),
                'created_by' => \Auth::user()->id
            ], ['value' => $request->custome_package]);
            // Settings Cache forget
            AdminSettingCacheForget();
            return response()->json(['custome_package' => admin_setting('custome_package')]);
        } elseif ($request->plan_package == null || $request->custome_package == null) {

            return response()->json('error');
        }
    }

    public function ActivePlans(Request $request)
    {
        if (Auth::user()->isAbleTo('plan manage') && Auth::user()->type != 'super admin') {
            $plan = Plan::where('custom_plan', 0)->where('is_enabled', 1)->get();
            $modules = Module::all();

            return view('plans.activeplans', compact('plan', 'modules'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function PlanBuy(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('plan manage') && Auth::user()->type != 'super admin') {
            try {
                $id       = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Plan Not Found.'));
            }
            $plan = Plan::find($id);
            $user = User::where('id', Auth::user()->id)->first();
            if (Auth::user()->active_plan != $plan->id) {
                $session = Session::get('user-module-selection');
                $modules = Module::all();
                $active_module = ActivatedModule();
                return view('plans.planpayment', compact('plan', 'modules'));
            } else {
                return redirect()->route('active.plans')->with('error', __('Plan is already assign.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function PlanTrial($id)
    {
        if (Auth::user()->isAbleTo('plan manage') && Auth::user()->type != 'super admin') {
            try {
                $id       = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Plan Not Found.'));
            }
            $plan = Plan::find($id);
            $user = User::where('id', Auth::user()->id)->first();
            if (!empty($plan->trial) == 1) {

                $user->assignPlan($plan->id, 'Trial', $plan->modules, 0, $user->id);
                $user->is_trial_done = 1;
                $user->save();
            }
            return redirect()->back()->with('success', 'Your trial has been started.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function planRefund($user_id)
    {
        $user = User::find($user_id);

        $plan = Plan::where('is_free_plan', 1)->first();
        if ($plan) {
            $user->assignPlan($plan->id, 'Month', $plan->modules, 0, $user->id);
        }

        $admin_setting = getAdminAllSetting();

        $currency = isset($admin_setting['defult_currancy']) ? $admin_setting['defult_currancy'] : 'USD';

        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        Order::create(
            [
                'order_id' => $orderID,
                'name' => null,
                'email' => null,
                'card_number' => null,
                'card_exp_month' => null,
                'card_exp_year' => null,
                'plan_name' => !empty($plan->name) ? $plan->name : 'Basic Package',
                'plan_id' => $plan->id,
                'price' => 0,
                'price_currency' => $currency,
                'txn_id' => '',
                'payment_type' => __('Zero Price'),
                'payment_status' => 'succeeded',
                'receipt' => null,
                'user_id' => $user->id,
            ]
        );

        return redirect()->back()->with('success', __('Order Refunded Successfully.'));
    }


    public function planEnable(Request $request)
    {
        $plan = Plan::findOrFail($request->plan_id);

        $user_with_plan = User::where('active_plan', $plan->id)->count();


        if ($user_with_plan > 0) {
            $msg = 'The company has subscribed to this plan, so it cannot be disable.';
            return response()->json(['error' => false, 'message' => $msg]);
        } else {
            $enable = $request->enable;
            $plan->is_enabled = $enable;
            $plan->save();
            if ($enable == "0") {
                $msg = 'Plan Successfully disable.';
            } else {
                $msg = 'Plan Successfully enable.';
            }
            return response()->json(['success' => true, 'message' => $msg]);
        }
    }
}
