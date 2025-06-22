<?php

namespace App\Http\Controllers;

use abenevaut\Infrastructure\Http\Controllers\ControllerAbstract;
use Inertia\Inertia;

class DashboardController extends ControllerAbstract
{
    public function index()
    {
        return Inertia::render('Dashboard');
    }
}
