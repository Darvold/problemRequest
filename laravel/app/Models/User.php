<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'fio',
        'login',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function assignedRequests()
    {
        return $this->hasMany(Request::class, 'assignedTo');
    }
}
