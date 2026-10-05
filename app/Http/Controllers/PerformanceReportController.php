<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['user' => ['nullable', 'string', 'max:100'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date']]);
        $filters['from'] = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $filters['to'] = $filters['to'] ?? now()->toDateString();
        $activityHeaders = ['Advertisement & Design', 'Competitor Offer', 'Desk Work', 'Digital', 'Event', 'FB Page', 'Mail', 'Market Analysis', 'Meeting', 'Office Activities', 'Online', 'Outdoor Activities', 'Phone', 'Planning', 'SMS', 'Visit'];
        $leadHeaders = ['Cold', 'Hot', 'Warm'];
        $salesHeaders = ['Sedan', 'SUV', 'Pickup'];
        $rows = [
            ['name' => 'Mohammad Ehsan Ahmed', 'activities' => [2 => 15, 5 => 91, 6 => 2, 8 => 2, 10 => 3, 12 => 4, 13 => 3, 14 => 3], 'leads' => [0 => 90, 2 => 33], 'sales' => [1 => 4]],
            ['name' => 'Al Muzahid Emu', 'activities' => [6 => 1, 12 => 101], 'leads' => [0 => 102], 'sales' => [0 => 2]],
            ['name' => 'Fahim Ahmed', 'activities' => [1 => 4, 2 => 4, 5 => 35, 10 => 1, 12 => 1, 14 => 1], 'leads' => [0 => 45], 'sales' => [1 => 3]],
            ['name' => 'Mr. Imam Hossin', 'activities' => [12 => 32], 'leads' => [0 => 32], 'sales' => [2 => 1]],
            ['name' => 'Hossain Imam', 'activities' => [15 => 2], 'leads' => [2 => 2], 'sales' => []],
            ['name' => 'Mr. Jubayer Hossain Prottoy', 'activities' => [1 => 2, 12 => 1], 'leads' => [0 => 2, 2 => 1], 'sales' => [0 => 1]],
        ];
        if (! empty($filters['user'])) $rows = array_values(array_filter($rows, fn (array $row) => $row['name'] === $filters['user']));
        return view('performance.index', compact('filters', 'activityHeaders', 'leadHeaders', 'salesHeaders', 'rows'));
    }
}
