<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ComparisonController extends Controller
{
    public function index()
    {
        $comparisonIds = Session::get('comparison', []);
        $vehicles = Vehicle::with(['brand', 'model', 'images'])
            ->whereIn('id', $comparisonIds)
            ->get();
            
        return view('comparison.index', compact('vehicles'));
    }
    
    public function add(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);
        
        $vehicleId = $request->vehicle_id;
        $comparison = Session::get('comparison', []);
        
        // Limit to 4 vehicles for comparison
        if (count($comparison) >= 4 && !in_array($vehicleId, $comparison)) {
            return response()->json([
                'success' => false, 
                'message' => 'You can compare up to 4 vehicles at a time'
            ]);
        }
        
        if (!in_array($vehicleId, $comparison)) {
            $comparison[] = $vehicleId;
            Session::put('comparison', $comparison);
            
            return response()->json([
                'success' => true, 
                'message' => 'Added to comparison',
                'count' => count($comparison)
            ]);
        }
        
        return response()->json([
            'success' => false, 
            'message' => 'Already in comparison',
            'count' => count($comparison)
        ]);
    }
    
    public function remove(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);
        
        $vehicleId = $request->vehicle_id;
        $comparison = Session::get('comparison', []);
        
        if (in_array($vehicleId, $comparison)) {
            $comparison = array_diff($comparison, [$vehicleId]);
            Session::put('comparison', $comparison);
            
            return response()->json([
                'success' => true, 
                'message' => 'Removed from comparison',
                'count' => count($comparison)
            ]);
        }
        
        return response()->json([
            'success' => false, 
            'message' => 'Not in comparison',
            'count' => count($comparison)
        ]);
    }
    
    public function clear()
    {
        Session::forget('comparison');
        
        return redirect()->back()->with('success', 'Comparison list cleared');
    }
} 