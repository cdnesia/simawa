<?php

namespace App\Http\Controllers;

use App\Models\Akm;
use App\Services\DataService;
use App\Services\KrsService;
use App\Services\PaymentService;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    protected $payment;
    public function __construct(PaymentService $paymentService)
    {
        $this->payment = $paymentService;
    }
    public function index(DataService $service)
    {
        $npm = auth('web')->user()->npm;
        $krs = $service->krs($npm);

        $labels = [];
        $ips = [];
        $ipk = [];

        foreach ($krs as $tahun => $item) {
            $labels[] = 'Semester ' . $item['semester'];
            $ips[] = $item['metadata']['ips'];
            $ipk[] = $item['metadata']['ipk'];
        }

        $d['labels'] = $labels;
        $d['ips'] = $ips;

        $cekBeasiswa = $service->cekBeasiswa();
        $cekTagihanSekarang = $this->payment->generateTagihanSekarang();
        $ambilTagihanTerhutang = $this->payment->ambilTagihanTerhutang();
        if ($cekBeasiswa) {
            $ambilTagihanTerhutangSelainSPP = collect($ambilTagihanTerhutang)
                ->filter(function ($item) {
                    return in_array($item['jenis_tagihan'], ['KKN']);
                });
            $d['tagihan_terhutang'] = $ambilTagihanTerhutangSelainSPP;

            $cekTagihanSelainSPP = collect($cekTagihanSekarang)
                ->filter(function ($item) {
                    return in_array($item['jenis_tagihan'], ['KKN']);
                });

            $d['tagihan_sekarang'] = $cekTagihanSelainSPP;
        } else {
            $d['tagihan_terhutang'] = $ambilTagihanTerhutang;
            $d['tagihan_sekarang'] = $cekTagihanSekarang;
        }
        return view('home', $d);
    }
}
