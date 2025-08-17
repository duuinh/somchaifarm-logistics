<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DailyRouteHistoryController extends Controller
{
    public function index()
    {
        return Inertia::render('DailyRouteHistory/Index');
    }
}