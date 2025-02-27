<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Models\VehicleModel;
use App\Models\Brochure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with(['brand', 'model', 'images'])
            ->where('is_approved', true);
        
        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('brand')) {
            $query->where('brand_id', $request->brand);
        }
        
        if ($request->has('model')) {
            $query->where('model_id', $request->model);
        }
        
        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }
        
        if ($request->has('min_price') && $request->has('max_price')) {
            $query->whereBetween('price', [$request->min_price, $request->max_price]);
        } else if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        } else if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        if ($request->has('year')) {
            $query->where('year', $request->year);
        }
        
        if ($request->has('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }
        
        if ($request->has('transmission')) {
            $query->where('transmission', $request->transmission);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('brand', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('model', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Apply sorting
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'popular':
                    $query->orderBy('views', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $vehicles = $query->paginate(12);
        
        // Get filter options
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $models = VehicleModel::where('is_active', true)->orderBy('name')->get();
        $years = Vehicle::distinct()->orderBy('year', 'desc')->pluck('year');
        $fuelTypes = Vehicle::distinct()->whereNotNull('fuel_type')->pluck('fuel_type');
        $transmissions = Vehicle::distinct()->whereNotNull('transmission')->pluck('transmission');
        
        return view('vehicles.index', compact(
            'vehicles', 
            'brands', 
            'models', 
            'years', 
            'fuelTypes', 
            'transmissions'
        ));
    }
    
    public function show($slug)
    {
        $vehicle = Vehicle::with(['brand', 'model', 'images', 'brochures', 'user'])
            ->where('slug', $slug)
            ->where('is_approved', true)
            ->firstOrFail();
        
        // Increment view count
        $vehicle->incrementViews();
        
        // Get similar vehicles
        $similarVehicles = Vehicle::with(['brand', 'model', 'images'])
            ->where('id', '!=', $vehicle->id)
            ->where('is_approved', true)
            ->where(function($query) use ($vehicle) {
                $query->where('brand_id', $vehicle->brand_id)
                    ->orWhere('model_id', $vehicle->model_id);
            })
            ->limit(4)
            ->get();
        
        return view('vehicles.show', compact('vehicle', 'similarVehicles'));
    }
    
    public function create()
    {
        $this->authorize('create', Vehicle::class);
        
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $models = VehicleModel::where('is_active', true)->orderBy('name')->get();
        
        return view('vehicles.create', compact('brands', 'models'));
    }
    
    public function store(Request $request)
    {
        $this->authorize('create', Vehicle::class);
        
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'model_id' => 'required|exists:models,id',
            'type' => 'required|in:car,bike',
            'condition' => 'required|in:new,used',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'fuel_type' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:50',
            'mileage' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:50',
            'engine' => 'nullable|string|max:100',
            'vin' => 'nullable|string|max:100',
            'location' => 'required|string|max:255',
            'images.*' => 'required|image|max:5120', // 5MB max
            'brochure' => 'nullable|mimes:pdf|max:10240', // 10MB max
        ]);
        
        $validated['user_id'] = Auth::id();
        $validated['is_approved'] = Auth::user()->role === 'admin';
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        
        $vehicle = Vehicle::create($validated);
        
        // Process images
        if ($request->hasFile('images')) {
            $isPrimary = true;
            $sortOrder = 0;
            
            foreach ($request->file('images') as $image) {
                $filename = Str::random(10) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('vehicles/' . $vehicle->id, $filename, 'public');
                
                // Create thumbnail
                $thumbnail = Image::make($image->getRealPath());
                $thumbnail->fit(300, 200);
                $thumbnailPath = 'vehicles/' . $vehicle->id . '/thumbnails/' . $filename;
                Storage::disk('public')->put($thumbnailPath, (string) $thumbnail->encode());
                
                VehicleImage::create([
                    'vehicle_id' => $vehicle->id,
                    'image_path' => $path,
                    'thumbnail_path' => $thumbnailPath,
                    'is_primary' => $isPrimary,
                    'sort_order' => $sortOrder,
                ]);
                
                $isPrimary = false;
                $sortOrder++;
            }
        }
        
        // Process brochure
        if ($request->hasFile('brochure')) {
            $brochure = $request->file('brochure');
            $filename = Str::random(10) . '.' . $brochure->getClientOriginalExtension();
            $path = $brochure->storeAs('brochures/' . $vehicle->id, $filename, 'public');
            
            $vehicle->brochures()->create([
                'title' => $request->brochure_title ?? 'Vehicle Brochure',
                'file_path' => $path,
            ]);
        }
        
        return redirect()->route('vehicles.show', $vehicle->slug)
            ->with('success', 'Vehicle listing created successfully.');
    }
    
    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);
        
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $models = VehicleModel::where('is_active', true)->orderBy('name')->get();
        
        return view('vehicles.edit', compact('vehicle', 'brands', 'models'));
    }
    
    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);
        
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'model_id' => 'required|exists:models,id',
            'type' => 'required|in:car,bike',
            'condition' => 'required|in:new,used',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'fuel_type' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:50',
            'mileage' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:50',
            'engine' => 'nullable|string|max:100',
            'vin' => 'nullable|string|max:100',
            'location' => 'required|string|max:255',
            'images.*' => 'nullable|image|max:5120', // 5MB max
            'brochure' => 'nullable|mimes:pdf|max:10240', // 10MB max
        ]);
        
        // Update slug if title changed
        if ($vehicle->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        }
        
        $vehicle->update($validated);
        
        // Process new images
        if ($request->hasFile('images')) {
            $sortOrder = $vehicle->images()->max('sort_order') + 1;
            
            foreach ($request->file('images') as $image) {
                $filename = Str::random(10) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('vehicles/' . $vehicle->id, $filename, 'public');
                
                // Create thumbnail
                $thumbnail = Image::make($image->getRealPath());
                $thumbnail->fit(300, 200);
                $thumbnailPath = 'vehicles/' . $vehicle->id . '/thumbnails/' . $filename;
                Storage::disk('public')->put($thumbnailPath, (string) $thumbnail->encode());
                
                VehicleImage::create([
                    'vehicle_id' => $vehicle->id,
                    'image_path' => $path,
                    'thumbnail_path' => $thumbnailPath,
                    'is_primary' => false,
                    'sort_order' => $sortOrder,
                ]);
                
                $sortOrder++;
            }
        }
        
        // Process new brochure
        if ($request->hasFile('brochure')) {
            $brochure = $request->file('brochure');
            $filename = Str::random(10) . '.' . $brochure->getClientOriginalExtension();
            $path = $brochure->storeAs('brochures/' . $vehicle->id, $filename, 'public');
            
            $vehicle->brochures()->create([
                'title' => $request->brochure_title ?? 'Vehicle Brochure',
                'file_path' => $path,
            ]);
        }
        
        return redirect()->route('vehicles.show', $vehicle->slug)
            ->with('success', 'Vehicle listing updated successfully.');
    }
    
    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);
        
        // Delete images from storage
        foreach ($vehicle->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            Storage::disk('public')->delete($image->thumbnail_path);
        }
        
        // Delete brochures from storage
        foreach ($vehicle->brochures as $brochure) {
            Storage::disk('public')->delete($brochure->file_path);
        }
        
        // Delete the vehicle (will cascade delete related records)
        $vehicle->delete();
        
        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle listing deleted successfully.');
    }
    
    public function getModelsByBrand(Request $request)
    {
        $models = VehicleModel::where('brand_id', $request->brand_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return response()->json($models);
    }
} 