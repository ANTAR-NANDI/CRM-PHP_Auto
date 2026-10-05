<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate(['date' => ['nullable', 'date', 'before_or_equal:today']]);
        $date = isset($filter['date']) ? Carbon::parse($filter['date'])->toDateString() : today()->toDateString();
        $employees = User::query()->with('roles')->where('is_active', true)->orderBy('name')->get();
        $attendance = Attendance::query()->whereDate('attendance_date', $date)->get()->keyBy('user_id');

        return view('admin.attendance.index', compact('employees', 'attendance', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            'employees' => ['required', 'array'],
            'employees.*.status' => ['required', Rule::in(['present', 'absent', 'leave', 'half_day'])],
            'employees.*.check_in' => ['nullable', 'date_format:H:i'],
            'employees.*.check_out' => ['nullable', 'date_format:H:i'],
            'employees.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $activeIds = User::query()->where('is_active', true)->whereIn('id', array_keys($data['employees']))->pluck('id')->all();

        DB::transaction(function () use ($data, $activeIds, $request) {
            foreach ($activeIds as $employeeId) {
                $row = $data['employees'][$employeeId];
                $hasTimes = in_array($row['status'], ['present', 'half_day'], true);
                Attendance::updateOrCreate(
                    ['user_id' => $employeeId, 'attendance_date' => $data['attendance_date']],
                    [
                        'status' => $row['status'],
                        'check_in' => $hasTimes ? ($row['check_in'] ?? null) : null,
                        'check_out' => $hasTimes ? ($row['check_out'] ?? null) : null,
                        'notes' => $row['notes'] ?? null,
                        'recorded_by' => $request->user()->id,
                    ]
                );
            }
        });

        return redirect()->route('attendance.index', ['date' => $data['attendance_date']])->with('success', 'Attendance saved successfully.');
    }
}
