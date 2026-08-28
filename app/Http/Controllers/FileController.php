<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        

    }

    public function Filesetting($id , Request $request)
    {
        if(Auth::user()->isAbleTo('business update'))
        {
            $business = Business::find($id);

            if($request->file_enable)
            {
                $validator = \Validator::make(
                    $request->all(), [
                        'file_label' => 'required',
                        ]
                    );
    
                    if($validator->fails())
                    {
                        $messages = $validator->getMessageBag();
    
                        return redirect()->back()->with('error', $messages->first());
                    }

                $post = $request->all();

                File::updateOrCreate(['key' => 'is_enable','business_id' => $business->id ,'created_by'=>$business->created_by],['label'=>$post['file_label'],'value'=>$post['file_enable']]);
                $tab = 10;
                return redirect()->back()->with('success', __('File successfully enable'))->with('tab', $tab);

            }
            else
            {
                File::updateOrCreate(['key' => 'is_enable','business_id' => $business->id ,'created_by'=>$business->created_by],['label' => '','value'=>'off']);
                $tab = 10;
                return redirect()->back()->with('success', __('File successfully disable'))->with('tab', $tab);
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        //
    }
}
