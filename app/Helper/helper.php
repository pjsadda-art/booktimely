<?php

use App\Models\AddOn;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\User;
use App\Models\Business;
use App\Models\userActiveModule;
use Illuminate\Support\Collection;
use App\Models\Setting;
use App\Models\UserCoupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\BusinessHours;
use Carbon\Carbon;
use App\Facades\ModuleFacade as Module;

if (!function_exists('getMenu')) {
    function getMenu()
    {
        $user = auth()->user();
        return Cache::rememberForever(
            'sidebar_menu_' . $user->id,
            function () use ($user) {
                $role = $user->roles->first();
                $menu = new \App\Classes\Menu($user);
                if ($role->name == 'super admin') {
                    event(new \App\Events\SuperAdminMenuEvent($menu));
                } else {
                    event(new \App\Events\CompanyMenuEvent($menu));
                }
                return generateMenu($menu->menu, null);
            }
        );
    }
}

if (!function_exists('generateMenu')) {
    function generateMenu($menuItems, $parent = null,$printedGroups = [])
    {
        $html = '';

        // Group the items by the 'group' key
        $groupedItems = collect($menuItems)->groupBy('group');

        $groupOrder = ['base', 'appointments', 'inventory', 'codes & tickets', 'contacts & reports', 'others'];

        $sortedGroupedItems = $groupedItems->sortBy(function ($group, $key) use ($groupOrder) {
            // Return the index of the group in the $groupOrder array to define the sort order
            return array_search($key, $groupOrder);
        });
        // dd($sortedGroupedItems);
        foreach ($sortedGroupedItems as $group => $items)
        {

            if(Auth::user()->type != 'super admin')
            {
                if ($group === 'base') {
                    // Process the items directly without a group name header
                    $printedGroups[] = $group; // Mark base as printed
                } else {
                    // Print the group header for all groups except 'base'
                    if ($parent === null && !in_array($group, $printedGroups)) {
                        $html .= '<li class="nav-main-title"><h3> '. ucfirst($group) .'</h3>';
                        $printedGroups[] = $group; // Mark this group as printed
                    }
                }

            }
            // Convert the collection of items to an array and filter based on parent
            $filteredItems = array_filter($items->toArray(), function ($item) use ($parent) {
                return $item['parent'] == $parent;
            });

            // Sort the filtered items by their order
            usort($filteredItems, function ($a, $b) {
                return $a['order'] - $b['order'];
            });
            // Loop through the filtered items and generate HTML
            foreach ($filteredItems as $item) {
                $hasChildren = hasChildren($menuItems, $item['name']);
                if ($item['parent'] == null) {
                    $html .= '<li class="dash-item dash-hasmenu">';
                } else {
                    $html .= '<li class="dash-item">';
                }

                if ($item['name'] == 'add-on-manager') {
                    $html .= '<a href="' . (!empty($item['route']) ? route($item['route'], $item['params'] ?? []) : '#!') . '" class="dash-link d-flex align-items-center">';
                    if ($item['parent'] == null) {
                        $html .= ' <span class="dash-micon"><i class="ti ti-' . $item['icon'] . '"></i></span>
                        <div class="text-center"> <span class="dash-mtext">';
                        $html .= __($item['title']) . '</span> <span class="text-center d-block animate-charcter">Premium</span></div>';
                    }
                } else {
                    // `params` lets one route serve several menu entries — the
                    // inventory lookup screens all share `inventory.reference.index`
                    // and differ only by {type}. Without it, route() would throw
                    // for any route that takes a parameter.
                    $html .= '<a href="' . (!empty($item['route']) ? route($item['route'], $item['params'] ?? []) : '#!') . '" class="dash-link">';

                    if ($item['parent'] == null) {
                        $html .= ' <span class="dash-micon"><i class="ti ti-' . $item['icon'] . '"></i></span>
                        <span class="dash-mtext">';
                    }
                    $html .= __($item['title']) . '</span>';
                }

                if ($hasChildren) {
                    $html .= '<span class="dash-arrow"> <i data-feather="chevron-right"></i> </span> </a>';
                    $html .= '<ul class="dash-submenu">';
                    $html .= generateMenu($menuItems, $item['name'],$printedGroups);
                    $html .= '</ul>';
                } else {
                    $html .= '</a>';
                }

                $html .= '</li>';
            }
        }

        return $html;
    }
}

if (!function_exists('hasChildren')) {
    function hasChildren($menuItems, $name)
    {
        foreach ($menuItems as $item) {
            if ($item['parent'] === $name) {
                return true;
            }
        }
        return false;
    }
}


if (!function_exists('getSettingMenu')) {
    function getSettingMenu()
    {
        $user = auth()->user();
        $role = $user->roles->first();
        $menu = new \App\Classes\Menu($user);
        if ($role->name == 'super admin') {
            event(new \App\Events\SuperAdminSettingMenuEvent($menu));
        } else {
            event(new \App\Events\CompanySettingMenuEvent($menu));
        }
        return generateSettingMenu($menu->menu);
    }
}


