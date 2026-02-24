<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPerkuliahan extends Model
{
    protected $connection = 'db_siade';
    protected $table = 'tbl_jadwal_perkuliahan';
}
