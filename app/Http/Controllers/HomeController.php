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
        if ($cekBeasiswa) {
            $d['tagihan_terhutang'] = [];
            $d['tagihan_sekarang'] = [];
        } else {
            $d['tagihan_terhutang'] = $this->payment->cekTagihanTerhutang();
            $d['tagihan_sekarang'] = $this->payment->cekTagihanSekarang();
        }
        return view('home', $d);
    }
}