if (!function_exists('generateSettingMenu')) {
    function generateSettingMenu($menuItems)
    {
        usort($menuItems, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        $html = '';
        foreach ($menuItems as $menu) {
            $html .= '<a href="#' . $menu['navigation'] . '" data-module="' . $menu['module'] . '" class="list-group-item list-group-item-action setting-menu-nav">' . $menu['title'] . '<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>';
        }
        return $html;
    }
}
if (!function_exists('getSettings')) {
    function getSettings()
    {
        $user = auth()->user();
        $role = $user->roles->first();
        if ($role->name == 'super admin') {
            $settings = getAdminAllSetting();
            $html = new \App\Classes\Setting($user, $settings);
            event(new \App\Events\SuperAdminSettingEvent($html));
        } else {
            $settings = getCompanyAllSetting();
            $html = new \App\Classes\Setting($user, $settings);
            event(new \App\Events\CompanySettingEvent($html));
        }
        return generateSettings($html->html);
    }
}
if (!function_exists('generateSettings')) {
    function generateSettings($settingItems)
    {
        usort($settingItems, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        $html = '';
        foreach ($settingItems as $setting) {
            $html .= $setting['html'];
        }
        return $html;
    }
}

if (!function_exists('getAdminAllSetting')) {
    function getAdminAllSetting()
    {
        // Laravel cache
        return Cache::rememberForever('admin_settings', function () {
            $super_admin = User::where('type', 'super admin')->first();
            $settings = [];
            if ($super_admin) {
                $settings = Setting::where('created_by', $super_admin->id)->where('business', $super_admin->active_business)->pluck('value', 'key')->toArray();
            }

            return $settings;
        });
    }
}

if (!function_exists('getCompanyAllSetting')) {
    function getCompanyAllSetting($user_id = null, $business = null)
    {
        if (!empty($user_id)) {
            $user = User::find($user_id);
        } else {

            $user =  auth()->user();
        }
        // // Check if the user is not 'company' or 'super admin' and find the creator
        if (!empty($user) && !in_array($user->type, ['company', 'super admin'])) {
            $user = User::find($user->created_by);
        }

        if (!empty($user)) {
            $business = $business ?? $user->active_business;
            $key = 'company_settings_' . $business . '_' . $user->id;
            return Cache::rememberForever($key, function () use ($user, $business) {
                $settings = [];
                $settings = Setting::where('created_by', $user->id)->where('business', $business)->pluck('value', 'key')->toArray();
                return $settings;
            });
        }

        return [];
    }
}

if (!function_exists('admin_setting')) {
    function admin_setting($key)
    {
        if ($key) {
            $admin_settings = getAdminAllSetting();
            $setting = (array_key_exists($key, $admin_settings)) ? $admin_settings[$key] : null;
            return $setting;
        }
    }
}

if (!function_exists('company_setting')) {
    function company_setting($key, $user_id = null, $business = null)
    {
        if ($key) {
            $company_settings = getCompanyAllSetting($user_id, $business);
            $setting = null;
            if (!empty($company_settings)) {
                $setting = (array_key_exists($key, $company_settings)) ? $company_settings[$key] : null;
            }
            return $setting;
        }
    }
}

if (!function_exists('AdminSettingCacheForget')) {
    function AdminSettingCacheForget()
    {
        try {
            Cache::forget('admin_settings');
        } catch (\Exception $e) {
            \Log::error('AdminSettingCacheForget :' . $e->getMessage());
        }
    }
}

if (!function_exists('comapnySettingCacheForget')) {
    function comapnySettingCacheForget()
    {
        try {
            $key = 'company_settings_' . getActiveBusiness() . '_' . creatorId();
            Cache::forget($key);
        } catch (\Exception $e) {
            \Log::error('comapnySettingCacheForget :' . $e->getMessage());
        }
    }
}

if (!function_exists('sideMenuCacheForget')) {
    function sideMenuCacheForget($type = null)
    {
        if ($type == 'all') {
            Cache::flush();
        }

        $user = auth()->user();
        if ($user->type == 'company') {
            $users = User::select('id')->where('created_by', $user->id)->pluck('id');
            foreach ($users as $id) {
                try {
                    $key = 'sidebar_menu_' . $id;
                    Cache::forget($key);
                } catch (\Exception $e) {
                    \Log::error('comapnySettingCacheForget :' . $e->getMessage());
                }
            }
            try {
                $key = 'sidebar_menu_' . $user->id;
                Cache::forget($key);
            } catch (\Exception $e) {
                \Log::error('comapnySettingCacheForget :' . $e->getMessage());
            }
            return true;
        }

        try {
            $key = 'sidebar_menu_' . $user->id;
            Cache::forget($key);
        } catch (\Exception $e) {
            \Log::error('comapnySettingCacheForget :' . $e->getMessage());
        }

        return true;
    }
}

if (!function_exists('getActiveBusiness')) {
    function getActiveBusiness($user_id = null)
    {
        if (!empty($user_id)) {
            $user = User::find($user_id);
        } else {
            $user =  auth()->user();
        }

        if ($user) {
            if (!empty($user->active_business)) {
                return $user->active_business;
            } else {
                if ($user->type == 'super admin') {
                    return 0;
                } else {
                    static $business = null;
                    if ($business == null) {
                        $business = Business::where('created_by', $user->id)->first();
                    }
                    return $business->id;
                }
            }
        }
    }
}

if (!function_exists('businessIndustryIs')) {
    /**
     * Whether a business's industry matches the given name (e.g. 'Auto Repair').
     * Used to gate industry-specific features that shouldn't appear for every
     * tenant, such as Job Cards. Defaults to the currently active business.
     */
    function businessIndustryIs($industryName, $businessId = null)
    {
        $businessId = $businessId ?? getActiveBusiness();

        static $cache = [];
        if (!array_key_exists($businessId, $cache)) {
            $business = Business::find($businessId);
            $cache[$businessId] = $business ? optional($business->industry)->name : null;
        }

        return $cache[$businessId] === $industryName;
    }
}

if (!function_exists('jobCardFeatureEnabled')) {
    function jobCardFeatureEnabled($businessId = null)
    {
        return businessIndustryIs('Auto Repair', $businessId);
    }
}

if (!function_exists('getBusiness')) {
    function getBusiness()
    {
        $data = [];
        if (Auth::check()) {
            static $users = null;
            if ($users == null) {
                $users = User::where('email', Auth::user()->email)->get();
            }
            static $business = null;
            if ($business == null) {
                $business =  Business::whereIn('id', $users->pluck('business_id')->toArray())->orWhereIn('created_by', $users->pluck('id')->toArray())->where('is_disable', 1)->get();
            }
            return $business;
        } else {
            return $data;
        }
    }
}


if (!function_exists('creatorId')) {
    function creatorId()
    {
        if (Auth::user()->type == 'super admin' || Auth::user()->type == 'company') {
            return Auth::user()->id;
        } else {
            return Auth::user()->created_by;
        }
    }
}


if (!function_exists('getModuleList')) {
    function getModuleList()
    {
        $all = Module::getOrdered();
        $list = [];
        foreach ($all as $module) {
            array_push($list, $module->name);
        }
        return $list;
    }
}

if (!function_exists('getshowModuleList')) {
    function getshowModuleList()
    {
        $all = Module::getOrdered();
        $list = [];
        foreach ($all as $module) {
            if ($module->display) {
                array_push($list, $module->name);
            }
        }
        return $list;
    }
}

if (!function_exists('module_is_active')) {
    function module_is_active($module, $user_id = null)
    {
        if (Module::has($module)) {
            $isModuleActive = Module::isEnabled($module);
            if ($isModuleActive == false) {
                return false;
            }
            if (Auth::check()) {
                $user = Auth::user();
            } elseif ($user_id != null) {
                $user = User::find($user_id);
            }
            if (!empty($user)) {
                if ($user->type == 'super admin') {
                    return true;
                } else {
                    $active_module = ActivatedModule($user->id);
                    if ((count($active_module) > 0 && in_array($module, $active_module))) {
                        return true;
                    }
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('ActivatedModule')) {
    function ActivatedModule($user_id = null)
    {
        $activated_module = user::$superadmin_activated_module;

        if ($user_id != null) {
            $user = User::find($user_id);
        } elseif (Auth::check()) {
            $user = Auth::user();
        }
        if (!empty($user)) {
            $available_modules = array_values(Module::allEnabled());
            static $active_module = null;
            if ($user->type == 'super admin') {
                $user_active_module = $available_modules;
            } else {
                if ($user->type != 'company') {
                    $user_not_com = User::find($user->created_by);
                    if (!empty($user)) {
                        // Sidebar Performance Changes
                        if ($active_module == null) {
                            $active_module = userActiveModule::where('user_id', $user_not_com->id)->pluck('module')->toArray();
                        }
                    }
                } else {
                    if ($active_module == null) {
                        $active_module = userActiveModule::where('user_id', $user->id)->pluck('module')->toArray();
                    }
                }
                // Find the common modules
                $commonModules = array_intersect($active_module, $available_modules);
                $user_active_module = array_unique(array_merge($commonModules, $activated_module));
            }
        }
        return $user_active_module;
    }
}
// module alias name
if (!function_exists('Module_Alias_Name')) {
    function Module_Alias_Name($module_name)
    {
        static $addons = [];
        static $resultArray = [];
        if (count($addons) == 0 && count($resultArray) == 0) {
            $addons = Module::all();
            $resultArray = array_reduce($addons, function ($carry, $item) {
                // Check if both "name" and "alias" keys exist in the current item
                if (isset($item->name) && isset($item->alias)) {
                    // Add a new key-value pair to the result array
                    $carry[$item->name] = $item->alias;
                }
                return $carry;
            }, []);
        }

        if ($module_name === 'general' || $module_name === 'General') {
            return $module_name;
        }

        $module = Module::find($module_name);
        if (isset($resultArray)) {
            $module_name =  array_key_exists($module_name, $resultArray) ? $resultArray[$module_name] : (!empty($module) ? $module->alias : $module_name);
        } elseif (!empty($module)) {
            $module_name = $module->alias;
        }
        return $module_name;
    }
}

if (!function_exists('get_permission_by_module')) {
    function get_permission_by_module($mudule)
    {
        $user = Auth::user();

        if ($user->type == 'super admin') {
            $permissions = Permission::where('module', $mudule)->orderBy('name')->get();
        } else {
            $permissions = new Collection();
            foreach ($user->roles as $role) {
                $permissions = $permissions->merge($role->permissions);
            }
            $permissions = $permissions->where('module', $mudule);
        }
        return $permissions;
    }
}

if (!function_exists('getActiveLanguage')) {
    function getActiveLanguage()
    {
        if ((Auth::check()) && (!empty(Auth::user()->lang))) {
            return Auth::user()->lang;
        } else {
            if (in_array(\Request::route()->getName(), ['appointments.form', 'appointment.form.submit', 'appointments.done', 'appointment.duration', 'get.staff.data', 'appointment.rtl'])) {
                return 'en';
            } else {
                $admin_settings = getAdminAllSetting();
                return !empty($admin_settings['defult_language']) ? $admin_settings['defult_language'] : 'en';
            }
        }
    }
}

if (!function_exists('languages')) {
    function languages()
    {

        try {
            $arrLang = Language::where('status', 1)->get()->pluck('name', 'code')->toArray();
        } catch (\Throwable $th) {
            $arrLang = [
                "ar" => "Arabic",
                "da" => "Danish",
                "de" => "German",
                "en" => "English",
                "es" => "Spanish",
                "fr" => "French",
                "it" => "Italian",
                "ja" => "Japanese",
                "nl" => "Dutch",
                "pl" => "Polish",
                "pt" => "Portuguese",
                "ru" => "Russian",
                "tr" => "Turkish"
            ];
        }
        return $arrLang;
    }
}


// setConfigEmail ( SMTP )
if (!function_exists('SetConfigEmail')) {
    function SetConfigEmail($user_id = null, $business_id = null)
    {
        try {

            if (!empty($user_id)) {
                $company_settings = getCompanyAllSetting($user_id);
            } elseif (!empty($user_id) && !empty($business_id)) {
                $company_settings = getCompanyAllSetting($user_id, $business_id);
            } else if (Auth::check()) {
                $company_settings = getCompanyAllSetting();
            } else {
                $user_id = User::where('type', 'super admin')->first()->id;
                $company_settings = getCompanyAllSetting($user_id);
            }

            config(
                [
                    'mail.driver' => $company_settings['mail_driver'],
                    'mail.host' => $company_settings['mail_host'],
                    'mail.port' => $company_settings['mail_port'],
                    'mail.encryption' => $company_settings['mail_encryption'],
                    'mail.username' => $company_settings['mail_username'],
                    'mail.password' => $company_settings['mail_password'],
                    'mail.from.address' => $company_settings['mail_from_address'],
                    'mail.from.name' => $company_settings['mail_from_name'],
                ]
            );
            return true;
        } catch (\Exception $e) {

            return false;
        }
    }
}

// file upload

if (!function_exists('upload_file')) {
    function upload_file($request, $key_name, $name, $path, $custom_validation = [])
    {
        try {
            $storage_settings = getAdminAllSetting();
            if (isset($storage_settings['storage_setting'])) {
                if ($storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key' => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root' => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url']
                        ]
                    );
                    $max_size = !empty($storage_settings['wasabi_max_upload_size']) ? $storage_settings['wasabi_max_upload_size'] : '2048';
                    $mimes =  !empty($storage_settings['wasabi_storage_validation']) ? $storage_settings['wasabi_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                } else if ($storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key' => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ]
                    );
                    $max_size = !empty($storage_settings['s3_max_upload_size']) ? $storage_settings['s3_max_upload_size'] : '2048';
                    $mimes =  !empty($storage_settings['s3_storage_validation']) ? $storage_settings['s3_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                } else {
                    $max_size = !empty($storage_settings['local_storage_max_upload_size']) ? $storage_settings['local_storage_max_upload_size'] : '2048';
                    $mimes =  !empty($storage_settings['local_storage_validation']) ? $storage_settings['local_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                }
                if (is_array($request)) {
                    $request = new Illuminate\Http\Request($request);
                }
                $file = $request->$key_name;

                $extension = strtolower($file->getClientOriginalExtension());
                $allowed_extensions = explode(',', $mimes);
                if (empty($extension) || !in_array($extension, $allowed_extensions)) {
                    return [
                        'flag' => 0,
                        'msg' => 'The ' . $key_name . ' must be a file of type: ' . implode(', ', $allowed_extensions) . '.',
                    ];
                }

                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {
                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = Validator::make($request->all(), [
                    $key_name => $validation
                ]);
                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];
                    return $res;
                } else {
                    $name = $name;
                    $save = Storage::disk($storage_settings['storage_setting'])->putFileAs(
                        $path,
                        $file,
                        $name
                    );
                    if ($storage_settings['storage_setting'] == 'wasabi') {
                        $url = $save;
                    } elseif ($storage_settings['storage_setting'] == 's3') {
                        $url = $save;
                    } else {
                        $url = 'uploads/' . $save;
                    }
                    $res = [
                        'flag' => 1,
                        'msg'  => 'success',
                        'url'  => $url
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => 'not set configurations',
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }
}

if (!function_exists('multi_upload_file')) {
    function multi_upload_file($request, $key_name, $name, $path, $custom_validation = [])
    {
        try {
            $storage_settings = getAdminAllSetting();

            if (isset($storage_settings['storage_setting'])) {
                if ($storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key' => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root' => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url']
                        ]
                    );
                    $max_size = !empty($storage_settings['wasabi_max_upload_size']) ? $storage_settings['wasabi_max_upload_size'] : '2048';
                    $mimes =  !empty($storage_settings['wasabi_storage_validation']) ? $storage_settings['wasabi_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                } else if ($storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key' => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ]
                    );
                    $max_size = !empty($storage_settings['s3_max_upload_size']) ? $storage_settings['s3_max_upload_size'] : '2048';
                    $mimes =  !empty($storage_settings['s3_storage_validation']) ? $storage_settings['s3_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                } else {
                    $max_size = !empty($storage_settings['local_storage_max_upload_size']) ? $storage_settings['local_storage_max_upload_size'] : '2048';
                    $mimes =  !empty($storage_settings['local_storage_validation']) ? $storage_settings['local_storage_validation'] : 'jpeg,jpg,png,svg,zip,txt,gif,docx';
                }

                $file = $request;
                $key_validation = $key_name . '*';

                $extension = strtolower($file->getClientOriginalExtension());
                $allowed_extensions = explode(',', $mimes);
                if (empty($extension) || !in_array($extension, $allowed_extensions)) {
                    return [
                        'flag' => 0,
                        'msg' => 'The ' . $key_name . ' must be a file of type: ' . implode(', ', $allowed_extensions) . '.',
                    ];
                }

                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {
                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = Validator::make(array($key_name => $request), [
                    $key_validation => $validation
                ]);
                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];


                    return $res;
                } else {

                    $name = $name;

                    $save = Storage::disk($storage_settings['storage_setting'])->putFileAs(
                        $path,
                        $file,
                        $name
                    );

                    if ($storage_settings['storage_setting'] == 'wasabi') {
                        $url = $save;
                    } elseif ($storage_settings['storage_setting'] == 's3') {
                        $url = $save;
                    } else {
                        $url = 'uploads/' . $save;
                    }
                    $res = [
                        'flag' => 1,
                        'msg'  => 'success',
                        'url'  => $url
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => 'not set configration',
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }
}

if (!function_exists('check_file')) {
    function check_file($path)
    {
        if (!empty($path)) {
            $storage_settings = getAdminAllSetting();
            if (isset($storage_settings['storage_setting']) == null || $storage_settings['storage_setting'] == 'local') {

                return file_exists(base_path($path));
            } else {

                if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key' => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ]
                    );
                } else if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key' => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root' => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url']
                        ]
                    );
                }
                try {
                    return  Storage::disk($storage_settings['storage_setting'])->exists($path);
                } catch (\Throwable $th) {
                    return 0;
                }
            }
        } else {
            return 0;
        }
    }
}

if (!function_exists('get_file')) {
    function get_file($path)
    {

        $storage_settings = getAdminAllSetting();

        if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 's3') {
            config(
                [
                    'filesystems.disks.s3.key' => $storage_settings['s3_key'],
                    'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                    'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                    'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                    // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                    // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                ]
            );
            return Storage::disk('s3')->url($path);
        } else if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 'wasabi') {
            config(
                [
                    'filesystems.disks.wasabi.key' => $storage_settings['wasabi_key'],
                    'filesystems.disks.wasabi.secret' => $storage_settings['wasabi_secret'],
                    'filesystems.disks.wasabi.region' => $storage_settings['wasabi_region'],
                    'filesystems.disks.wasabi.bucket' => $storage_settings['wasabi_bucket'],
                    'filesystems.disks.wasabi.root' => $storage_settings['wasabi_root'],
                    'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url']
                ]
            );

            return Storage::disk('wasabi')->url($path);
        } else {
            return asset($path);
        }
    }
}
if (!function_exists('get_base_file')) {
    function get_base_file($path)
    {
        $admin_settings = getAdminAllSetting();
        if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 's3') {
            config(
                [
                    'filesystems.disks.s3.key' => $admin_settings['s3_key'],
                    'filesystems.disks.s3.secret' => $admin_settings['s3_secret'],
                    'filesystems.disks.s3.region' => $admin_settings['s3_region'],
                    'filesystems.disks.s3.bucket' => $admin_settings['s3_bucket'],
                    // 'filesystems.disks.s3.url' => $admin_settings['s3_url'],
                    // 'filesystems.disks.s3.endpoint' => $admin_settings['s3_endpoint'],
                ]
            );

            return Storage::disk('s3')->url($path);
        } else if (isset($storage_settings['storage_setting']) && $storage_settings['storage_setting'] == 'wasabi') {
            config(
                [
                    'filesystems.disks.wasabi.key' => $admin_settings['wasabi_key'],
                    'filesystems.disks.wasabi.secret' => $admin_settings['wasabi_secret'],
                    'filesystems.disks.wasabi.region' => $admin_settings['wasabi_region'],
                    'filesystems.disks.wasabi.bucket' => $admin_settings['wasabi_bucket'],
                    'filesystems.disks.wasabi.root' => $admin_settings['wasabi_root'],
                    'filesystems.disks.wasabi.endpoint' => $admin_settings['wasabi_url']
                ]
            );
            return Storage::disk('wasabi')->url($path);
        } else {
            return base_path($path);
        }
    }
}
if (!function_exists('delete_file')) {
    function delete_file($path)
    {
        if (check_file($path)) {
            $storage_settings = getAdminAllSetting();
            if (isset($storage_settings['storage_setting'])) {
                if ($storage_settings['storage_setting'] == 'local') {
                    return File::delete($path);
                } else {
                    if ($storage_settings['storage_setting'] == 's3') {
                        config(
                            [
                                'filesystems.disks.s3.key' => $storage_settings['s3_key'],
                                'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                                'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                                'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                                // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                                // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                            ]
                        );
                    } else if ($storage_settings['storage_setting'] == 'wasabi') { {
                            config(
                                [
                                    'filesystems.disks.wasabi.key' => $storage_settings['wasabi_key'],
                                    'filesystems.disks.wasabi.secret' => $storage_settings['wasabi_secret'],
                                    'filesystems.disks.wasabi.region' => $storage_settings['wasabi_region'],
                                    'filesystems.disks.wasabi.bucket' => $storage_settings['wasabi_bucket'],
                                    'filesystems.disks.wasabi.root' => $storage_settings['wasabi_root'],
                                    'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url']
                                ]
                            );
                        }
                        return Storage::disk($storage_settings['storage_setting'])->delete($path);
                    }
                }
            }
        }
    }
}

if (!function_exists('get_size')) {
    function get_size($url)
    {
        $url = str_replace(' ', '%20', $url);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }
}
if (!function_exists('delete_folder')) {
    function delete_folder($path)
    {
        $storage_settings = getAdminAllSetting();
        if (isset($storage_settings['storage_setting'])) {

            if ($storage_settings['storage_setting'] == 'local') {
                if (is_dir(Storage::path($path))) {
                    return \File::deleteDirectory(Storage::path($path));
                }
            } else {
                if ($storage_settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key' => $storage_settings['s3_key'],
                            'filesystems.disks.s3.secret' => $storage_settings['s3_secret'],
                            'filesystems.disks.s3.region' => $storage_settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $storage_settings['s3_bucket'],
                            // 'filesystems.disks.s3.url' => $storage_settings['s3_url'],
                            // 'filesystems.disks.s3.endpoint' => $storage_settings['s3_endpoint'],
                        ]
                    );
                } else if ($storage_settings['storage_setting'] == 'wasabi') {
                    config(
                        [
                            'filesystems.disks.wasabi.key' => $storage_settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $storage_settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $storage_settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $storage_settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.root' => $storage_settings['wasabi_root'],
                            'filesystems.disks.wasabi.endpoint' => $storage_settings['wasabi_url']
                        ]
                    );
                }
                return Storage::disk($storage_settings['storage_setting'])->deleteDirectory($path);
            }
        }
    }
}
if (!function_exists('delete_directory')) {
    function delete_directory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
if (!function_exists('currency')) {
    function currency($code = null)
    {
        if ($code == null) {
            $c = Currency::get();
        } else {
            $c = Currency::where('code', $code)->first();
        }
        return $c;
    }
}

// Company Subscription Details
if (!function_exists('SubscriptionDetails')) {
    function SubscriptionDetails($user_id = null)
    {
        $data = [];
        $data['status'] = false;
        if ($user_id != null) {
            $user = User::find($user_id);
        } elseif (\Auth::check()) {
            $user = \Auth::user();
        }

        if (isset($user) && !empty($user)) {
            if ($user->type != 'company' && $user->type != 'super admin') {
                $user = User::find($user->created_by);
            }

            if (!empty($user)) {
                if ($user->active_plan != 0) {
                    $data['status'] = true;
                    $data['active_plan'] = $user->active_plan;
                    $data['billing_type'] = $user->billing_type;
                    $data['plan_expire_date'] = $user->plan_expire_date;
                    $data['active_module'] = ActivatedModule();
                    $data['total_user'] = $user->total_user == -1 ? 'Unlimited' : (isset($user->total_user) ? $user->total_user : 'Unlimited');
                    $data['total_business'] = $user->total_business == -1 ? 'Unlimited' : (isset($user->total_business) ? $user->total_business : 'Unlimited');
                    $data['seeder_run'] = $user->seeder_run;
                }
            }
        }
        return $data;
    }
}


if (!function_exists('PlanCheck')) {
    function PlanCheck($type = 'User', $id = null)
    {
        if (!empty($id)) {
            $user = User::where('id', $id)->first();
            if ($user->type == 'company') {
                $id = $user->id;
            } else {
                $user = User::where('id', $user->created_by)->first();
                $id = $user->id;
            }
        } else {
            $user = \Auth::user();
            if ($user->type == 'company') {
                $id = $user->id;
            } else {
                $user = User::where('id', $user->created_by)->first();
                $id = $user->id;
            }
        }
        if ($type == "User") {
            if ($user->total_user >= 0) {
                if ($user->type == 'company') {
                    $users = User::where('created_by', $id)->where('business_id', getActiveBusiness())->whereNotIn('type', ['staff', 'customer'])->get();
                } else {
                    $users = User::where('created_by', $user->created_by)->get();
                }
                if ($users->count() >= $user->total_user) {
                    return false;
                } else {
                    return true;
                }
            } elseif ($user->total_user < 0) {
                return true;
            }
        }
        if ($type == "Business") {
            if ($user->total_business >= 0) {
                $business = Business::where('created_by', $id)->get();
                if ($business->count() >= $user->total_business) {
                    return false;
                } else {
                    return true;
                }
            } elseif ($user->total_business < 0) {
                return true;
            }
        }
    }
}
if (!function_exists('CheckCoupon')) {
    function CheckCoupon($code, $price = 0)
    {
        if (!empty($code) && intval($price) > 0) {
            $coupons = Coupon::where('code', strtoupper($code))->where('is_active', '1')->first();
            if (!empty($coupons)) {
                $usedCoupun     = $coupons->used_coupon();
                $discount_value = ($price / 100) * $coupons->discount;
                $final_price          = $price - $discount_value;

                if ($coupons->limit == $usedCoupun) {
                    return $price;
                } else {
                    return $final_price;
                }
            } else {
                return $price;
            }
        }
    }
}

if (!function_exists('UserCoupon')) {
    function UserCoupon($code, $orderID, $user_id = null)
    {

        if (!empty($code)) {
            $coupons = Coupon::where('code', strtoupper($code))->where('is_active', '1')->first();
            if ($user_id) {
                $user = User::find($user_id);
            } else {
                $user = \Auth::user();
            }
            if (!empty($coupons)) {
                $userCoupon         = new UserCoupon();
                $userCoupon->user   = $user->id;
                $userCoupon->coupon = $coupons->id;
                $userCoupon->order  = $orderID;
                $userCoupon->save();

                $usedCoupun = $coupons->used_coupon();
                if ($coupons->limit <= $usedCoupun) {
                    $coupons->is_active = 0;
                    $coupons->save();
                }
            }
        }
    }
}

// if Subscription price is 0 then call this
if (!function_exists('DirectAssignPlan')) {
    function DirectAssignPlan($plan_id, $duration, $user_module, $counter, $type, $coupon_code = null, $user_id = null)
    {
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $plan = Plan::find($plan_id);
        if (empty($user_id)) {
            $user_id = \Auth::user()->id;
        }
        $user = User::find($user_id);
        $assignPlan = $user->assignPlan($plan->id, $duration, $user_module, $counter, $user_id);
        if ($assignPlan['is_success']) {
            $order = Order::create(
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
                    'price_currency' => admin_setting('defult_currancy'),
                    'txn_id' => '',
                    'payment_type' => !empty($type) ? $type : "STRIPE",
                    'payment_status' => 'succeeded',
                    'receipt' => null,
                    'user_id' => $user_id,
                ]
            );
            if ($coupon_code) {

                UserCoupon($coupon_code, $order);
            }
            return ['is_success' => true];
        } else {
            return ['is_success' => false];
        }
    }
}
// if (!function_exists('makeEmailLang'))
// {
//     function makeEmailLang($lang)
//     {
//         $templates = EmailTemplate::all();
//         foreach ($templates as $template) {

