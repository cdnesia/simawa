<?php

namespace App\Http\Controllers;

use App\Models\Khs;
use App\Services\DataService;
use Illuminate\Http\Request;

class KhsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DataService $service)
    {
        $npm = auth('web')->user()->npm;
        $d['krs'] = $service->krs($npm);
        $d['metadata'] = $service->saya($npm);
        return view('khs.view', $d);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Khs $khs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Khs $khs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Khs $khs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Khs $khs)
    {
        //
    }
}
