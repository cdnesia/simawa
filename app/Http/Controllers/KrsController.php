<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use App\Services\DataService;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class KrsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataService $service)
    {
        $periode = $request->query('periode');
        $npm = auth('web')->user()->npm;
        $dataKrs = $service->krs($npm);

        $semester = collect($dataKrs)->map(function ($item, $key) {
            return [
                'tahun_akademik' => $key,
                'semester' => $item['semester'] ?? null,
            ];
        })->values()->toArray();

        if (!$periode || !preg_match('/^\d{5}$/', $periode)) {
            $periode = array_key_first($dataKrs);
        }

        $d['semester'] = $semester;
        $d['krs'] = $dataKrs[$periode] ?? ['tahun_akademik' => null, 'semester' => null, 'krs' => []];
        $d['metadata'] = $service->saya($npm);
        return view('krs.view', $d);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(DataService $service, PaymentService $payment)
    {
        $npm = auth('web')->user()->npm;
        $prodi = auth('web')->user()->mahasiswa->kode_program_studi;
        $jadwalKontrak = $service->jadwalKontrakKrs($prodi);
        $cekBeasiswa = $service->cekBeasiswa();

        if ($jadwalKontrak) {
            $cekBolehKontrak = collect($payment->cekKontrakMk())->first();
            if ($cekBolehKontrak['boleh_kontrak'] || $cekBeasiswa) {
                $TAAktif = $service->tahunAkademikAktif($prodi);

                $krs = collect($service->krs($npm, $TAAktif))->values();

                $d['existing'] = collect($krs->first()['krs'] ?? [])
                    ->pluck('jadwal_id')
                    ->map(function ($id) {
                        try {
                            return Crypt::decrypt($id);
                        } catch (DecryptException $e) {
                            return null;
                        }
                    })
                    ->filter()
                    ->values()
                    ->toArray();

                $d['jadwal_perkuliahan'] = $service->jadwalKuliah();
                $d['metadata'] = $service->saya($npm);
                return view('krs.jadwal-kuliah', $d);
            } else {
                return view('krs.krsBB');
            }
        } else {
            return view('krs.krsError');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DataService $service)
    {
        if ($request->ajax()) {
            $prodi = auth('web')->user()->mahasiswa->kode_program_studi;
            $jadwalKontrak = $service->jadwalKontrakKrs($prodi);

            if (!$jadwalKontrak) {
                return response()->json(['success' => false, 'message' => 'Jadwal kontrak mata kuliah berakhir.']);
            }

            $TAAktif = $service->tahunAkademikAktif($prodi);
            $insert = [
                'jadwal_id' => Crypt::decrypt($request->jadwal_id),
                'npm' => auth('web')->user()->npm,
                'kode_tahun_akademik' => $TAAktif,
            ];

            Krs::insert($insert);
            return response()->json(['success' => true, 'message' => 'Mata kuliah berhasil di kontrak.']);
        }
    }
    public function destroy(Request $request, $id, DataService $service)
    {
        if ($request->ajax()) {
            $prodi = auth('web')->user()->mahasiswa->kode_program_studi;
            $jadwalKontrak = $service->jadwalKontrakKrs($prodi);
            if (!$jadwalKontrak) {
                return response()->json(['success' => false, 'message' => 'Jadwal kontrak mata kuliah berakhir.']);
            }
            $id = Crypt::decrypt($id);
            $npm = auth('web')->user()->npm;
            $krs = Krs::where('jadwal_id', $id)
                ->where('npm', $npm)
                ->first();

            if (!$krs) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            if ($krs->persetujuan_pa == 'Y') {
                return response()->json([
                    'success' => false,
                    'message' => 'Mata kuliah sudah disetujui PA dan tidak dapat dihapus'
                ], 403);
            }

            Krs::where('id', $krs->id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mata kuliah berhasil dibatalkan'
            ]);
        }
    }
    public function print(Request $request, DataService $service)
    {
        $periode = $request->query('periode');
        $npm = auth('web')->user()->npm;
        $dataKrs = $service->krs($npm);

        if (!$periode || !preg_match('/^\d{5}$/', $periode)) {
            $periode = array_key_first($dataKrs);
        }

        $data = [
            'saya' => $service->saya($npm),
            'npm' => $npm,
            'periode' => $periode,
            'krs' => $dataKrs[$periode] ?? []
        ];

        $pdf = Pdf::loadView('krs.print', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream('KRS-' . $npm . '-' . $periode . '.pdf');
    }
}