//             $default_lang  = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', 'en')->first();

//             $emailTemplateLang              = new EmailTemplateLang();
//             $emailTemplateLang->parent_id   = $template->id;
//             $emailTemplateLang->lang        = $lang;
//             $emailTemplateLang->subject     = $default_lang->subject;
//             $emailTemplateLang->content     = $default_lang->content;
//             $emailTemplateLang->variables   = $default_lang->variables;
//             $emailTemplateLang->save();
//         }
//     }
// }
if (!function_exists('error_res')) {
    function error_res($msg = "", $args = array())
    {
        $msg       = $msg == "" ? "error" : $msg;
        $msg_id    = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg       = $msg_id == $converted ? $msg : $converted;
        $json      = array(
            'flag' => 0,
            'msg' => $msg,
        );

        return $json;
    }
}

if (!function_exists('success_res')) {
    function success_res($msg = "", $args = array())
    {
        $msg       = $msg == "" ? "success" : $msg;
        $json      = array(
            'flag' => 1,
            'msg' => $msg,
        );

        return $json;
    }
}

if (!function_exists('GetDeviceType')) {
    function GetDeviceType($user_agent)
    {
        $mobile_regex = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tablet_regex = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';
        if (preg_match_all($mobile_regex, $user_agent)) {
            return 'mobile';
        } else {
            if (preg_match_all($tablet_regex, $user_agent)) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }
    }
}

