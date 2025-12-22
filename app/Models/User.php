<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, HasPermissions;

    protected $table = "usuarios";
    protected $connection = "timeit";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $appends = ['role', 'status', 'email', 'employee_id'];

    protected $hidden = [
        'clave',
        'correo',
        'rol',
        'estado',
        'Empleados_id'
    ];

    public function adminlte_image()
    {
        return $this->employee->curriculum->photo;
    }

    public function adminlte_desc()
    {
        return $this->employee->job->name;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'Empleados_id');
    }

    public function getAuthPassword()
    {
        return $this->clave;
    }

    public function getEmailAttribute()
    {
        return $this->attributes['correo'];
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['correo'] = $value;
    }

    public function getRoleAttribute()
    {
        return $this->attributes['rol'];
    }

    public function setRoleAttribute($value)
    {
        $this->attributes['rol'] = $value;
    }

    public function getStatusAttribute()
    {
        return $this->attributes['estado'];
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['estado'] = $value;
    }

    public function getEmployeeIdAttribute()
    {
        return $this->attributes['Empleados_id'];
    }

    public function setEmployeeIdAttribute($value)
    {
        $this->attributes['Empleados_id'] = $value;
    }

    public function findForPassport($username)
    {
        return $this->where('correo', $username)
            ->first();
    }

    public function validateForPassportPassword($password)
    {
        return $this->getAuthPassword() === $password;
    }
}
