<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('package:update --all --confirm')->everySixHours();
Schedule::command('release:notify')->dailyAt('08:00');
