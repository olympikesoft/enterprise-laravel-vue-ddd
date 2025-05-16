<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donation extends Model
{
    use HasFactory, SoftDeletes;

    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_COMPLETED = 'completed';
    public const PAYMENT_STATUS_FAILED = 'failed';

    protected $fillable = [
        'campaign_id',
        'user_id', // Donator (can be null if anonymous)
        'donor_name', // For anonymous donations or if user not registered
        'amount',
        'notes',
        'status',
        'payment_status',
        'transaction_id', // From payment gateway
        'payment_gateway_response', // Store raw response for debugging
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}