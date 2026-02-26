<?php

namespace App\Http\Controllers;

use App\Models\KegiatanMahasiswa;
use App\Models\PendaftaranKKN;
use App\Services\DataService;
use Illuminate\Http\Request;

class PendaftaranKKNController extends Controller
{
    private $modul = 'pendaftaran-kkn';
    public function __construct()
    {

        view()->share('modul', $this->modul);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(DataService $dataService)
    {
        return view('kkn.view');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(DataService $dataService)
    {
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahunAngkatan = auth('web')->user()->mahasiswa->tahun_angkatan;
        $d['data'] = null;
        $d['persyaratan'] = KegiatanMahasiswa::where('tipe', 'KKN')
            ->whereJsonContains('kode_program_studi', $kodeProdi)
            ->whereJsonContains('tahun_angkatan', $tahunAngkatan)
            ->get()
            ->map(function ($item) {
                $item->encrypted_id = encrypt($item->id);
                unset($item->id);
                return $item;
            })->toArray();
        return view('kkn.form', $d);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $id = decrypt($request->id);

            $kegiatan = KegiatanMahasiswa::findOrFail($id);

            // PendaftaranKkn::create([
            //     'mahasiswa_id' => auth()->user()->mahasiswa->id,
            //     'kegiatan_id'  => $kegiatan->id,
            //     'tanggal_daftar' => now(),
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mendaftar KKN'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftar'
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PendaftaranKKN $pendaftaranKKN)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PendaftaranKKN $pendaftaranKKN)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PendaftaranKKN $pendaftaranKKN)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PendaftaranKKN $pendaftaranKKN)
    {
        //
    }
}
