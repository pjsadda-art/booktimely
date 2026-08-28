<?php

namespace Workdo\Api\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ApiGeneratorController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('api manage'))
        {
            $path = base_path("packages/workdo/Api/src/documentation/");
            $fileNames = [];
            $files = File::files($path);

            foreach ($files as $file) {
                $fileNames[] = pathinfo($file, PATHINFO_FILENAME);
            }
            $contents = [];
            foreach($fileNames as $filename){
                $contents[$filename] = json_decode(file_get_contents($path.$filename.'.json'));
            }
            return view('api::index',compact('contents','fileNames'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }

}
