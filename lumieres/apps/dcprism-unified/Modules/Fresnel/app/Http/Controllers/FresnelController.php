<?php

namespace Modules\Fresnel\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FresnelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('fresnel::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fresnel::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('fresnel::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('fresnel::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
