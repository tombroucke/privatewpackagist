<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:update-packages')->everySixHours();
Schedule::command('app:send-updates-notification')->dailyAt('08:00');
