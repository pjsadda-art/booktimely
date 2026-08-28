<?php

namespace App\Http\Controllers;

use App\Events\CancelSubscription;
use App\Models\AddOn;
use App\Models\Sidebar;
use App\Models\User;
use App\Models\userActiveModule;
use App\Facades\ModuleFacade as Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Permission;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use ZipArchive;

class ModuleController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAbleTo('module manage')) {
            try {
                $modules = Module::allModules();
                $addons = json_decode(file_get_contents('https://bookinggo-demo.workdo.io/cronjob/booking_addon.json', true));

                $path = base_path('packages/workdo');
                $devPackagePath = \Illuminate\Support\Facades\File::directories($path);

                $devPackageDirectories = array_map(function ($dir) {
                    return basename($dir);
                }, $devPackagePath);
//echo "<pre>";   print_r($modules);
                $moduleNames = array_column($modules, 'name');
                //echo "<pre>";   print_r($moduleNames);

                $devPackages = array_filter($devPackageDirectories, function ($item) use ($moduleNames) {
                    return !in_array($item, $moduleNames);
                });
//echo "<pre>1";   print_r($devPackages);exit;
                $devModules = [];
                $index = 0;

                foreach ($devPackages as $devPackage) {
                    $moduleFilePath = "{$path}/{$devPackage}/module.json";

                    $devPackageFileContent = file_get_contents($moduleFilePath);
                    $devPackageArr = json_decode($devPackageFileContent);

                    $devModules[$index]['name'] = $devPackageArr->name;
                    $devModules[$index]['alias'] = $devPackageArr->alias;
                    $devModules[$index]['monthly_price'] = $devPackageArr->monthly_price ?? 0;
                    $devModules[$index]['yearly_price'] = $devPackageArr->yearly_price ?? 0;
                    $devModules[$index]['image'] = url('/packages/workdo/' . $devPackage . '/favicon.png');
                    $devModules[$index]['description'] = $devPackageArr->description ?? "";
                    $devModules[$index]['priority'] = $devPackageArr->priority ?? 10;
                    $devModules[$index]['child_module'] = $devPackageArr->child_module ?? [];
                    $devModules[$index]['parent_module'] = $devPackageArr->parent_module ?? [];
                    $devModules[$index]['version'] = $devPackageArr->version ?? 1.0;
                    $devModules[$index]['package_name'] = $devPackageArr->package_name ?? null;
                    $devModules[$index]['display'] = $devPackageArr->display ?? true;

                    $index++;
                }
                //echo "<pre>";   print_r($devModules);exit;
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('oops something went wrong!'));
            }



            return view('module.index', compact('modules', 'addons', 'devModules'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function add()
    {
        if (Auth::user()->isAbleTo('module add')) {
            return view('module.add');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function enable(Request $request)
    {
        $module = Module::find($request->name);
        if (!empty($module)) {
            // Sidebar Performance Changes
            sideMenuCacheForget('all');

            if ($module->isEnabled()) {
                $check_child_module = $this->Check_Child_Module($module);
                if ($check_child_module == true) {
                    $module = Module::find($request->name);
                    $module->disable();
                    return redirect()->back()->with('success', __('Module Disable Successfully!'));
                } else {
                    return redirect()->back()->with('error', __($check_child_module['msg']));
                }
            } else {
                $addon = AddOn::where('module', $request->name)->first();
                if (empty($addon)) {
                    Artisan::call('migrate --path=/packages/workdo/' . $request->name . '/src/Database/Migrations');
                    Artisan::call('package:seed ' . $request->name);

                    $filePath = base_path('packages/workdo/' . $request->name . '/module.json');
                    $jsonContent = file_get_contents($filePath);
                    $data = json_decode($jsonContent, true);

                    $addon = new AddOn;
                    $addon->module = $data['name'];
                    $addon->name = $data['alias'];
                    $addon->monthly_price = $data['monthly_price'] ?? 0;
                    $addon->yearly_price = $data['monthly_price'] ?? 0;
                    $addon->package_name = $data['package_name'];
                    $addon->save();
                    Module::moduleCacheForget($request->name);
                }
                $module = Module::find($request->name);
                $check_parent_module = $this->Check_Parent_Module($module);
                if ($check_parent_module['status'] == true) {

                    Artisan::call('migrate --path=/packages/workdo/' . $request->name . '/src/Database/Migrations');
                    Artisan::call('package:seed ' . $request->name);
                    $module = Module::find($request->name);
                    $module->enable();
                    return redirect()->back()->with('success', __('Module Enable Successfully!'));
                } else {
                    return redirect()->back()->with('error', __($check_parent_module['msg']));
                }
            }
        } else {
            return redirect()->back()->with('error', __('oops something wren wrong!'));
        }
    }
    // private function installDependencies($moduleName)
    // {
    //     // Define the dependencies for each module
    //     $dependencies = [
    //         'Paypal' => ['srmklive/paypal' => '^3.0'],
    //         // Add more modules and their dependencies as needed
    //     ];
    //     if (array_key_exists($moduleName, $dependencies)) {
    //         $requiredPackages = $dependencies[$moduleName];
    //         foreach ($requiredPackages as $package => $version) {
    //             // Use Composer to install the required packages
    //             try {
    //                 putenv('COMPOSER_HOME=' . base_path('vendor/bin/composer'));

    //                 $process = new Process(['composer', 'require', $package . ':' . $version]);
    //                 $process->setEnv(['COMPOSER_HOME' => base_path('vendor/bin/composer')]); // Set the environment variable
    //                 $process->run();

    //                 if (!$process->isSuccessful()) {
    //                     throw new ProcessFailedException($process);
    //                 }

    //             } catch (\Throwable $th) {
    //             }
    //         }
    //     }
    // }

    public function install(Request $request)
    {
        $zip = new ZipArchive;
        $fileName = $request->file('file')->getClientOriginalName();
        $fileName = str_replace('.zip', '', $fileName);
        try {
            $res = $zip->open($request->file);
        } catch (\Exception $e) {
            return error_res($e->getMessage());
        }
        if ($res === TRUE) {
            $zip->extractTo('packages/workdo/');
            $zip->close();

            $filePath = base_path('packages/workdo/' . $fileName . '/module.json');
            $jsonContent = file_get_contents($filePath);
            $data = json_decode($jsonContent, true);

            $addon = AddOn::where('module', $fileName)->first();
            if (empty($addon)) {
                $addon = new AddOn;
                $addon->module = $data['name'];
                $addon->name = $data['alias'];
                $addon->monthly_price = 0;
                $addon->yearly_price = 0;
                $addon->is_enable = 0;
                $addon->package_name = $data['package_name'];
                $addon->save();
            }else{
                Artisan::call('db:seed', [
                    '--class' => 'PackagesName',
                ]);
            }
            Module::moduleCacheForget($addon->module);

            return success_res('Install successfully.');
        } else {
            return error_res('oops something went wrong');
        }
        return error_res('oops something went wrong');
    }

    // Other product addon not install in other product for below function
    // public function install(Request $request){
    //     $zip = new ZipArchive;
    //     $uploadedFile = $request->file('file');

    //     $zipBaseName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
    //     $specificFileName = $zipBaseName . '/module.json';

    //     try {
    //             $res = $zip->open($request->file);
    //       } catch (\Exception $e) {
    //             return error_res($e->getMessage());
    //       }
    //     if ($res === TRUE)
    //     {
    //         if ($zip->locateName($specificFileName) !== false)
    //         {
    //             $fileContent = $zip->getFromName($specificFileName);
    //             $jsonData = json_decode($fileContent, true);
    //             if (json_last_error() !== JSON_ERROR_NONE) {
    //                 return error_res('Invalid JSON format in module.json.');
    //             }
    //             $addon_product = isset($jsonData['product']) ? $jsonData['product'] : null;
    //             if ($addon_product == 'BookingGo SaaS') {
    //                 $zip->extractTo('Modules/');
    //                 $zip->close();
    //                 return success_res('Install successfully.');
    //             } else {
    //                 $zip->close();
    //                 return error_res('Addon is not compatible with These site!');
    //             }
    //         }else{
    //             $zip->close();
    //             return error_res('Addon is not compatible with These site!');
    //         }
    //     } else {
    //         return error_res('oops something wren wrong');
    //     }
    //     return error_res('oops something wren wrong');
    // }

    // public function remove($module)
    // {
    //     if(Auth::user()->isAbleTo('module remove'))
    //     {
    //         $module = Module::find($module);
    //         if($module)
    //         {
    //             $module->disable();
    //             $module->delete();
    //             Permission::where('module',$module)->delete();
    //             Artisan::call('module:migrate-refresh '.$module);
    //             AddOn::where('module',$module)->delete();

    //             // Sidebar Performance Changes
    //             sideMenuCacheForget('all');
    //             return redirect()->back()->with('success', __('Module delete successfully!'));
    //         }
    //         else
    //         {
    //             return redirect()->back()->with('error', __('oops something wren wrong!'));
    //         }
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }
    public function Check_Parent_Module($module)
    {
        $path = $module->getPath() . '/module.json';
        $json = json_decode(file_get_contents($path), true);
        $data['status'] = true;
        $data['msg'] = '';

        if (isset($json['parent_module']) && !empty($json['parent_module'])) {
            foreach ($json['parent_module'] as $key => $value) {
                $modules = implode(',', $json['parent_module']);
                $parent_module = module_is_active($value);
                if ($parent_module == true) {
                    $module = Module::find($value);
                    if ($module) {
                        $this->Check_Parent_Module($module);
                    }
                } else {
                    $data['status'] = false;
                    $data['msg'] = 'please activate this module ' . $modules;
                    return $data;
                }
            }
            return $data;
        } else {
            return $data;
        }
    }
    public function Check_Child_Module($module)
    {
        $path = $module->getPath() . '/module.json';
        $json = json_decode(file_get_contents($path), true);
        $status = true;
        if (isset($json['child_module']) && !empty($json['child_module'])) {
            foreach ($json['child_module'] as $key => $value) {
                $child_module = module_is_active($value);
                if ($child_module == true) {
                    $module = Module::find($value);
                    $module->disable();
                    if ($module) {
                        $this->Check_Child_Module($module);
                    }
                }
            }
            return true;
        } else {
            return true;
        }
    }
    public function GuestModuleSelection(Request $request)
    {
        try {
            $post = $request->all();
            unset($post['_token']);
            Session::put('user-module-selection', $post);
            Session::put('Subscription', 'custom_subscription');
        } catch (\Throwable $th) {
        }
        return true;
    }
    public function ModuleReset(Request $request)
    {
        $value = Session::get('user-module-selection');
        if (!empty($value)) {
            Session::forget('user-module-selection');
        }
        return redirect()->route('plans.index');
    }
    public function CancelAddOn($name = null)
    {
        if (!empty($name)) {
            $name         = \Illuminate\Support\Facades\Crypt::decrypt($name);
            $user_module = explode(',', Auth::user()->active_module);
            $user_module = array_values(array_diff($user_module, array($name)));
            $user = User::find(Auth::user()->id);
            $user->active_module = implode(',', $user_module);
            $user->save();

            event(new CancelSubscription(creatorId(), getActiveBusiness(), $name));

            userActiveModule::where('user_id', Auth::user()->id)->where('module', $name)->delete();

            // Settings Cache forget
            comapnySettingCacheForget();
            sideMenuCacheForget();
            return redirect()->back()->with('success', __('Successfully cancel subscription.'));
        } else {
            return redirect()->back()->with('error', __('Something went wrong please try again .'));
        }
    }
}
