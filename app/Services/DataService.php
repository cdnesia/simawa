<?php

namespace App\Services;

use App\Models\JadwalPerkuliahan;
use App\Models\KalenderAkademik;
use App\Models\Krs;
use App\Models\MasterMahasiswa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DataService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function tahunAkademikAktif($kodeProdi = null)
    {
        $today = Carbon::today()->toDateString();
        $query = DB::connection('db_siade')
            ->table('master_tahun_akademik')
            ->where('status', 'A')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today);
        $query->whereJsonContains('kode_program_studi', $kodeProdi);
        return $query->orderByDesc('id')->value('kode_tahun_akademik');
    }
    public function krs($npm = null, $tahunAkademik = null)
    {
        $dosen = collect($this->dataDosen())->keyBy('id');
        $ruang = collect($this->dataRuang())->keyBy('id');
        $krsRaw = DB::connection('db_siade')
            ->table('tbl_mahasiswa_krs as k')
            ->leftJoin('tbl_jadwal_perkuliahan as j', 'k.jadwal_id', '=', 'j.id')
            ->leftJoin('master_kurikulum_matakuliah as mk', 'j.mata_kuliah_id', '=', 'mk.id')
            ->leftJoin('master_hari as h', 'j.hari_id', '=', 'h.id')
            ->where('k.npm', $npm)
            ->orderBy('k.kode_tahun_akademik')
            ->orderBy('h.id')
            ->select(
                'k.*',
                'j.jam_mulai',
                'j.jam_selesai',
                'j.dosen_id',
                'j.ruang_id',
                'j.kelompok',
                'k.jadwal_id',
                'mk.kode_mata_kuliah',
                'mk.nama_mata_kuliah_idn',
                'mk.sks_mata_kuliah',
                'h.nama_hari',
                'h.id',
            )
            ->get();

        $krsRaw = $krsRaw->sortBy([
            fn($a, $b) => $a->kode_tahun_akademik <=> $b->kode_tahun_akademik,
            fn($b, $a) => ($a->nama_hari ?? '') <=> ($b->nama_hari ?? '')
        ]);

        $krs = [];
        $semester = 1;
        $total_sks_kumulatif = 0;
        $total_bobot_kumulatif = 0;

        foreach ($krsRaw as $row) {
            $ta = $row->kode_tahun_akademik;
            if (!isset($krs[$ta])) {
                $krs[$ta] = [
                    'semester' => $semester++,
                    'jumlah_sks' => 0,
                    'total_bobot' => 0,
                    'krs' => [],
                ];
            }

            $sks = $row->sks_mata_kuliah ?? 0;
            $bobot = $row->nilai_bobot ?? 0;

            $krs[$ta]['jumlah_sks'] += $sks;
            $krs[$ta]['total_bobot'] += $bobot * $sks;

            $total_sks_kumulatif += $sks;
            $total_bobot_kumulatif += $bobot * $sks;

            $krs[$ta]['krs'][] = [
                'id_krs' => Crypt::encrypt($row->id),
                'nilai_angka' => $row->nilai_angka ?? '',
                'jadwal_id' => Crypt::encrypt($row->jadwal_id) ?? '',
                'nilai_huruf' => $row->nilai_huruf ?? '',
                'nilai_bobot' => $bobot,
                'persetujuan_pa' => $row->persetujuan_pa ?? '',
                'lulus' => $row->lulus ?? '',
                'edome' => $row->edome ?? '',
                'kode_mata_kuliah' => $row->kode_mata_kuliah ?? '',
                'nama_mata_kuliah' => $row->nama_mata_kuliah_idn ?? '',
                'sks_matakuliah' => $sks,
                'jam_mulai' => $row->jam_mulai ?? '',
                'jam_selesai' => $row->jam_selesai ?? '',
                'dosen_id' => $dosen[$row->dosen_id]['nama_lengkap'] ?? '',
                'ruang_id' => $ruang[$row->ruang_id]['nama'] ?? '',
                'kelompok' => $row->kelompok ?? '',
                'nama_hari' => $row->nama_hari ?? '',
            ];

            $krs[$ta]['metadata'] = [
                'ips' => $krs[$ta]['jumlah_sks']
                    ? round($krs[$ta]['total_bobot'] / $krs[$ta]['jumlah_sks'], 2)
                    : 0,
                'ipk' => $total_sks_kumulatif
                    ? round($total_bobot_kumulatif / $total_sks_kumulatif, 2)
                    : 0,
            ];
        }
        if ($tahunAkademik) {
            return [
                $tahunAkademik => $krs[$tahunAkademik] ?? []
            ];
        }

        return $krs;
    }
    public function jadwalKontrakKrs()
    {
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $today = Carbon::today()->toDateString();
        $query = KalenderAkademik::where('keg_kontrak_krs', 1)
            ->where('status', 'A')
            ->where('kode_tahun_akademik', $this->tahunAkademikAktif($kodeProdi))
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today);
        return $query->exists();
    }
    public function jadwalKKN()
    {
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $today = Carbon::today()->toDateString();
        $query = KalenderAkademik::where('keg_pendaftaran_kkn', 1)
            ->where('status', 'A')
            ->where('kode_tahun_akademik', $this->tahunAkademikAktif($kodeProdi))
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today);
        return $query->exists();
    }
    public function jadwalKuliah()
    {
        $dosen = collect($this->dataDosen())->keyBy('id');
        $ruang = collect($this->dataRuang())->keyBy('id');
        $kelasId = auth('web')->user()->mahasiswa->program_kuliah_id;
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $tahunAkademik = $this->tahunAkademikAktif($kodeProdi);

        $jadwalRaw = DB::connection('db_siade')->table('tbl_jadwal_perkuliahan as j')
            ->join('master_kurikulum_matakuliah as mk', 'j.mata_kuliah_id', '=', 'mk.id')
            ->join('master_hari as h', 'j.hari_id', '=', 'h.id')
            ->join('master_kurikulum_prodi as kp', 'mk.kurikulum_id', '=', 'kp.kurikulum_id')
            ->join('master_kurikulum as k', 'mk.kurikulum_id', '=', 'k.id')
            ->orderBy('mk.semester')
            ->orderBy('h.id')
            ->orderBy('j.ruang_id')
            ->select(
                'j.id',
                'j.jam_mulai',
                'j.jam_selesai',
                'j.dosen_id',
                'j.ruang_id',
                'j.kelompok',
                'j.program_kuliah_id',
                'mk.kode_mata_kuliah',
                'mk.nama_mata_kuliah_idn',
                'mk.sks_mata_kuliah',
                'mk.semester',
                'h.nama_hari',
                'h.id as id_hari',
                'k.nama_kurikulum',
            )
            ->where('kp.kode_program_studi', $kodeProdi)
            ->where('j.kode_program_studi', $kodeProdi)
            ->where('j.program_kuliah_id', $kelasId)
            ->where('j.tahun_akademik', $tahunAkademik)
            ->get();

        $jadwal_kuliah = [];

        foreach ($jadwalRaw as $row) {
            $semester = $row->semester;
            if (!isset($jadwal_kuliah[$semester])) {
                $jadwal_kuliah[$semester] = [
                    'jadwal_kuliah' => [],
                ];
            }

            $jadwal_kuliah[$semester]['jadwal_kuliah'][] = [
                'jadwal_id' => Crypt::encrypt($row->id),
                'nama_hari' => $row->nama_hari ?? '',
                'jam_mulai' => $row->jam_mulai ?? '',
                'jam_selesai' => $row->jam_selesai,
                'semester' => $row->semester ?? '',
                'dosen_id' => $dosen[$row->dosen_id]['nama_lengkap'] ?? '',
                'ruang_id' => $ruang[$row->ruang_id]['nama'] ?? '',
                'kode_mata_kuliah' => $row->kode_mata_kuliah ?? '',
                'nama_mata_kuliah' => $row->nama_mata_kuliah_idn ?? '',
                'sks' => $row->sks_mata_kuliah_idn ?? '',
                'kurikulum' => $row->nama_kurikulum ?? '',
                'kelompok' => $row->kelompok ?? '',
            ];
        }

        return $jadwal_kuliah;
    }
    private function dataDosen()
    {
        $url = "https://api.umjambi.ac.id/";
        $timestamp = time();
        $nonce = Str::uuid()->toString();
        $path = 'api/data-dosen';

        $body = json_encode([]);

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
    private function dataRuang()
    {
        $url = "https://api.umjambi.ac.id/";
        $timestamp = time();
        $nonce = Str::uuid()->toString();
        $path = 'api/data-ruang';

        $body = json_encode([]);

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
    private function dataProdi()
    {
        return DB::connection('db_siade')
            ->table('master_program_studi as ps')
            ->join('master_fakultas as f', 'ps.fakultas_id', '=', 'f.id')
            ->where('ps.status', 'A')
            ->where('f.status', 'A')
            ->select(
                'ps.*',
                'f.nama_fakultas_idn'
            )
            ->get()
            ->keyBy('kode_program_studi');
    }
    private function dataKelas()
    {
        return DB::connection('db_siade')->table('master_kelas_perkuliahan')->get()->keyBy('id');
    }
    public function saya($npm = null)
    {
        if (!$npm) {
            return null;
        }
        $dosen = collect($this->dataDosen())->keyBy('id');
        $prodis = $this->dataProdi();

        $kelas = $this->dataKelas();
        $q = MasterMahasiswa::where('npm', $npm)->first();

        return [
            'nama_mahasiswa' => $q->nama_mahasiswa,
            'npm' => $q->npm,
            'va_code' => $q->va_code,
            'tahun_angkatan' => $q->tahun_angkatan,
            'kode_program_studi' => $q->kode_program_studi,
            'nama_program_studi' => $prodis[$q->kode_program_studi]->nama_program_studi_idn ?? '',
            'nama_fakultas' => $prodis[$q->kode_program_studi]->nama_fakultas_idn ?? '',
            'id_kelas' => $q->program_kuliah_id,
            'nama_kelas' => $kelas[$q->program_kuliah_id]->nama_program_perkuliahan ?? '',
            'id_pa' => $q->pa_id,
            'dosen_pa' => $dosen[$q->pa_id]['nama_lengkap'] ?? '',
            'isi_biodata' => $q->isi_biodata,
        ];
    }
    public function cekBeasiswa()
    {
        $kodeProdi = auth('web')->user()->mahasiswa->kode_program_studi;
        $npm = auth('web')->user()->npm;
        return DB::connection('db_siade')
            ->table('tbl_penerima_beasiswa')
            ->where('npm', $npm)
            ->whereJsonContains('tahun_akademik', $this->tahunAkademikAktif($kodeProdi))
            ->first();
    }
}
