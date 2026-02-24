<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $connection = 'db_payment';
    protected $table = 'tagihan';
}
