<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Redirect;

class LogoutListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Logout $event)
    {

        Redirect::to('/')->send();
    }
}
