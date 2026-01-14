<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Order;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminExportController extends Controller
{
    /**
     * Export analytics data as CSV
     */
    public function exportAnalytics(Request $request): StreamedResponse
    {
        $startDate = $request->get('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $type = $request->get('type', 'users'); // users, revenue, enrollments

        $filename = "analytics_{$type}_{$startDate}_to_{$endDate}.csv";

        return response()->streamDownload(function () use ($type, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            switch ($type) {
                case 'users':
                    $this->exportNewUsers($handle, $startDate, $endDate);
                    break;
                case 'revenue':
                    $this->exportRevenue($handle, $startDate, $endDate);
                    break;
                case 'enrollments':
                    $this->exportEnrollments($handle, $startDate, $endDate);
                    break;
                case 'courses':
                    $this->exportTopCourses($handle);
                    break;
                default:
                    $this->exportSummary($handle, $startDate, $endDate);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function exportNewUsers($handle, $startDate, $endDate): void
    {
        fputcsv($handle, ['Date', 'New Users']);

        $data = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($data as $row) {
            fputcsv($handle, [$row->date, $row->count]);
        }
    }

    private function exportRevenue($handle, $startDate, $endDate): void
    {
        fputcsv($handle, ['Date', 'Revenue ($)']);

        $data = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($data as $row) {
            fputcsv($handle, [$row->date, number_format($row->total, 2)]);
        }
    }

    private function exportEnrollments($handle, $startDate, $endDate): void
    {
        fputcsv($handle, ['Date', 'New Enrollments']);

        $data = Enrollment::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($data as $row) {
            fputcsv($handle, [$row->date, $row->count]);
        }
    }

    private function exportTopCourses($handle): void
    {
        fputcsv($handle, ['Course Title', 'Price', 'Enrollments', 'Instructor ID']);

        $data = Course::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->take(50)
            ->get(['id', 'title', 'price', 'instructor_id', 'enrollments_count']);

        foreach ($data as $course) {
            fputcsv($handle, [$course->title, $course->price, $course->enrollments_count, $course->instructor_id]);
        }
    }

    private function exportSummary($handle, $startDate, $endDate): void
    {
        fputcsv($handle, ['Metric', 'Value']);

        $newUsers = User::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->count();
        $newEnrollments = Enrollment::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->count();
        $revenue = Order::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->sum('total');
        $totalUsers = User::count();
        $totalCourses = Course::count();

        fputcsv($handle, ['Period Start', $startDate]);
        fputcsv($handle, ['Period End', $endDate]);
        fputcsv($handle, ['New Users', $newUsers]);
        fputcsv($handle, ['New Enrollments', $newEnrollments]);
        fputcsv($handle, ['Revenue', '$' . number_format($revenue, 2)]);
        fputcsv($handle, ['Total Users (All Time)', $totalUsers]);
        fputcsv($handle, ['Total Courses (All Time)', $totalCourses]);
    }
}
