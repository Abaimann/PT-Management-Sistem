<?php

namespace Database\Seeders;

use App\Models\HRIS\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shiftsData = [
            [
                'code' => 'SHIFT-NORMAL',
                'name' => 'Shift Normal (Office Hours)',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'late_tolerance_minutes' => 15,
                'is_overnight' => false,
                'is_active' => true,
            ],
            [
                'code' => 'SHIFT-PAGI',
                'name' => 'Shift Pagi (Early Operational)',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'late_tolerance_minutes' => 15,
                'is_overnight' => false,
                'is_active' => true,
            ],
            [
                'code' => 'SHIFT-SIANG',
                'name' => 'Shift Siang (Middle Operational)',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'late_tolerance_minutes' => 15,
                'is_overnight' => false,
                'is_active' => true,
            ],
            [
                'code' => 'SHIFT-MALAM',
                'name' => 'Shift Malam (Overnight Shift)',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'late_tolerance_minutes' => 15,
                'is_overnight' => true,
                'is_active' => true,
            ],
        ];

        foreach ($shiftsData as $shift) {
            Shift::updateOrCreate(['code' => $shift['code']], $shift);
        }
    }
}
