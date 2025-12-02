<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'gateway',
        'gateway_transaction_id',
        'gateway_customer_id',
        'amount',
        'currency',
        'gateway_fee',
        'net_amount',
        'status',
        'payment_method',
        'payment_type',
        'payable_type',
        'payable_id',
        'gateway_response',
        'metadata',
        'description',
        'customer_email',
        'customer_name',
        'customer_phone',
        'billing_address',
        'ip_address',
        'refund_reason',
        'refund_amount',
        'refunded_at',
        'paid_at',
        'failed_at',
        'created_by',
        'modified_by',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_CANCELLED = 'cancelled';

    const GATEWAY_STRIPE = 'stripe';
    const GATEWAY_PAYPAL = 'paypal';
    const GATEWAY_SSLCOMMERZ = 'sslcommerz';
    const GATEWAY_MANUAL = 'manual';

    const TYPE_ONE_TIME = 'one_time';
    const TYPE_SUBSCRIPTION = 'subscription';
    const TYPE_RECURRING = 'recurring';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->transaction_id)) {
                $model->transaction_id = Str::uuid();
            }
            if (auth()->check()) {
                $model->created_by = $model->created_by ?? auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->modified_by = auth()->id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['gateway'] ?? null, fn($q, $gateway) => $q->where('gateway', $gateway))
            ->when($filters['user_id'] ?? null, fn($q, $userId) => $q->where('user_id', $userId))
            ->when($filters['from_date'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['to_date'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('transaction_id', 'like', "%{$search}%")
                        ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%");
                });
            });
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'description' => $reason ?? $this->description,
        ]);
    }

    public function markAsRefunded(float $amount = null, string $reason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REFUNDED,
            'refunded_at' => now(),
            'refund_amount' => $amount ?? $this->amount,
            'refund_reason' => $reason,
        ]);
    }

    public function calculateNetAmount(): float
    {
        return $this->amount - $this->gateway_fee;
    }
}
