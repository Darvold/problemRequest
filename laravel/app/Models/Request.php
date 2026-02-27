<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'clientName',
        'phone',
        'address',
        'problemText',
        'status',
        'assignedTo',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function master()
    {
        return $this->belongsTo(User::class, 'assignedTo');
    }
}
