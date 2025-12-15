<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = "tickets";
    protected $connection = "mysql";

    protected $fillable = [
        'code',
        'volume',
        'row',
        'locker',
        'query_type',
    ];
}
