<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\VehicleLog;
use Carbon\Carbon;

use Illuminate\Contracts\Queue\ShouldQueue;

class WeeklyReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function build()
    {
        return $this->subject('Weekly Traffic Report - EVSU SmartGate')
                    ->view('emails.weekly_report');
    }
}
