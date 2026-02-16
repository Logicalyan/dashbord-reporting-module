<?php

namespace App\Http\Controllers;


class SettingsController extends Controller
{
    public function integrations()
    {
        return inertia('settings/integrations');
    }
}
