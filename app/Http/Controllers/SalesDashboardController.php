<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class SalesDashboardController extends Controller
{
    /**
     * Simple, mobile-friendly console for sales users.
     */
    public function index(): View
    {
        return view('sales.dashboard');
    }
}

