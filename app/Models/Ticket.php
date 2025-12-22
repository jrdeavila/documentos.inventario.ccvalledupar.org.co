<?php

namespace App\Models;

use App\Observers\TicketObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(TicketObserver::class)]
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
        'user_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