// Get Cache Size
if (!function_exists('CacheSize')) {
    function CacheSize()
    {
        //start for cache clear
        $file_size = 0;
        foreach (\File::allFiles(storage_path('/framework')) as $file) {
            $file_size += $file->getSize();
        }
        $file_size = number_format($file_size / 1000000, 4);

        return $file_size;
    }
}

if (!function_exists('get_module_img')) {
    function get_module_img($module)
    {
        $module = Module::find($module);
        return $module->image;
    }
}

if (!function_exists('sidebar_logo')) {
    function sidebar_logo()
    {
        $admin_settings = getAdminAllSetting();
        if (\Auth::check() && (\Auth::user()->type != 'super admin')) {
            $company_settings = getCompanyAllSetting();

            if ((isset($company_settings['cust_darklayout']) ? $company_settings['cust_darklayout'] : 'off') == 'on') {
                if (!empty($company_settings['logo_light'])) {
                    if (check_file($company_settings['logo_light'])) {
                        return $company_settings['logo_light'];
                    } else {
                        return 'uploads/logo/logo_light.png';
                    }
                } else {
                    if (!empty($admin_settings['logo_light'])) {
                        if (check_file($admin_settings['logo_light'])) {
                            return $admin_settings['logo_light'];
                        } else {
                            return 'uploads/logo/logo_light.png';
                        }
                    } else {
                        return 'uploads/logo/logo_light.png';
                    }
                }
            } else {
                if (!empty($company_settings['logo_dark'])) {
                    if (check_file($company_settings['logo_dark'])) {
                        return $company_settings['logo_dark'];
                    } else {
                        return 'uploads/logo/logo_dark.png';
                    }
                } else {
                    if (!empty($admin_settings['logo_dark'])) {
                        if (check_file($admin_settings['logo_dark'])) {
                            return $admin_settings['logo_dark'];
                        } else {
                            return 'uploads/logo/logo_dark.png';
                        }
                    } else {
                        return 'uploads/logo/logo_dark.png';
                    }
                }
            }
        } else {
            if ((isset($admin_settings['cust_darklayout']) ? $admin_settings['cust_darklayout'] : 'off') == 'on') {
                if (!empty($admin_settings['logo_light'])) {
                    if (check_file($admin_settings['logo_light'])) {
                        return $admin_settings['logo_light'];
                    } else {
                        return 'uploads/logo/logo_light.png';
                    }
                } else {
                    return 'uploads/logo/logo_light.png';
                }
            } else {
                if (!empty($admin_settings['logo_dark'])) {
                    if (check_file($admin_settings['logo_dark'])) {
                        return $admin_settings['logo_dark'];
                    } else {
                        return 'uploads/logo/logo_dark.png';
                    }
                } else {
                    return 'uploads/logo/logo_dark.png';
                }
            }
        }
    }
}

