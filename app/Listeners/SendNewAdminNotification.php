<?php

namespace App\Listeners;

use App\Events\NewAdminNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewAdminNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewAdminNotification $event): void
    {
        //
    }
}
