<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class ScheduleRoiController extends Controller
{
    /**
     * Manually trigger the daily ROI run.
     *
     * This route delegates entirely to the canonical `roi:generate-weekly` artisan
     * command so there is ONE code path for ROI processing and commission generation.
     * The old inline logic in this controller used wrong commission rates (3.5 %/3 %/…
     * instead of the plan-based settings rates) and wrong eligibility criteria (direct
     * children count instead of referral-tree level count), and it crashed with a
     * BadMethodCallException on Controller::info() for every user after the first ROI
     * payment date was set.  All of that is replaced by the single command below.
     */
    public function schedule()
    {
        $exitCode = Artisan::call('roi:generate-weekly');
        $output   = Artisan::output();

        if ($exitCode === 0) {
            return redirect()->back()->with('success', 'ROI generation completed successfully.' . PHP_EOL . $output);
        }

        return redirect()->back()->with('error', 'ROI generation finished with errors.' . PHP_EOL . $output);
    }
}
