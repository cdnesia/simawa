<?php

namespace App\Services;

use App\Models\KalenderAkademik;
use App\Models\KegiatanMahasiswa;
use App\Models\Tagihan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentService
{
    protected $dataService;
    protected $apiService;

    public function __construct(DataService $dataService, ApiService $apiService)
    {
        $this->dataService = $dataService;
        $this->apiService = $apiService;
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
    private function formatTagihanDariResponse(array $data, $tahun_akademik): array
    {
        $rincian = $data['rincian'] ?? [];
        $totalTagihan = $data['total_tagihan'] ?? collect($rincian)->sum('nominal');

        return [
            'nomor_tagihan' => $data['nomor_tagihan'],
            'tahun_akademik' => $tahun_akademik,
            'detail_tagihan' => json_encode($rincian),
            'total_tagihan' => $totalTagihan,
            'nominal_ditagih' => $data['nominal_ditagih'] ?? 0,
            'nominal_terbayar' => $data['nominal_dterbayar'] ?? 0,
        ];
    }
    public function generateTagihanSekarang(&$generated = false)
    {
        $npm = auth('web')->user()->npm;
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahun_akademik = $this->dataService->tahunAkademikAktif($kodeProdi);

        $body = [
            'npm' => $npm,
            'tahun_akademik' => $tahun_akademik,
        ];

        $response = $this->apiService->post('/api/tagihan-spp/create', $body);

        $generated = (bool) ($response['success'] ?? false);

        $data = $response['data'] ?? [];

        if (empty($data['rincian'] ?? [])) {
            return [];
        }

        return [$this->formatTagihanDariResponse($data, $tahun_akademik)];
    }
    public function ambilTagihanTerhutang($npm = null, $tahun_akademik = [])
    {
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        if (!$npm) {
            $npm = auth('web')->user()->npm;
        }

        $tahunTerhutang = array_filter([$this->dataService->tahunAkademikAktif($kodeProdi)]);

        $tagihan = collect();

        foreach ($tahunTerhutang as $th) {
            $response = $this->apiService->post('/api/tagihan-spp', [
                'npm' => $npm,
                'tahun_akademik' => $th,
            ]);

            $data = $response['data'] ?? [];

            if (empty($data['rincian'] ?? [])) {
                continue;
            }

            $tagihan->push($this->formatTagihanDariResponse($data, $th));
        }

        return $tagihan->sortBy('tahun_akademik')->values();
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
    public function generateTagihanKKN($kegiatan_mahasiswa_id = null)
    {
        $url = config('services.simaku_url');
        $npm = auth('web')->user()->npm;
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahun_akademik = $this->dataService->tahunAkademikAktif($kodeProdi);

        $timestamp = time();
        $nonce = Str::uuid()->toString();
        $path = 'api/generate-tagihan-kkn';

        $body = json_encode([
            'npm' => $npm,
            'tahun_akademik' => $tahun_akademik,
            'kegiatan_mahasiswa_id' => $kegiatan_mahasiswa_id,
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

        return $responseData;
    }
    public function generateTagihanPKL($kegiatan_mahasiswa_id = null)
    {
        $npm = auth('web')->user()->npm;
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahun_akademik = $this->dataService->tahunAkademikAktif($kodeProdi);

        $persyaratan = KegiatanMahasiswa::find($kegiatan_mahasiswa_id);
        $dataSaya = $this->dataService->saya($npm);

        $today = Carbon::today()->toDateString();
        $waktu_berakhir = KalenderAkademik::where('keg_pendaftaran_pkl', 1)
            ->where('status', 'A')
            ->where('kode_tahun_akademik', $tahun_akademik)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->value('tanggal_selesai');

        $nominal = (int) ($persyaratan->biaya_pendaftaran ?? 0);

        $body = [
            'npm' => $npm,
            'tahun_akademik' => $tahun_akademik,
            'jenis_tagihan' => 'PKL',
            'total_tagihan' => $nominal,
            'nominal_ditagih' => $nominal,
            'total_potongan' => 0,
            'id_kelas_perkuliahan' => (string) ($dataSaya['id_kelas'] ?? ''),
            'nama_kelas_perkuliahan' => $dataSaya['nama_kelas'] ?? null,
            'waktu_berakhir' => $waktu_berakhir,
            'status_aktif' => 'Y',
            'detail_tagihan' => [
                [
                    'nama_bipot' => $persyaratan->nama_kegiatan ?? 'Pendaftaran PKL',
                    'nominal' => $nominal,
                    'id_bipot' => $persyaratan->id_bipot ?? null,
                ],
            ],
            'detail_potongan' => [],
        ];

        return $this->apiService->post('/api/tagihan/create', $body);
    }
    public function cekTagihanKKN($kegiatan_mahasiswa_id = null)
    {
        $url = config('services.simaku_url');
        $npm = auth('web')->user()->npm;
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahun_akademik = $this->dataService->tahunAkademikAktif($kodeProdi);

        $timestamp = time();
        $nonce = Str::uuid()->toString();
        $path = 'api/cek-tagihan-kkn';

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

        return $responseData;
    }
}
