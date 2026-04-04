<?php

namespace App\Http\Controllers;

use App\Models\Khs;
use App\Services\DataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class KhsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DataService $service)
    {
        $npm = auth('web')->user()->npm;
        $periode = $request->query('periode');
        $krsOld = DB::connection('db_siade_old')
            ->table('krs')
            ->where('nim', $npm)
            ->get()
            ->keyBy('JadwalID');

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
        return view('khs.view', $d);
    }

    /**
     * Show the form for creating a new resource.
     */
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

        $pdf = Pdf::loadView('khs.print', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream('KRS-' . $npm . '-' . $periode . '.pdf');
    }
}
