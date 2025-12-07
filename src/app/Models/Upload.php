<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $table = 'uploads';
    protected $fillable = [
        'user_id',
        'file_name',
        'file_hash',
        'size',
        'type',
        'status',
        'verified_by',
        'verified_at',
        'notes'
    ];
}
