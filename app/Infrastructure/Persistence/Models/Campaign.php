<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
    * @property int $id
    * @property int $user_id
    * @property string $title
    * @property string $description
    * @property float $goal_amount
    * @property float $current_amount
    * @property int|null $approved_by
    * @property \Illuminate\Support\Carbon|null $start_date
    * @property \Illuminate\Support\Carbon|null $end_date
    * @property \Illuminate\Support\Carbon|null $approved_at
    * @property string $status
    * @property \Illuminate\Support\Carbon|null $created_at
    * @property \Illuminate\Support\Carbon|null $updated_at
    * @property \Illuminate\Support\Carbon|null $deleted_at
    */

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending_approval';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'goal_amount',
        'current_amount',
        'start_date',
        'approved_by',
        'end_date',
        'approved_at',
        'status',
    ];

    protected $casts = [
        'goal_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'approved_at' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

       /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Database\Factories\CampaignFactory::new();
    }
}
