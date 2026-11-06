<?php

namespace App\Models\Internship;

use App\Models\HRIS\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipLogbook extends Model
{
    use HasFactory;

    protected $table = 'internship_logbooks';

    protected $fillable = [
        'intern_id',
        'date',
        'activity_description',
        'output_url',
        'status',
        'verified_by',
        'verification_notes',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }
}
