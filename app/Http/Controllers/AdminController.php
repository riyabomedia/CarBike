<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }
    
    public function dashboard()
    {
        $stats = [
            'total_vehicles' => Vehicle::count(),
            'pending_approval' => Vehicle::where('is_approved', false)->count(),
            'total_users' => User::count(),
            'total_dealers' => User::where('role', 'dealer')->count(),
            'total_sellers' => User::where('role', 'seller')->count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
        ];
        
        $recentVehicles = Vehicle::with(['brand', 'model', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $popularVehicles = Vehicle::with(['brand', 'model'])
            ->orderBy('views', 'desc')
            ->limit(5)
            ->get();
            
        $vehiclesByType = DB::table('vehicles')
            ->select(DB::raw('type, count(*) as count'))
            ->groupBy('type')
            ->get();
            
        $vehiclesByCondition = DB::table('vehicles')
            ->select(DB::raw('condition, count(*) as count'))
            ->groupBy('condition')
            ->get();
            
        return view('admin.dashboard', compact(
            'stats', 
            'recentVehicles', 
            'popularVehicles', 
            'vehiclesByType', 
            'vehiclesByCondition'
        ));
    }
    
    public function pendingVehicles()
    {
        $vehicles = Vehicle::with(['brand', 'model', 'user'])
            ->where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('admin.vehicles.pending', compact('vehicles'));
    }
    
    public function approveVehicle(Vehicle $vehicle)
    {
        $vehicle->update(['is_approved' => true]);
        
        return redirect()->back()->with('success', 'Vehicle approved successfully');
    }
    
    public function rejectVehicle(Vehicle $vehicle)
    {
        // You might want to send a notification to the user here
        $vehicle->delete();
        
        return redirect()->back()->with('success', 'Vehicle rejected and deleted');
    }
    
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.users.index', compact('users'));
    }
    
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }
    
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,dealer,seller,buyer',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $user->update($validated);
        
        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }
    
    public function brands()
    {
        $brands = Brand::orderBy('name')->paginate(10);
        
        return view('admin.brands.index', compact('brands'));
    }
    
    public function createBrand()
    {
        return view('admin.brands.create');
    }
    
    public function storeBrand(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands',
            'logo' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }
        
        Brand::create($validated);
        
        return redirect()->route('admin.brands')->with('success', 'Brand created successfully');
    }
    
    public function editBrand(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }
    
    public function updateBrand(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'logo' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }
        
        $brand->update($validated);
        
        return redirect()->route('admin.brands')->with('success', 'Brand updated successfully');
    }
    
    public function models()
    {
        $models = VehicleModel::with('brand')->orderBy('name')->paginate(10);
        
        return view('admin.models.index', compact('models'));
    }
    
    public function createModel()
    {
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.models.create', compact('brands'));
    }
    
    public function storeModel(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:car,bike',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        VehicleModel::create($validated);
        
        return redirect()->route('admin.models')->with('success', 'Model created successfully');
    }
    
    public function editModel(VehicleModel $model)
    {
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.models.edit', compact('model', 'brands'));
    }
    
    public function updateModel(Request $request, VehicleModel $model)
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:car,bike',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $model->update($validated);
        
        return redirect()->route('admin.models')->with('success', 'Model updated successfully');
    }
} 