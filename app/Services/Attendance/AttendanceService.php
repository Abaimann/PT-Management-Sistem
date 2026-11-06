<?php

namespace App\Services\Attendance;

use App\Models\Attendance\AttendanceRecord;
use App\Models\Core\AuditLog;
use App\Models\Core\CompanySetting;
use App\Models\HRIS\Employee;
use App\Models\HRIS\Shift;
use App\Models\HRIS\WorkSchedule;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceService
{
    /**
     * Radius of the Earth in meters for Haversine calculation.
     */
    protected const EARTH_RADIUS_METERS = 6371000;

    /**
     * Timezone for PT Management Sistem.
     */
    protected const TIMEZONE = 'Asia/Jakarta';

    /**
     * Calculate geodesic distance between two GPS coordinates using Haversine Formula.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::EARTH_RADIUS_METERS * $c, 2);
    }

    /**
     * Retrieve active company settings for geofencing.
     */
    public function getCompanySetting(): ?CompanySetting
    {
        return CompanySetting::first();
    }

    /**
     * Resolve shift for employee on specific date.
     */
    public function getEmployeeShift(Employee $employee, Carbon $date): Shift
    {
        $schedule = WorkSchedule::with('shift')
            ->where('employee_id', $employee->id)
            ->whereDate('schedule_date', $date->toDateString())
            ->first();

        if ($schedule && $schedule->shift) {
            return $schedule->shift;
        }

        return Shift::where('code', 'SHIFT-NORMAL')->first()
            ?? Shift::where('is_active', true)->first()
            ?? Shift::first();
    }

    /**
     * Calculate late minutes against shift start time and tolerance.
     */
    public function calculateLateMinutes(Carbon $clockInTime, Shift $shift): int
    {
        // Parse shift start time on the date of clock in
        $shiftStart = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $clockInTime->format('Y-m-d').' '.$shift->start_time,
            self::TIMEZONE
        );

        $toleranceLimit = $shiftStart->copy()->addMinutes($shift->late_tolerance_minutes ?? 15);

        if ($clockInTime->greaterThan($toleranceLimit)) {
            return (int) ceil($shiftStart->diffInMinutes($clockInTime));
        }

        return 0;
    }

    /**
     * Save selfie image to private disk storage.
     * Supports both UploadedFile and Base64 data URI from WebRTC canvas.
     */
    public function storeSelfie(mixed $selfieInput, string $prefix = 'selfie'): string
    {
        $disk = Storage::disk('local');
        $directory = 'attendance/selfies';

        if ($selfieInput instanceof UploadedFile) {
            $extension = $selfieInput->getClientOriginalExtension() ?: 'jpg';
            $filename = sprintf('%s_%s_%s.%s', $prefix, now()->format('YmdHis'), Str::random(8), $extension);
            $path = $selfieInput->storeAs($directory, $filename, 'local');

            return $path;
        }

        if (is_string($selfieInput) && str_starts_with($selfieInput, 'data:image')) {
            // Strip data:image/...;base64, prefix
            $imageParts = explode(';base64,', $selfieInput);
            $imageBase64 = $imageParts[1] ?? '';
            $imageData = base64_decode($imageBase64);

            $filename = sprintf('%s_%s_%s.jpg', $prefix, now()->format('YmdHis'), Str::random(8));
            $path = $directory.'/'.$filename;
            $disk->put($path, $imageData);

            return $path;
        }

        if (is_string($selfieInput) && ! empty($selfieInput)) {
            // Raw binary or path
            $filename = sprintf('%s_%s_%s.jpg', $prefix, now()->format('YmdHis'), Str::random(8));
            $path = $directory.'/'.$filename;
            $disk->put($path, $selfieInput);

            return $path;
        }

        throw new Exception('Format foto selfie tidak valid atau kosong.');
    }

    /**
     * Process employee clock-in with geofence, selfie verification, and shift lateness validation.
     */
    public function checkIn(Employee $employee, array $data): AttendanceRecord
    {
        return DB::transaction(function () use ($employee, $data) {
            $now = Carbon::now(self::TIMEZONE);
            $today = $now->toDateString();

            // 1. Business Rule: Check duplicate check-in today
            $existing = AttendanceRecord::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();

            if ($existing && $existing->clock_in) {
                throw new Exception('Anda sudah melakukan check-in kehadiran hari ini.');
            }

            if ($existing && $existing->is_locked) {
                throw new Exception('Rekam kehadiran hari ini telah dikunci oleh administrator.');
            }

            // 2. Resolve Shift & Schedule
            $shift = $this->getEmployeeShift($employee, $now);

            // 3. Geofence Distance Calculation
            $company = $this->getCompanySetting();
            $lat = (float) ($data['latitude'] ?? 0);
            $lng = (float) ($data['longitude'] ?? 0);
            $distanceMeters = null;
            $isWithinGeofence = true;

            if ($company && $company->latitude && $company->longitude) {
                $distanceMeters = $this->calculateDistance(
                    $lat,
                    $lng,
                    (float) $company->latitude,
                    (float) $company->longitude
                );

                $allowedRadius = $company->geofence_radius_meters ?? 100;
                $isWithinGeofence = ($distanceMeters <= $allowedRadius);
            }

            // 4. Calculate Lateness
            $lateMinutes = $this->calculateLateMinutes($now, $shift);

            // 5. Determine Status
            if (! $isWithinGeofence) {
                $status = 'OUT_OF_RADIUS';
            } elseif ($lateMinutes > 0) {
                $status = 'LATE';
            } else {
                $status = 'ON_TIME';
            }

            // 6. Save Private Selfie
            $selfiePath = null;
            if (! empty($data['selfie'])) {
                $selfiePath = $this->storeSelfie($data['selfie'], 'checkin_'.$employee->nik);
            }

            // 7. Persist Record
            $record = $existing ?? new AttendanceRecord;
            $record->employee_id = $employee->id;
            $record->shift_id = $shift->id;
            $record->date = $today;
            $record->clock_in = $now->format('H:i:s');
            $record->clock_in_selfie = $selfiePath;
            $record->clock_in_latitude = $lat;
            $record->clock_in_longitude = $lng;
            $record->clock_in_distance_meters = $distanceMeters;
            $record->late_minutes = $lateMinutes;
            $record->status = $status;
            $record->notes = $data['notes'] ?? null;
            $record->save();

            // 8. Audit Log
            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'attendance_checkin',
                'auditable_type' => AttendanceRecord::class,
                'auditable_id' => $record->id,
                'old_values' => null,
                'new_values' => [
                    'employee_id' => $employee->id,
                    'clock_in' => $record->clock_in,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'distance_meters' => $distanceMeters,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $record;
        });
    }

    /**
     * Process employee clock-out with validations.
     */
    public function checkOut(Employee $employee, array $data): AttendanceRecord
    {
        return DB::transaction(function () use ($employee, $data) {
            $now = Carbon::now(self::TIMEZONE);
            $today = $now->toDateString();

            // 1. Business Rule: Must check-in before check-out
            $record = AttendanceRecord::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();

            if (! $record || ! $record->clock_in) {
                throw new Exception('Anda belum melakukan check-in hari ini, tidak dapat melakukan check-out.');
            }

            if ($record->is_locked) {
                throw new Exception('Rekam kehadiran hari ini telah dikunci oleh administrator.');
            }

            if ($record->clock_out) {
                throw new Exception('Anda sudah melakukan check-out kehadiran hari ini.');
            }

            // 2. Geofence Distance Calculation
            $company = $this->getCompanySetting();
            $lat = (float) ($data['latitude'] ?? 0);
            $lng = (float) ($data['longitude'] ?? 0);
            $distanceMeters = null;

            if ($company && $company->latitude && $company->longitude) {
                $distanceMeters = $this->calculateDistance(
                    $lat,
                    $lng,
                    (float) $company->latitude,
                    (float) $company->longitude
                );
            }

            // 3. Save Private Selfie (if provided)
            $selfiePath = null;
            if (! empty($data['selfie'])) {
                $selfiePath = $this->storeSelfie($data['selfie'], 'checkout_'.$employee->nik);
            }

            $oldValues = $record->toArray();

            // 4. Update Clock Out
            $record->clock_out = $now->format('H:i:s');
            if ($selfiePath) {
                $record->clock_out_selfie = $selfiePath;
            }
            $record->clock_out_latitude = $lat;
            $record->clock_out_longitude = $lng;
            $record->clock_out_distance_meters = $distanceMeters;
            if (! empty($data['notes'])) {
                $record->notes = ($record->notes ? $record->notes.' | ' : '').$data['notes'];
            }
            $record->save();

            // 5. Audit Log
            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'attendance_checkout',
                'auditable_type' => AttendanceRecord::class,
                'auditable_id' => $record->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'clock_out' => $record->clock_out,
                    'distance_meters' => $distanceMeters,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $record;
        });
    }
}
