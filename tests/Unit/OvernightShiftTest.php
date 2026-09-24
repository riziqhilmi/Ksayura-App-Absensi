<?php

namespace Tests\Unit;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ShiftController;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class OvernightShiftTest extends TestCase
{
    public function test_normal_shift_uses_same_day_start_and_end(): void
    {
        $shift = new Shift([
            'name' => 'Shift Pagi',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'grace_period' => 15,
        ]);

        $this->assertShiftRequestIsValid([
            'name' => 'Shift Pagi',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'grace_period' => 15,
        ]);

        $controller = new AttendanceController();
        $assignmentDate = Carbon::parse('2026-09-15');

        $start = $this->invokePrivate($controller, 'getShiftStartDateTimeForDate', [$assignmentDate, $shift]);
        $end = $this->invokePrivate($controller, 'getShiftEndDateTimeForDate', [$assignmentDate, $shift]);
        $lateMinutes = $this->invokePrivate($controller, 'getLateMinutesForDate', [
            Carbon::parse('2026-09-15 08:20:00'),
            $shift,
            $assignmentDate,
        ]);

        $this->assertSame('2026-09-15 08:00:00', $start->toDateTimeString());
        $this->assertSame('2026-09-15 16:00:00', $end->toDateTimeString());
        $this->assertSame(5, $lateMinutes);
    }

    public function test_overnight_shift_uses_assignment_date_as_start_and_next_day_as_end(): void
    {
        $shift = new Shift([
            'name' => 'Shift Malam',
            'start_time' => '23:00',
            'end_time' => '08:00',
            'grace_period' => 15,
        ]);

        $this->assertShiftRequestIsValid([
            'name' => 'Shift Malam',
            'start_time' => '23:00',
            'end_time' => '08:00',
            'grace_period' => 15,
        ]);

        $controller = new AttendanceController();
        $assignmentDate = Carbon::parse('2026-09-15');

        $start = $this->invokePrivate($controller, 'getShiftStartDateTimeForDate', [$assignmentDate, $shift]);
        $end = $this->invokePrivate($controller, 'getShiftEndDateTimeForDate', [$assignmentDate, $shift]);
        $window = $this->invokePrivate($controller, 'getCheckInWindowForDate', [
            Carbon::parse('2026-09-15 22:30:00'),
            $shift,
            $assignmentDate,
        ]);
        $lateMinutes = $this->invokePrivate($controller, 'getLateMinutesForDate', [
            Carbon::parse('2026-09-16 00:30:00'),
            $shift,
            $assignmentDate,
        ]);

        $this->assertSame('2026-09-15 23:00:00', $start->toDateTimeString());
        $this->assertSame('2026-09-16 08:00:00', $end->toDateTimeString());
        $this->assertTrue($window['can_check_in']);
        $this->assertSame('21:00', $window['available_from']);
        $this->assertSame(75, $lateMinutes);
    }

    private function assertShiftRequestIsValid(array $payload): void
    {
        $request = Request::create('/owner/shifts', 'POST', $payload);
        $validator = $this->invokePrivate(new ShiftController(), 'shiftValidator', [$request]);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
    }

    private function invokePrivate(object $object, string $method, array $arguments = [])
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }
}
