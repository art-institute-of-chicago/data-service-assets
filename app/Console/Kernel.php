<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * WEB-874: Make commands never overlap.
     */
    private const FOR_ONE_YEAR = 525600;

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('import:assets')
            ->everyFiveMinutes()
            ->withoutOverlapping(self::FOR_ONE_YEAR);

        $schedule->command('images:color')
            ->everyFiveMinutes()
            ->withoutOverlapping(self::FOR_ONE_YEAR);

        $schedule->command('images:lqip')
            ->everyFiveMinutes()
            ->withoutOverlapping(self::FOR_ONE_YEAR);

        $schedule->command('python:export')
            ->everyMinute()
            ->withoutOverlapping(self::FOR_ONE_YEAR);

        $schedule->command('python:import')
            ->everyMinute()
            ->withoutOverlapping(self::FOR_ONE_YEAR);

        $schedule->command('invalidate')
            ->everyMinute();

        // WEB-1835, WEB-1838: Staging content shim checks prod NetX
        if (config('app.env') === 'production') {
            $schedule->command('delete:partial')
                ->everyFiveMinutes()
                ->withoutOverlapping(self::FOR_ONE_YEAR);
        }

        $schedule->command('delete:full')
            ->weekly()
            ->withoutOverlapping(self::FOR_ONE_YEAR);
    }

    /**
     * Register the Closure based commands for the application.
     * By default, it loads all commands in `Commands` non-recursively.
     *
     * @return void
     */
    protected function commands()
    {

        $this->load(__DIR__ . '/Commands');

    }
}