if (!function_exists('light_logo')) {
    function light_logo()
    {
        if (\Auth::check()) {
            $company_settings = getCompanyAllSetting();
            $logo_light = isset($company_settings['logo_light']) ? $company_settings['logo_light'] : 'uploads/logo/logo_light.png';
        } else {
            $admin_settings = getAdminAllSetting();
            $logo_light = isset($admin_settings['logo_light']) ? $admin_settings['logo_light'] : 'uploads/logo/logo_light.png';
        }
        if (check_file($logo_light)) {
            return $logo_light;
        } else {
            return 'uploads/logo/logo_dark.png';
        }
    }
}

if (!function_exists('dark_logo')) {
    function dark_logo()
    {
        if (\Auth::check()) {
            $company_settings = getCompanyAllSetting();
            $logo_dark = isset($company_settings['logo_dark']) ? $company_settings['logo_dark'] : 'uploads/logo/logo_dark.png';
        } else {
            $admin_settings = getAdminAllSetting();
            $logo_dark = isset($admin_settings['logo_dark']) ? $admin_settings['logo_dark'] : 'uploads/logo/logo_dark.png';
        }
        if (check_file($logo_dark)) {
            return $logo_dark;
        } else {
            return 'uploads/logo/logo_dark.png';
        }
    }
}

if (!function_exists('currency_format')) {
    function currency_format($price, $company_id = null, $business = null)
    {

        return number_format($price, company_setting('currency_format', $company_id, $business), '.', '');
    }
}

