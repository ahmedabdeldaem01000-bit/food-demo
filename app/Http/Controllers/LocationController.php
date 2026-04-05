<?php

namespace App\Http\Controllers;

use App\Events\LocationUpdated;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        return view('web.location-tracker');
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        $userId = session()->getId();
        broadcast(new LocationUpdated(
            $userId,
            $validated['latitude'],
            $validated['longitude']
        ));
        return response()->json(['success' => true, 'message' => 'Location updated successfully']);
    }
}
