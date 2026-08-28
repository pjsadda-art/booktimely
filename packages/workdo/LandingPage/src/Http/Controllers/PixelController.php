<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\LandingPage\Entities\LandingPageSetting;
use Workdo\LandingPage\Entities\Pixel;

class PixelController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('landingpage_pixels')) {
            // Display a listing of the resource.
            $pixels = Pixel::all();
            return view('landing-page::landingpage.seo.index', compact('pixels'));
        }
    }


    public function create()
    {
        $pixals_platforms = LandingPageSetting::pixel_plateforms();

        return view('landing-page::landingpage.seo.create', compact('pixals_platforms'));
    }


    public function store(Request $request)
    {
        // Store a newly created resource in storage.
        $request->validate([
            'platform' => 'required',
            'pixel_id' => 'required',
        ]);

        Pixel::create($request->all());

        return redirect()->back()->with('success', 'Pixel created successfully');
    }

    public function show(Pixel $pixel)
    {
        // Display the specified resource.
        return view('landing-page::landingpage.seo.show', compact('pixel'));
    }

    public function edit(Pixel $pixel)
    {
        // Show the form for editing the specified resource.
        $pixals_platforms = []; // Add the logic to get platforms
        return view('landing-page::landingpage.seo.edit', compact('pixel', 'pixals_platforms'));
    }


    public function update(Request $request, Pixel $pixel)
    {
        // Update the specified resource in storage.
        $request->validate([
            'platform' => 'required',
            'pixel_id' => 'required',
        ]);

        $pixel->update($request->all());

        return redirect()->back()->with('success', 'Pixel updated successfully');
    }

    public function destroy($id)
    {
        $pixel = Pixel::find($id);
        $pixel->delete();
        return redirect()->back()->with('success', 'Pixel deleted successfully');
    }

    public function destoryPixel(Request $request,$pixelId){
        $pixel = Pixel::find($pixelId);
        $pixel->delete();
        return redirect()->back()->with('success', 'Pixel deleted successfully');
    }
}
