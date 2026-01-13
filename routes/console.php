<?php

use App\Jobs\AggregateInstructorStats;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Aggregate instructor stats every hour for fast dashboard loading
Schedule::job(new AggregateInstructorStats)->hourly();
