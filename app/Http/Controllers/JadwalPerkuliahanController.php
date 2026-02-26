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
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $TAAktif = $service->tahunAkademikAktif($kodeProdi);

        $d['jadwal_kuliah'] = $service->krs($npm, $TAAktif);
        return view('jadwal-kuliah.view', $d);
    }
}
