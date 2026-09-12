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

        $cekTagihanSekarang = $this->payment->cekTagihanSekarang();

        dd($cekTagihanSekarang);

        if (empty($cekTagihanSekarang)) {
            $generateTagihanSekarang = $this->payment->generateTagihanSekarang();

            if (!$generateTagihanSekarang['success']) {
dd($generateTagihanSekarang);
                if (!str_contains($generateTagihanSekarang['message'] ?? '', 'tidak ditemukan')) {
                    return redirect()->back()->with('error', $generateTagihanSekarang['message']);
                }
            }
        }

        $ambilTagihan = $this->payment->ambilTagihan();

        if ($cekBeasiswa) {
        } else {
            $d['tagihan_sekarang'] = $ambilTagihan;
        }

        return view('home', $d);
    }
}
