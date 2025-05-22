<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $campaign_id
 * @property int $user_id
 * @property float $amount
 * @property string|null $notes
 * @property string $status
 * @property string $payment_status
 * @property string|null $transaction_id
 * @property string|null $payment_gateway_response
 * @property string $currency
 *  @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $approved_by
 * @property \Illuminate\Support\Carbon|null $canceled_at
 * @property \Illuminate\Support\Carbon|null $canceled_by
 * @property \Illuminate\Support\Carbon|null $completed_at
 *  @property \Illuminate\Support\Carbon|null $completed_by
 * @property \Illuminate\Support\Carbon|null $refunded_at
 * @property \Illuminate\Support\Carbon|null $refunded_by
 * @property \Illuminate\Support\Carbon|null $failed_at
 * @property \Illuminate\Support\Carbon|null $failed_by
 * @property \Illuminate\Support\Carbon|null $pending_at
 * @property \Illuminate\Support\Carbon|null $pending_by
 * @property \Illuminate\Support\Carbon|null $processing_at
 * @property \Illuminate\Support\Carbon|null $processing_by
 *  @property \Illuminate\Support\Carbon|null $disputed_at
 *  @property \Illuminate\Support\Carbon|null $disputed_by
 * @property \Illuminate\Support\Carbon|null $resolved_at
 */

class Donation extends Model
{
    use HasFactory, SoftDeletes;

    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_COMPLETED = 'successful';
    public const PAYMENT_STATUS_FAILED = 'failed';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'amount',
        'notes',
        'status',
        'payment_status',
        'transaction_id',
        'payment_gateway_response',
        'currency',
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
