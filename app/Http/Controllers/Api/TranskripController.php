<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TranskripController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }
    public function store(Request $request)
    {
        return response()->json(['message' => 'Created']);
    }
    public function show($id)
    {
        return response()->json(['data' => ['id' => $id]]);
    }
    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Updated']);
    }
    public function destroy($id)
    {
        return response()->json(['message' => 'Deleted']);
    }
}
