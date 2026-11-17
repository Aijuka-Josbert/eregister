<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily scheduled encrypted backup at 23:30 East Africa Time
Schedule::command('drms:backup --type=full --verify')->dailyAt('23:30');
