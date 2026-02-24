<?php

namespace App\Http\Controllers;

use App\Models\JadwalPerkuliahan;
use App\Services\DataService;
use Illuminate\Http\Request;

class JadwalPerkuliahanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DataService $service)
    {
        $npm = auth('web')->user()->npm;
        $TAAktif = $service->tahunAkademikAktif();
        $d['jadwal_kuliah'] = $service->krs($npm, $TAAktif);
        return view('jadwal-kuliah.view', $d);
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
    public function show(JadwalPerkuliahan $jadwalPerkuliahan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JadwalPerkuliahan $jadwalPerkuliahan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JadwalPerkuliahan $jadwalPerkuliahan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JadwalPerkuliahan $jadwalPerkuliahan)
    {
        //
    }
}
