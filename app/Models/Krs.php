<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Krs extends Model
{
    protected $connection = 'db_siade';
    protected $table = 'tbl_mahasiswa_krs';

    public function jadwal()
    {
        return $this->belongsTo(JadwalPerkuliahan::class, 'jadwal_id', 'id');
    }

    public function mataKuliah()
    {
        return DB::connection('db_siade')
            ->table('master_kurikulum_matakuliah')
            ->join('tbl_jadwal_perkuliahan', 'tbl_jadwal_perkuliahan.mata_kuliah_id', '=', 'master_kurikulum_matakuliah.id')
            ->where('tbl_jadwal_perkuliahan.id', $this->jadwal_id)
            ->select('master_kurikulum_matakuliah.*')
            ->first();
    }

    public function hari()
    {
        return DB::connection('db_siade')
            ->table('master_hari')
            ->join('tbl_jadwal_perkuliahan', 'tbl_jadwal_perkuliahan.hari_id', '=', 'master_hari.id')
            ->where('tbl_jadwal_perkuliahan.id', $this->jadwal_id)
            ->select('master_hari.*')
            ->first();
    }
}
