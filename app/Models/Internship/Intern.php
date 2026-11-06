<?php

namespace App\Models\Internship;

use App\Models\Core\Department;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Intern extends Model
{
    use HasFactory;

    protected $table = 'interns';

    protected $fillable = [
        'user_id',
        'mentor_id',
        'department_id',
        'intern_code',
        'full_name',
        'institution',
        'major',
        'phone',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'mentor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(InternshipLogbook::class, 'intern_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(InternshipEvaluation::class, 'intern_id');
    }
}
