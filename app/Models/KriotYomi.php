<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KriotYomi extends Model
{
    use HasFactory;

    protected $table = 'kriot_yomi';
    protected $primaryKey = 'record_number';
}
