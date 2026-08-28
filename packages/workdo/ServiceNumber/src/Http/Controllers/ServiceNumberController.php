<?php

namespace Workdo\ServiceNumber\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ServiceNumberController extends Controller
{

    public function index()
    {
        return view('service-number::index');
    }


    public function create()
    {
        return view('service-number::create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        return view('service-number::show');
    }

    public function edit($id)
    {
        return view('service-number::edit');
    }


    public function update(Request $request, $id)
    {
        //
    }

 
    public function destroy($id)
    {
        //
    }
}
