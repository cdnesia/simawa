<?php

namespace App\Services;

use App\Models\Akm;
use App\Models\MasterBipotAngkatan;
use App\Models\Payment;
use App\Models\Potongan;
use App\Models\StatusMahasiswa;
use App\Models\Tagihan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
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
            ->where('tahun_akademik', '!=', $this->tahunAkademikAktif())
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today);
        if ($kodeProdi) {
            $query->whereJsonContains('kode_program_studi', $kodeProdi);
        }
        return collect($query->pluck('tahun_akademik') ?? [])->toArray();
    }
    public function tahunAkademikAktif($kodeProdi = null)
    {
        $today = Carbon::today()->toDateString();
        $query = DB::connection('db_siade')
            ->table('master_tahun_akademik')
            ->where('status', 'A')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today);
        if ($kodeProdi) {
            $query->whereJsonContains('kode_program_studi', $kodeProdi);
        }
        return $query->value('kode_tahun_akademik');
    }
    public function cekTagihanSekarang()
    {
        $url = config('app.simaku_url');
        $npm = auth('web')->user()->npm;
        $query = Tagihan::where('npm', $npm)->where('tahun_akademik', $this->tahunAkademikAktif())->orderBy('tahun_akademik')->get();
        if ($query->isNotEmpty()) {
            return collect($query)->toArray();
        }

        $timestamp = time();
        $body = json_encode([
            'npm' => $npm,
        ]);

        $data = $timestamp . 'POST' . 'api/cek-tagihan' . $body;
        $signature = hash_hmac('sha256', $data, 'supersecret123');
        $response = Http::withHeaders([
            'X-API-KEY' => 'kampus-client-01',
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
        ])->post($url, json_decode($body, true));

        $responseData = $response->json();

        $data = $responseData['data'] ?? [];

        if (empty($data)) {
            return [];
        }
        if (array_is_list($data)) {
            return $data;
        }
        return [$data];
    }
    public function cekTagihanTerhutang($npm = null, $tahun_akademik = [])
    {
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi ?? null;
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
}
