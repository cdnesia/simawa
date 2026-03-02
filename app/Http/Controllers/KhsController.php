<?php

namespace App\Http\Controllers;

use App\Models\Khs;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class KhsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DataService $service)
    {
        $npm = auth('web')->user()->npm;

        $krsOld = DB::connection('db_siade_old')
            ->table('krs')
            ->where('nim', $npm)
            ->get()
            ->keyBy('JadwalID');

        $dataKrs = $service->krs($npm);
        $flatKrs = collect($dataKrs)
            ->pluck('krs')
            ->flatten(1)
            ->map(function ($item) use ($krsOld, $npm) {
                $item['jadwal_id'] = Crypt::decrypt($item['jadwal_id']);
                $item['id_krs'] = Crypt::decrypt($item['id_krs']);
                if (isset($krsOld[$item['jadwal_id']])) {
                    $old = $krsOld[$item['jadwal_id']];
                    if ($old->nilai_angka != $item['nilai_angka']) {
                        DB::connection('db_siade')->table('tbl_mahasiswa_krs')
                            ->where('id', $item['id_krs'])
                            ->where('npm', $npm)
                            ->update([
                                'nilai_angka' => $old->nilai_angka,
                                'nilai_huruf' => $old->nilai_huruf,
                                'nilai_bobot' => $old->nilai_bobot,
                            ]);
                    }
                }
                return $item;
            });

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
