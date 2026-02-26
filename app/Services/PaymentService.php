<?php

namespace App\Services;

use App\Models\Tagihan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentService
{
    protected $dataService;

    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }
    private function expandTerms(int $start, int $end): array
    {
        $y  = intdiv($start, 10);
        $s  = $start % 10;
        $ye = intdiv($end,   10);
        $se = $end   % 10;
        $out = [];
        while ($y < $ye || ($y === $ye && $s <= $se)) {
            $out[] = $y * 10 + $s;
            $s++;
            if ($s > 2) {
                $s = 1;
                $y++;
            }
        }
        return $out;
    }
    public function tahunPembayaranAktif($kodeProdi = null)
    {
        $today = Carbon::today()->toDateString();
        $query = DB::connection('db_simkeu')
            ->table('master_jadwal_pembayaran')
            ->select('tahun_akademik')
            ->where('tahun_akademik', '!=', $this->dataService->tahunAkademikAktif())
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today);
        $query->whereJsonContains('kode_program_studi', $kodeProdi);
        return collect($query->pluck('tahun_akademik') ?? [])->toArray();
    }
    public function cekTagihanSekarang()
    {
        $url = config('services.simaku_url');
        $npm = auth('web')->user()->npm;
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahun_akademik = $this->dataService->tahunAkademikAktif($kodeProdi);

        $query = Tagihan::where('npm', $npm)->where('tahun_akademik', $tahun_akademik)->orderBy('tahun_akademik')->get();

        if ($query->isNotEmpty()) {
            return collect($query)->toArray();
        }

        $url = config('services.simaku_url');
        $npm = auth('web')->user()->npm;

        $timestamp = time();
        $nonce = Str::uuid()->toString();
        $path = 'api/cek-tagihan';

        $body = json_encode([
            'npm' => $npm,
            'tahun_akademik' => $tahun_akademik,
        ]);

        $data = $timestamp . $nonce . 'POST' . $path . $body;
        $signature = hash_hmac('sha256', $data, config('services.hmac_secret'));
        $response = Http::withHeaders([
            'X-API-KEY'   => config('services.hmac_api_key'),
            'X-TIMESTAMP' => $timestamp,
            'X-NONCE'     => $nonce,
            'X-SIGNATURE' => $signature,
        ])->withBody($body, 'application/json')
            ->post($url . $path);

        $responseData = $response->json();

        $data = $responseData['data'] ?? [];
        if (empty($data)) {
            return [];
        }
        return [$data];
    }
    public function generateTagihanSekarang()
    {
        $url = config('services.simaku_url');
        $npm = auth('web')->user()->npm;
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahun_akademik = $this->dataService->tahunAkademikAktif($kodeProdi);

        $query = Tagihan::where('npm', $npm)->where('tahun_akademik', $tahun_akademik)->orderBy('tahun_akademik')->get();

        if ($query->isNotEmpty()) {
            return collect($query)->toArray();
        }

        $timestamp = time();
        $nonce = Str::uuid()->toString();
        $path = 'api/generate-tagihan';

        $body = json_encode([
            'npm' => $npm,
        ]);

        $data = $timestamp . $nonce . 'POST' . $path . $body;
        $signature = hash_hmac('sha256', $data, config('services.hmac_secret'));
        $response = Http::withHeaders([
            'X-API-KEY'   => config('services.hmac_api_key'),
            'X-TIMESTAMP' => $timestamp,
            'X-NONCE'     => $nonce,
            'X-SIGNATURE' => $signature,
        ])->withBody($body, 'application/json')
            ->post($url . $path);

        $responseData = $response->json();

        $data = $responseData['data'] ?? [];
        if (empty($data)) {
            return [];
        }
        return $data;
    }
    public function ambilTagihanTerhutang($npm = null, $tahun_akademik = [])
    {
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        if (!$npm) {
            $npm = auth('web')->user()->npm;
        }
        if (empty($tahun_akademik)) {
            $tahun_akademik = $this->tahunPembayaranAktif($kodeProdi);
        }

        $query = Tagihan::where('npm', $npm)->orderBy('tahun_akademik');
        $query->whereIn('tahun_akademik', $tahun_akademik);
        return $query->get();
    }
    public function cekKontrakMk()
    {
        $url = config('services.simaku_url');
        $npm = auth('web')->user()->npm;
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahun_akademik = $this->dataService->tahunAkademikAktif($kodeProdi);

        $timestamp = time();
        $nonce = Str::uuid()->toString();
        $path = 'api/cek-kontrak-matakuliah';

        $body = json_encode([
            'npm' => $npm,
            'tahun_akademik' => $tahun_akademik,
        ]);

        $data = $timestamp . $nonce . 'POST' . $path . $body;
        $signature = hash_hmac('sha256', $data, config('services.hmac_secret'));
        $response = Http::withHeaders([
            'X-API-KEY'   => config('services.hmac_api_key'),
            'X-TIMESTAMP' => $timestamp,
            'X-NONCE'     => $nonce,
            'X-SIGNATURE' => $signature,
        ])->withBody($body, 'application/json')
            ->post($url . $path);

        $responseData = $response->json();

        $data = $responseData['data'] ?? [];

        if (empty($data)) {
            return [];
        }
        return [$data];
    }
}
