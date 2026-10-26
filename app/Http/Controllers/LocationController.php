<?php

namespace App\Http\Controllers;

use App\Events\LocationUpdated;
use Illuminate\Http\Request;

class LocationController extends Controller
{
 public function index(Request $request)
{
    // بنجيب الـ orderId من الـ URL اللي جاي من الـ redirect
    $orderId = $request->query('orderId');

    // بنمرره للـ view عشان الـ Blade يشوفه
    
    return view('web.location-tracker', compact('orderId'));
}
    public function update(Request $request)
    {
      
        $validated = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'room_id' => 'required',
        ]);
        $userId = session()->getId();
        broadcast(new LocationUpdated(
            $userId,
            $validated['latitude'],
            $validated['longitude'],
            $validated['room_id']
            
        ));
        return response()->json(['success' => true, 'message' => 'Location updated successfully']);
    }
}
