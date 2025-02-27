<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $wishlist = Auth::user()->wishlist()->with(['brand', 'model', 'images'])->get();
        
        return view('wishlist.index', compact('wishlist'));
    }
    
    public function add(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);
        
        $userId = Auth::id();
        $vehicleId = $request->vehicle_id;
        
        // Check if already in wishlist
        $exists = Wishlist::where('user_id', $userId)
            ->where('vehicle_id', $vehicleId)
            ->exists();
            
        if (!$exists) {
            Wishlist::create([
                'user_id' => $userId,
                'vehicle_id' => $vehicleId,
            ]);
            
            return response()->json(['success' => true, 'message' => 'Added to wishlist']);
        }
        
        return response()->json(['success' => false, 'message' => 'Already in wishlist']);
    }
    
    public function remove(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);
        
        $userId = Auth::id();
        $vehicleId = $request->vehicle_id;
        
        Wishlist::where('user_id', $userId)
            ->where('vehicle_id', $vehicleId)
            ->delete();
            
        return response()->json(['success' => true, 'message' => 'Removed from wishlist']);
    }
} 