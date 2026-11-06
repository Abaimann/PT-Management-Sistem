<?php

namespace App\Models\Approval;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $table = 'approval_requests';

    protected $fillable = [
        'document_type',
        'document_id',
        'requester_id',
        'status',
        'total_steps',
        'current_step',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_steps' => 'integer',
            'current_step' => 'integer',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class, 'approval_request_id')->orderBy('step_order');
    }
}
