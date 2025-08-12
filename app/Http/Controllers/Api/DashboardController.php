<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Contoh data dummy
        return response()->json([
            'message' => 'Dashboard statistik',
            'data' => []
        ]);
    }
    public function credit()
    {
        return response()->json([
            'message' => 'Credit page',
            'team' => ['Developer 1', 'Developer 2']
        ]);
    }
}
