<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $connection = 'timeit';

    public $timestamps = false;

    public $appends = ['name'];

    protected $hidden = ['nombre', 'created_at', 'updated_at'];

    public function getNameAttribute()
    {
        return $this->attributes['nombre'];
    }

    public function setNameAttribute($value)
    {
        $this->attributes['nombre'] = $value;
    }
}
