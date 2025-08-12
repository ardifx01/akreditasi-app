<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }
    public function update(Request $request)
    {
        // Update logic
        return response()->json(['message' => 'Profile updated']);
    }
    public function destroy(Request $request)
    {
        // Delete logic
        return response()->json(['message' => 'Profile deleted']);
    }
}