if (!function_exists('currency_format_with_sym')) {

    function currency_format_with_sym($price, $company_id = null, $business = null)
    {
        if (!empty($company_id) && empty($business)) {
            $company_settings = getCompanyAllSetting($company_id);
        } elseif (!empty($company_id) && !empty($business)) {
            $company_settings = getCompanyAllSetting($company_id, $business);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $symbol_position = 'pre';
        $symbol = '$';
        $format = '1';
        $currency_space = null;
        $number = explode('.', $price);
        $length = strlen(trim($number[0]));

        if (isset($company_settings['site_currency_symbol_position']) && $company_settings['site_currency_symbol_position'] == "post") {
            $symbol_position = 'post';
        }

        if (isset($company_settings['defult_currancy_symbol'])) {
            $symbol = $company_settings['defult_currancy_symbol'];
        }

        if (isset($company_settings['currency_format'])) {
            $format = $company_settings['currency_format'];
        }

        if ($length > 3) {
            $decimal_separator  = isset($company_settings['float_number']) && $company_settings['float_number'] === 'dot' ? '.' : ',';
            $thousand_separator = isset($company_settings['thousand_separator']) && $company_settings['thousand_separator'] === 'dot' ? '.' : ',';
        } else {
            $decimal_separator  = isset($company_settings['decimal_separator']) && $company_settings['decimal_separator'] === 'dot'  ? '.' : ',';
            $thousand_separator = isset($company_settings['thousand_separator']) && $company_settings['thousand_separator'] === 'dot' ? '.' : ',';
        }

        if (isset($company_settings['currency_space'])) {
            $currency_space = isset($company_settings['currency_space']) ? $company_settings['currency_space'] : '';
        }
        if (isset($company_settings['site_currency_symbol_name'])) {
            $defult_currancy = $company_settings['defult_currancy'];
            $defult_currancy_symbol = $company_settings['defult_currancy_symbol'];
            $symbol = $company_settings['site_currency_symbol_name'] == 'symbol' ? $defult_currancy_symbol : $defult_currancy;
        }
        $price = number_format($price, $format, $decimal_separator, $thousand_separator);

        return (($symbol_position == "pre") ? $symbol : '') . ($currency_space == 'withspace' ? ' ' : '') . $price . ($currency_space == 'withspace' ? ' ' : '') . (($symbol_position == "post") ? $symbol : '');
     }

}




if (!function_exists('company_date_formate')) {
    function company_date_formate($date, $company_id = null, $business = null)
    {

        if (!empty($company_id) && empty($business)) {
            $company_settings = getCompanyAllSetting($company_id);
        } elseif (!empty($company_id) && !empty($business)) {
            $company_settings = getCompanyAllSetting($company_id, $business);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $date_formate = !empty($company_settings['site_date_format']) ? $company_settings['site_date_format'] : 'd-m-y';

        return date($date_formate, strtotime($date));
    }
}

if (!function_exists('super_currency_format_with_sym')) {
    function super_currency_format_with_sym($price)
    {
        $admin_settings = getAdminAllSetting();

        $symbol_position = 'pre';
        $symbol = '$';
        $format = '1';
        $currency_space = null;
        $number = explode('.', $price);
        $length = strlen(trim($number[0]));

        if (isset($admin_settings['site_currency_symbol_position']) && $admin_settings['site_currency_symbol_position'] == "post") {
            $symbol_position = 'post';
        }

        if (isset($admin_settings['defult_currancy_symbol'])) {
            $symbol = $admin_settings['defult_currancy_symbol'];
        }

        if (isset($admin_settings['currency_format'])) {
            $format = $admin_settings['currency_format'];
        }

        if ($length > 3) {
            $decimal_separator  = isset($admin_settings['float_number']) && $admin_settings['float_number'] === 'dot' ? '.' : ',';
            $thousand_separator = isset($admin_settings['thousand_separator']) && $admin_settings['thousand_separator'] === 'dot' ? '.' : ',';
        } else {
            $decimal_separator  = isset($admin_settings['decimal_separator']) && $admin_settings['decimal_separator'] === 'dot'  ? '.' : ',';
            $thousand_separator = isset($admin_settings['thousand_separator']) && $admin_settings['thousand_separator'] === 'dot' ? '.' : ',';
        }

        if (isset($admin_settings['currency_space'])) {
            $currency_space = isset($admin_settings['currency_space']) ? $admin_settings['currency_space'] : '';
        }
        if (isset($admin_settings['site_currency_symbol_name'])) {
            $defult_currancy = $admin_settings['defult_currancy'];
            $defult_currancy_symbol = $admin_settings['defult_currancy_symbol'];
            $symbol = $admin_settings['site_currency_symbol_name'] == 'symbol' ? $defult_currancy_symbol : $defult_currancy;
        }
        $price = number_format($price, $format, $decimal_separator, $thousand_separator);

        return (($symbol_position == "pre") ? $symbol : '') . ($currency_space == 'withspace' ? ' ' : '') . $price . ($currency_space == 'withspace' ? ' ' : '') . (($symbol_position == "post") ? $symbol : '');
    }

}
if (!function_exists('company_datetime_formate')) {
    function company_datetime_formate($date, $company_id = null, $business = null)
    {
        $company_settings = getCompanyAllSetting($company_id, $business);
        $date_formate = !empty($company_settings['site_date_format']) ? $company_settings['site_date_format'] : 'd-m-y';
        $time_formate = !empty($company_settings['site_time_format']) ? $company_settings['site_time_format'] : 'H:i';
        return date($date_formate . ' ' . $time_formate, strtotime($date));
    }
}
if (!function_exists('company_Time_formate')) {
    function company_Time_formate($time, $company_id = null, $business = null)
    {
        if (!empty($company_id) && empty($business)) {
            $company_settings = getCompanyAllSetting($company_id);
        } elseif (!empty($company_id) && !empty($business)) {
            $company_settings = getCompanyAllSetting($company_id, $business);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $time_formate = !empty($company_settings['site_time_format']) ? $company_settings['site_time_format'] : 'H:i';
        return date($time_formate, strtotime($time));
    }
}
// module price name
if (!function_exists('ModulePriceByName')) {
    function ModulePriceByName($module_name)
    {
        static $addons = [];
        static $resultArray = [];
        if (count($addons) == 0 && count($resultArray) == 0) {
            $addons = AddOn::all()->toArray();
            $resultArray = array_reduce($addons, function ($carry, $item) {
                // Check if both "module" and "name" keys exist in the current item
                if (isset($item['module'])) {
                    // Add a new key-value pair to the result array
                    $carry[$item['module']]['monthly_price'] = $item['monthly_price'];
                    $carry[$item['module']]['yearly_price'] = $item['yearly_price'];
                }
                return $carry;
            }, []);
        }

        $module = Module::find($module_name);
        $data = [];
        $data['monthly_price'] = 0;
        $data['yearly_price'] = 0;
        if (!empty($module)) {
            $path = $module->getPath() . '/module.json';
            $json = json_decode(file_get_contents($path), true);

            $data['monthly_price'] = (isset($json['monthly_price']) && !empty($json['monthly_price'])) ? $json['monthly_price'] : 0;
            $data['yearly_price'] = (isset($json['yearly_price']) && !empty($json['yearly_price'])) ? $json['yearly_price'] : 0;
        }

        if (isset($resultArray)) {
            $data['monthly_price'] = isset($resultArray[$module_name]['monthly_price']) ? $resultArray[$module_name]['monthly_price'] : $data['monthly_price'];
            $data['yearly_price'] = isset($resultArray[$module_name]['yearly_price']) ? $resultArray[$module_name]['yearly_price'] : $data['yearly_price'];
        }

        return $data;
    }
}


// if (!function_exists('timeSlot')) {
//     function timeSlot($serviceId = null, $date = null, $flexibleData = null)
//     {
//         $service = Service::find($serviceId);
//         $company_settings = getCompanyAllSetting($service->created_by, $service->business_id);
//         $maximum_slot = isset($company_settings['maximum_slot']) ? $company_settings['maximum_slot'] : '1';

//         if ($date && !empty($service)) {
//             $booked_appointment = Appointment::where('service_id', $serviceId)->where('date', $date)->where('business_id', $service->business_id)->where('created_by', $service->created_by)->select('time')->get()->toArray();

//             $selectedDate = Carbon::createFromFormat('d-m-Y', $date);
//             $dayName = $selectedDate->format('l');                              //get dayname using date

//             $businessday = BusinessHours::where('created_by', $service->created_by)->where('business_id', $service->business_id)->where('day_name', $dayName)->first();

//             $duration = $service->duration;
//             $start_time = Carbon::createFromFormat('H:i:s', isset($businessday->start_time) ? $businessday->start_time : '09:30:00');
//             $end_time = Carbon::createFromFormat('H:i:s', isset($businessday->end_time) ? $businessday->end_time : '18:00:00');
//             $break_times = isset($businessday->break_hours) ? json_decode($businessday->break_hours, true) : '';

//             $timeSlots = [];
//             $currentSlot = clone $start_time;
//             if (is_array($break_times)) {
//                 foreach ($break_times as $break) {
//                     $breakStart = Carbon::createFromFormat('H:i', $break['start']);
//                     $breakEnd = Carbon::createFromFormat('H:i', $break['end']);

//                     // Add time slots before the break, excluding booked slots
//                     while ($currentSlot->addMinutes($duration)->lt($breakStart)) {
//                         $slot = [
//                             'start' => $currentSlot->copy()->subMinutes($duration)->format('H:i'),
//                             'end' => $currentSlot->format('H:i'),
//                             'service_id' => $service->id
//                         ];

//                         $bookedCount = isSlotBooked($slot, $booked_appointment);
//                         if ($bookedCount < $maximum_slot) {
//                             $timeSlots[] = $slot;
//                         }
//                     }

//                     // Skip time slots during the break
//                     if ($currentSlot->lte($breakEnd)) {
//                         $currentSlot = $breakEnd->copy();
//                     }
//                 }
//             }

//             // Add remaining time slots after the last break, excluding booked slots
//             while ($currentSlot->addMinutes($duration)->lte($end_time)) {
//                 $slot = [
//                     'start' => $currentSlot->copy()->subMinutes($duration)->format('H:i'),
//                     'end' => $currentSlot->format('H:i'),
//                     'service_id' => $service->id
//                 ];

//                 $bookedCount = isSlotBooked($slot, $booked_appointment);
//                 if ($bookedCount < $maximum_slot) {
//                     $timeSlots[] = $slot;
//                 }
//             }

//             if (module_is_active('FlexibleHours') && $flexibleData->isNotEmpty()) {
//                 $selectedDate = Carbon::createFromFormat('d-m-Y', $date);
//                 $dayName = $selectedDate->format('D');

//                 $filtered_flexible_data = $flexibleData->filter(function ($flexible_day) use ($dayName) {
//                     $flexible_data = json_decode($flexible_day->days, true);
//                     return isset($flexible_data[$dayName]) && $flexible_data[$dayName] === 'on';
//                 });

//                 foreach ($filtered_flexible_data as $flexible_day) {
//                     $start_time = Carbon::createFromFormat('H:i:s', $flexible_day->start_time);
//                     $end_time = Carbon::createFromFormat('H:i:s', $flexible_day->end_time);
//                     $flexible_id = $flexible_day->id;

//                     foreach ($timeSlots as $key => $slot) {
//                         $slotStart = Carbon::createFromFormat('H:i', $slot['start']);
//                         $slotEnd = Carbon::createFromFormat('H:i', $slot['end']);

//                         if ($slotStart >= $start_time && $slotEnd <= $end_time) {
//                             unset($timeSlots[$key]);
//                         } elseif (($slotStart < $end_time && $slotEnd > $start_time)) {
//                             if ($slotStart < $start_time) {
//                                 $timeSlots[$key]['end'] = $start_time->format('H:i');
//                             } elseif ($slotEnd > $end_time) {
//                                 $timeSlots[$key]['start'] = $end_time->format('H:i');
//                             }
//                         }
//                     }

//                     $slot = [
//                         'start' => $start_time->format('H:i'),
//                         'end' => $end_time->format('H:i'),
//                         'flexible_id' => $flexible_id,
//                     ];
//                     $bookedCount = isSlotBooked($slot, $booked_appointment);
//                     if ($bookedCount < $maximum_slot) {
//                         $timeSlots[] = $slot;
//                     }
//                 }
//                 usort($timeSlots, function ($a, $b) {
//                     return strtotime($a['start']) - strtotime($b['start']);
//                 });
//             }

//             return array_values($timeSlots);
//         }
//     }
// }

if (!function_exists('timeSlot')) {
    function timeSlot($serviceId = null, $date = null, $flexibleData = null,$service_data = null,$staff_id = null, $duration = null)
    {
        $service = Service::find($serviceId);
        $company_settings = getCompanyAllSetting($service->created_by, $service->business_id);
        $maximum_slot = isset($company_settings['maximum_slot']) ? $company_settings['maximum_slot'] : '1';

        if ($date && !empty($service)) {
            $booked_appointment = Appointment::where('service_id', $serviceId)->where('date', $date)->where('business_id', $service->business_id)->where('staff_id', $staff_id)->where('created_by', $service->created_by)->select('time')->get()->toArray();

            $selectedDate = Carbon::createFromFormat('d-m-Y', $date);
            $dayName = $selectedDate->format('l');                              //get dayname using date

            $businessday = BusinessHours::where('created_by', $service->created_by)->where('business_id', $service->business_id)->where('day_name', $dayName)->first();

            // Roster activation (RosterService::ACTIVATION_DATE): before that
            // date the roster is not consulted at all and $businessday
            // (legacy) stays authoritative untouched. From that date on the
            // roster is authoritative and legacy hours are never used as a
            // fallback — a staff member with no covering shift is simply
            // unavailable that day.
            $staffMember = !empty($staff_id) ? \App\Models\Staff::where('user_id', $staff_id)->first() : null;
            $roster = app(\App\Services\RosterService::class);

            if (!empty($staffMember) && $roster->isActivated($selectedDate)) {
                $rosterHours = $roster->hoursForStaffOnDate($staffMember, $selectedDate);

                if ($rosterHours === null) {
                    return [];
                }

                $businessday = (object) [
                    'start_time' => $rosterHours['start'] . ':00',
                    'end_time' => $rosterHours['end'] . ':00',
                    'day_off' => 'off',
                    'break_hours' => isset($businessday->break_hours) ? $businessday->break_hours : null,
                ];
            }

            if (!empty($businessday) && $businessday->day_off == 'on') {
                return [];
            }

            if($duration == null){
                $duration = $service->duration;
            }

            $start_time = Carbon::createFromFormat('H:i:s', isset($businessday->start_time) ? $businessday->start_time : '09:30:00');
            $end_time = Carbon::createFromFormat('H:i:s', isset($businessday->end_time) ? $businessday->end_time : '18:00:00');
            $break_times = isset($businessday->break_hours) ? json_decode($businessday->break_hours, true) : '';

            $timeSlots = [];
            $currentSlot = clone $start_time;
            // $now = Carbon::now($company_settings['defult_timezone'])->format('H:i');    //get current time
            $now = Carbon::now($company_settings['defult_timezone']);
            $isToday = $selectedDate->isToday();

            // If the selected date is today, use current time as the cutoff
            if ($isToday) {
                $now = $now->format('H:i');
            } else {
                // If the date is tomorrow or later, ignore the current time and start from business start time
                $now = $start_time->format('H:i');
            }

            if (is_array($break_times)) {
                foreach ($break_times as $break) {
                    $breakStart = Carbon::createFromFormat('H:i', $break['start']);
                    $breakEnd = Carbon::createFromFormat('H:i', $break['end']);
                    // Add time slots before the break, excluding booked slots
                    while ($currentSlot->addMinutes((int) $duration)->lt($breakStart)) {
                        $slot = [
                            'start' => $currentSlot->copy()->subMinutes((int) $duration)->format('H:i'),
                            'end' => $currentSlot->format('H:i'),
                            'service_id' => $service->id
                        ];
                        // Skip slots before the current time
                        if ($currentSlot->lt($now)) {
                            continue;
                        }

                        $bookedCount = isSlotBooked($slot, $booked_appointment);
                        if ($bookedCount < $maximum_slot) {
                            $timeSlots[] = $slot;
                        }
                    }
                    // Skip time slots during the break
                    if ($currentSlot->lte($breakEnd)) {
                        $currentSlot = $breakEnd->copy();
                    }
                }
            }


            // Add remaining time slots after the last break, excluding booked slots
            while ($currentSlot->addMinutes((int) $duration)->lte($end_time)) {
                $slot = [
                    'start' => $currentSlot->copy()->subMinutes((int) $duration)->format('H:i'),
                    'end' => $currentSlot->format('H:i'),
                    'service_id' => $service->id
                ];

                // Skip slots before the current time
                if ($currentSlot->lt($now)) {
                    continue;
                }

                $bookedCount = isSlotBooked($slot, $booked_appointment);
                if ($bookedCount < $maximum_slot) {
                    $timeSlots[] = $slot;
                }
            }
            // Special hours
            if (module_is_active('FlexibleHours', $service->created_by) && !is_null($flexibleData)) {
                $selectedDate = Carbon::createFromFormat('d-m-Y', $date);
                $dayName = $selectedDate->format('D');

                $filtered_flexible_data = $flexibleData->filter(function ($flexible_day) use ($dayName) {
                    $flexible_data = json_decode($flexible_day->days, true);
                    return isset($flexible_data[$dayName]) && $flexible_data[$dayName] === 'on';
                });
                foreach ($filtered_flexible_data as $data) {
                    $startSpecial = Carbon::createFromFormat('H:i:s', $data->start_time);
                    $endSpecial = Carbon::createFromFormat('H:i:s', $data->end_time);
                    $timeSlots = removeSlotsBetweenSpecialHours($timeSlots, $startSpecial, $endSpecial, $data->id);
                }
            }

            return $timeSlots;
        }
    }
}

function removeSlotsBetweenSpecialHours($slots, $startSpecial, $endSpecial, $flexible_id)
{
    $newSlots = [];
    $specialSlotAdded = false;

    foreach ($slots as $slot) {
        $start = strtotime($slot['start']);
        $end = strtotime($slot['end']);
        $startSpecialTime = strtotime($startSpecial);
        $endSpecialTime = strtotime($endSpecial);

        if ($end <= $startSpecialTime || $start >= $endSpecialTime) {
            // Slot does not fall between special hours, keep it
            $newSlots[] = $slot;
        } else {
            // Slot falls between special hours, remove it
            if (!$specialSlotAdded) {
                // Add new slot only once
                $newSlots[] = ['start' => $startSpecial->format('H:i'), 'end' => $endSpecial->format('H:i'), 'flexible_id' => $flexible_id];
                $specialSlotAdded = true;
            }
        }
    }

    return $newSlots;
}

if (!function_exists('isSlotBooked')) {
    function isSlotBooked($slot, $bookedAppointments)
    {
        $currentStart = Carbon::createFromFormat('H:i', $slot['start']);
        $currentEnd = Carbon::createFromFormat('H:i', $slot['end']);

        $count = 0;

        foreach ($bookedAppointments as $bookedSlot) {
            // Extract start and end times from the booked slot string
            [$bookedStartTime, $bookedEndTime] = explode('-', $bookedSlot['time']);

            $bookedStart = Carbon::createFromFormat('H:i', $bookedStartTime);
            $bookedEnd = Carbon::createFromFormat('H:i', $bookedEndTime);

            // Check if the current slot overlaps with any booked slot
            if (($currentStart->gte($bookedStart) && $currentStart->lt($bookedEnd)) ||
                ($currentEnd->gt($bookedStart) && $currentEnd->lte($bookedEnd))
            ) {
                $count++;

                // return true; // Slot is booked
            }
        }
        return $count;
        // return false; // Slot is not booked

    }
}

if (!function_exists('validateAppointmentWindow')) {
    /**
     * Server-side re-check that a booking submission (any form layout, any
     * booking channel) still falls inside business hours — and, when the
     * FlexibleDays module is active and configured for the staff member,
     * inside that staff member's own hours too. The slot picker already
     * steers customers away from closed times, but nothing enforced it at
     * save time, so a direct POST could create an out-of-hours appointment.
     *
     * @return string|null An error message, or null when the window is valid.
     */
    function validateAppointmentWindow($businessId, $createdBy, $staffId, $date, $timeRange)
    {
        if (empty($date) || empty($timeRange) || strpos($timeRange, '-') === false) {
            return __('Invalid appointment time.');
        }

        [$startStr, $endStr] = explode('-', $timeRange, 2);

        try {
            $selectedDate = Carbon::createFromFormat('d-m-Y', $date);
            $requestStart = Carbon::createFromFormat('H:i', trim($startStr));
            $requestEnd = Carbon::createFromFormat('H:i', trim($endStr));
        } catch (\Exception $e) {
            return __('Invalid appointment time.');
        }

        $dayName = $selectedDate->format('l');

        $businessDay = BusinessHours::where('created_by', $createdBy)
            ->where('business_id', $businessId)
            ->where('day_name', $dayName)
            ->first();

        if (!empty($businessDay) && $businessDay->day_off == 'on') {
            return __('This business is closed on :day.', ['day' => $dayName]);
        }

        $businessStart = Carbon::createFromFormat('H:i:s', !empty($businessDay->start_time) ? $businessDay->start_time : '09:30:00');
        $businessEnd = Carbon::createFromFormat('H:i:s', !empty($businessDay->end_time) ? $businessDay->end_time : '18:00:00');

        if ($requestStart->lt($businessStart) || $requestEnd->gt($businessEnd)) {
            return __('Selected time is outside business hours.');
        }

        $breaks = !empty($businessDay->break_hours) ? json_decode($businessDay->break_hours, true) : [];
        if (is_array($breaks) && overlapsAnyBreak($requestStart, $requestEnd, $breaks)) {
            return __('Selected time overlaps a business break.');
        }

        if (module_is_active('FlexibleDays', $createdBy) && !empty($staffId) && class_exists(\Workdo\FlexibleDays\Entities\FlexibleStaffHours::class)) {
            $staffDay = \Workdo\FlexibleDays\Entities\FlexibleStaffHours::where('created_by', $createdBy)
                ->where('business_id', $businessId)
                ->where('staff_id', $staffId)
                ->where('day_name', $dayName)
                ->first();

            if (!empty($staffDay)) {
                if ($staffDay->day_off == 'on') {
                    return __('The selected staff member is off on :day.', ['day' => $dayName]);
                }

                if (!empty($staffDay->start_time) && !empty($staffDay->end_time)) {
                    $staffStart = Carbon::createFromFormat('H:i:s', $staffDay->start_time);
                    $staffEnd = Carbon::createFromFormat('H:i:s', $staffDay->end_time);

                    if ($requestStart->lt($staffStart) || $requestEnd->gt($staffEnd)) {
                        return __('Selected time is outside the staff member\'s working hours.');
                    }
                }

                $staffBreaks = !empty($staffDay->break_hours) ? json_decode($staffDay->break_hours, true) : [];
                if (is_array($staffBreaks) && overlapsAnyBreak($requestStart, $requestEnd, $staffBreaks)) {
                    return __('Selected time overlaps a staff break.');
                }
            }
        }

        return null;
    }
}

if (!function_exists('overlapsAnyBreak')) {
    function overlapsAnyBreak($requestStart, $requestEnd, $breaks)
    {
        foreach ($breaks as $break) {
            if (empty($break['start']) || empty($break['end'])) {
                continue;
            }

            $breakStart = Carbon::createFromFormat('H:i', $break['start']);
            $breakEnd = Carbon::createFromFormat('H:i', $break['end']);

            if ($requestStart->lt($breakEnd) && $requestEnd->gt($breakStart)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('resolveDefaultAppointmentStatus')) {
    /**
     * Online and calendar bookings each carry their own default status *and*
     * their own explicitly-chosen notification template — the two settings
     * are a pair, not independently defaulted, so there is deliberately no
     * template fallback here: an unmapped channel sends nothing.
     *
     * @param string $channel 'online' or 'calendar'
     * @return array{status_id: int|null, template_name: string|null}
     */
    function resolveDefaultAppointmentStatus($channel, $businessId, $createdBy)
    {
        $slug = company_setting($channel . '_default_status', $createdBy, $businessId) ?: 'pending';
        $statusId = optional(\App\Models\CustomStatus::findBySlug($slug, $businessId, $createdBy))->id;

        $templateId = company_setting($channel . '_default_template_id', $createdBy, $businessId);
        $templateName = !empty($templateId) ? optional(\App\Models\EmailTemplate::find($templateId))->name : null;

        return ['status_id' => $statusId, 'template_name' => $templateName];
    }
}

if (!function_exists('EmbeddedCode')) {
    function EmbeddedCode($business = null)
    {
        if (empty($business)) {
            $business = Business::find(getActiveBusiness());
        }
        $route = route('appointments.form', $business->slug);

        return '<iframe src="' . $route . '" width="100%" height="700px"></iframe>';
    }
}


if (!function_exists('get_module_card_img')) {
    function get_module_card_img($module)
    {
        $url = url("/packages/workdo/" . $module . '/src/theme/card.png');
        return $url;
    }
}

if (!function_exists('pixelSourceCode')) {
    function pixelSourceCode($platform, $pixelId)
    {
        // Facebook Pixel script
        if ($platform === 'facebook') {
            $script = "
                <script>
                    !function(f,b,e,v,n,t,s)
                    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                    n.queue=[];t=b.createElement(e);t.async=!0;
                    t.src=v;s=b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t,s)}(window, document,'script',
                    'https://connect.facebook.net/en_US/fbevents.js');
                    fbq('init', '%s');
                    fbq('track', 'PageView');
                </script>

                <noscript><img height='1' width='1' style='display:none' src='https://www.facebook.com/tr?id=%d&ev=PageView&noscript=1'/></noscript>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }


        // Twitter Pixel script
        if ($platform === 'twitter') {
            $script = "
            <script>
            !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
            },s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='https://static.ads-twitter.com/uwt.js',
            a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
            twq('config','%s');
            </script>
            ";

            return sprintf($script, $pixelId);
        }


        // Linkedin Pixel script
        if ($platform === 'linkedin') {
            $script = "
                <script type='text/javascript'>
                    _linkedin_data_partner_id = %d;
                </script>
                <script type='text/javascript'>
                    (function () {
                        var s = document.getElementsByTagName('script')[0];
                        var b = document.createElement('script');
                        b.type = 'text/javascript';
                        b.async = true;
                        b.src = 'https://snap.licdn.com/li.lms-analytics/insight.min.js';
                        s.parentNode.insertBefore(b, s);
                    })();
                </script>
                <noscript><img height='1' width='1' style='display:none;' alt='' src='https://dc.ads.linkedin.com/collect/?pid=%d&fmt=gif'/></noscript>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        // Pinterest Pixel script
        if ($platform === 'pinterest') {
            $script = "
            <!-- Pinterest Tag -->
            <script>
            !function(e){if(!window.pintrk){window.pintrk = function () {
            window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
            n=window.pintrk;n.queue=[],n.version='3.0';var
            t=document.createElement('script');t.async=!0,t.src=e;var
            r=document.getElementsByTagName('script')[0];
            r.parentNode.insertBefore(t,r)}}('https://s.pinimg.com/ct/core.js');
            pintrk('load', '%s');
            pintrk('page');
            </script>
            <noscript>
            <img height='1' width='1' style='display:none;' alt=''
            src='https://ct.pinterest.com/v3/?event=init&tid=2613174167631&pd[em]=<hashed_email_address>&noscript=1' />
            </noscript>
            <!-- end Pinterest Tag -->

            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        // Quora Pixel script
        if ($platform === 'quora') {
            $script = "
            <script>
                    !function (q, e, v, n, t, s) {
                        if (q.qp) return;
                        n = q.qp = function () {
                            n.qp ? n.qp.apply(n, arguments) : n.queue.push(arguments);
                        };
                        n.queue = [];
                        t = document.createElement(e);
                        t.async = !0;
                        t.src = v;
                        s = document.getElementsByTagName(e)[0];
                        s.parentNode.insertBefore(t, s);
                    }(window, 'script', 'https://a.quora.com/qevents.js');
                    qp('init', %s);
                    qp('track', 'ViewContent');
                </script>

                <noscript><img height='1' width='1' style='display:none' src='https://q.quora.com/_/ad/%d/pixel?tag=ViewContent&noscript=1'/></noscript>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        // Bing Pixel script
        if ($platform === 'bing') {
            $script = '
                <script>
                (function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[] ,f=function(){var o={ti:"%d"}; o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")} ,n=d.createElement(t),n.src=r,n.async=1,n.onload=n .onreadystatechange=function() {var s=this.readyState;s &&s!=="loaded"&& s!=="complete"||(f(),n.onload=n. onreadystatechange=null)},i= d.getElementsByTagName(t)[0],i. parentNode.insertBefore(n,i)})(window,document,"script"," //bat.bing.com/bat.js","uetq");
                </script>
                <noscript><img src="//bat.bing.com/action/0?ti=%d&Ver=2" height="0" width="0" style="display:none; visibility: hidden;" /></noscript>
            ';

            return sprintf($script, $pixelId, $pixelId);
        }

        // Google adwords Pixel script
        if ($platform === 'google-adwords') {
            $script = "
                <script type='text/javascript'>

                var google_conversion_id = '%s';
                var google_custom_params = window.google_tag_params;
                var google_remarketing_only = true;

                </script>
                <script type='text/javascript' src='//www.googleadservices.com/pagead/conversion.js'>
                </script>
                <noscript>
                <div style='display:inline;'>
                <img height='1' width='1' style='border-style:none;' alt='' src='//googleads.g.doubleclick.net/pagead/viewthroughconversion/%s/?guid=ON&amp;script=0'/>
                </div>
                </noscript>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }


        // Google tag manager Pixel script
        if ($platform === 'google-analytics') {
            $script = "
                <script async src='https://www.googletagmanager.com/gtag/js?id=%s'></script>
                <script>

                window.dataLayer = window.dataLayer || [];

                function gtag(){dataLayer.push(arguments);}

                gtag('js', new Date());

                gtag('config', '%s');

                </script>
            ";

            return sprintf($script, $pixelId, $pixelId);
        }

        //snapchat
        if ($platform === 'snapchat') {
            $script = " <script type='text/javascript'>
            (function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function()
            {a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
            a.queue=[];var s='script';r=t.createElement(s);r.async=!0;
            r.src=n;var u=t.getElementsByTagName(s)[0];
            u.parentNode.insertBefore(r,u);})(window,document,
            'https://sc-static.net/scevent.min.js');

            snaptr('init', '%s', {
            'user_email': '__INSERT_USER_EMAIL__'
            });

            snaptr('track', 'PAGE_VIEW');

            </script>";
            return sprintf($script, $pixelId, $pixelId);
        }

        //tiktok
        if ($platform === 'tiktok') {
            $script = " <script>
            !function (w, d, t) {
            w.TiktokAnalyticsObject=t;
            var ttq=w[t]=w[t]||[];
            ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
            for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;
            n++)ttq.setAndDefer(e,ttq.methods[n]);
            return e},ttq.load=function(e,n){var i='https://analytics.tiktok.com/i18n/pixel/events.js';
            ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};
            var o=document.createElement('script');
            o.type='text/javascript',o.async=!0,o.src=i+'?sdkid='+e+'&lib='+t;
            var a=document.getElementsByTagName('script')[0];
            a.parentNode.insertBefore(o,a)};

            ttq.load('%s');
            ttq.page();
            }(window, document, 'ttq');
            </script>";

            return sprintf($script, $pixelId, $pixelId);
        }
    }
}

if (!function_exists('frontend_bussiness_slug')) {
    function frontend_bussiness_slug()
    {
        $uri = url()->full();
        if ($uri != env('APP_URL')) {
            $segments = explode('/', str_replace('' . url('') . '', '', $uri));
            $segments = $segments[1] ?? null;
            if ($segments == null) {
                $local = parse_url(config('app.url'))['host'];
                // Get the request host
                $remote = request()->getHost();
                // Get the remote domain
            }
            // remove WWW
            $remote = str_replace('www.', '', $remote);
            $domain = Setting::where('key', '=', 'domains')->where('value', '=', $remote)->first();
            if ($domain) {
                $enable_domain = Setting::where('key', '=', 'enable_domain')->where('value', 'on')->where('business', $domain->business)->first();
                if ($enable_domain) {
                    $business = Business::find($enable_domain->business);
                }
            }
            $sub_domain = Setting::where('key', '=', 'subdomain')->where('value', '=', $remote)->first();
            if ($sub_domain) {
                $enable_subdomain = Setting::where('key', '=', 'enable_subdomain')->where('value', 'on')->where('business', $sub_domain->business)->first();
                if ($enable_subdomain) {
                    $business = Business::find($enable_subdomain->business);
                }
            }
            if (isset($business)) {
                $slug = $business->slug;
            }
            return $slug;
        }
    }
}


// Return Currency Symbol , Currency format & Currency Sybmool position ( create for query optimization)
if (!function_exists('get_currency_format_and_symbol')) {
    function get_currency_format_and_symbol($company_id = null, $business = null)
    {
        if (!empty($company_id) && empty($company_id)) {
            $company_settings = getCompanyAllSetting($company_id);
        } else if (!empty($company_id) && !empty($business)) {
            $company_settings = getCompanyAllSetting($company_id, $business);
        } else {
            $company_settings = getCompanyAllSetting();
        }
        $symbol_position = 'pre';
        $currancy_symbol = '$';
        $currancy_format = 1;
        if (isset($company_settings['site_currency_symbol_position'])) {
            $symbol_position = $company_settings['site_currency_symbol_position'];
        }
        if (isset($company_settings['defult_currancy_symbol'])) {
            $currancy_symbol = $company_settings['defult_currancy_symbol'];
        }
        if (isset($company_settings['currency_format'])) {
            $currancy_format = $company_settings['currency_format'];
        }

        $data = [
            'currency_symbol_position' =>  $symbol_position,
            'currancy_symbol' =>  $currancy_symbol,
            'currancy_format' => $currancy_format
        ];
        return $data;
    }
}

if (!function_exists('templateData')) {
    function templateData()
    {
        $arr              = [];
        $arr['colors']    = [
            '003580',
            '666666',
            '6676ef',
            'f50102',
            'f9b034',
            'fbdd03',
            'c1d82f',
            '37a4e4',
            '8a7966',
            '6a737b',
            '050f2c',
            '0e3666',
            '3baeff',
            '3368e6',
            'b84592',
            'f64f81',
            'f66c5f',
            'fac168',
            '46de98',
            '40c7d0',
            'be0028',
            '2f9f45',
            '371676',
            '52325d',
            '511378',
            '0f3866',
            '48c0b6',
            '297cc0',
            'ffffff',
            '000',
        ];
        $arr['templates'] = [
            "template1" => "New York",
            "template2" => "Toronto",
            "template3" => "Rio",
            "template4" => "London",
            "template5" => "Istanbul",
            "template6" => "Mumbai",
            "template7" => "Hong Kong",
            "template8" => "Tokyo",
            "template9" => "Sydney",
            "template10" => "Paris",
        ];
        return $arr;
    }
}
