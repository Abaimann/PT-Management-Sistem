<?php

namespace App\Models\Internship;

use App\Models\HRIS\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipEvaluation extends Model
{
    use HasFactory;

    protected $table = 'internship_evaluations';

    protected $fillable = [
        'intern_id',
        'evaluator_id',
        'discipline_score',
        'technical_score',
        'teamwork_score',
        'communication_score',
        'final_score',
        'recommendation',
        'certificate_number',
        'evaluated_at',
    ];

    protected function casts(): array
    {
        return [
            'evaluated_at' => 'date',
            'discipline_score' => 'decimal:2',
            'technical_score' => 'decimal:2',
            'teamwork_score' => 'decimal:2',
            'communication_score' => 'decimal:2',
            'final_score' => 'decimal:2',
        ];
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evaluator_id');
    }
}
