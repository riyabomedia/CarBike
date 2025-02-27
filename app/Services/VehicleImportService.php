<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Models\VehicleModel;
use App\Models\Brochure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class VehicleImportService
{
    /**
     * Import vehicles from CSV file
     */
    public function importFromCsv(string $filePath, int $userId)
    {
        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);
        $importedCount = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                $data = array_combine($headers, $row);
                $this->createVehicleFromData($data, $userId);
                $importedCount++;
            }
            DB::commit();
            return $importedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV Import Error: ' . $e->getMessage());
            throw $e;
        } finally {
            fclose($file);
        }
    }

    /**
     * Import vehicles from JSON file
     */
    public function importFromJson(string $filePath, int $userId)
    {
        $jsonContent = file_get_contents($filePath);
        $vehicles = json_decode($jsonContent, true);
        $importedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($vehicles as $vehicleData) {
                $this->createVehicleFromData($vehicleData, $userId);
                $importedCount++;
            }
            DB::commit();
            return $importedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('JSON Import Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Import vehicles from XML file
     */
    public function importFromXml(string $filePath, int $userId)
    {
        $xml = simplexml_load_file($filePath);
        $importedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($xml->vehicle as $vehicleXml) {
                $vehicleData = json_decode(json_encode($vehicleXml), true);
                $this->createVehicleFromData($vehicleData, $userId);
                $importedCount++;
            }
            DB::commit();
            return $importedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('XML Import Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a vehicle from imported data
     */
    private function createVehicleFromData(array $data, int $userId)
    {
        // Find or create brand
        $brand = Brand::firstOrCreate(
            ['name' => $data['brand']],
            ['is_active' => true]
        );

        // Find or create model
        $model = VehicleModel::firstOrCreate(
            [
                'brand_id' => $brand->id,
                'name' => $data['model'],
                'type' => $data['type'] ?? 'car'
            ],
            ['is_active' => true]
        );

        // Create vehicle
        $vehicle = Vehicle::create([
            'user_id' => $userId,
            'brand_id' => $brand->id,
            'model_id' => $model->id,
            'type' => $data['type'] ?? 'car',
            'condition' => $data['condition'] ?? 'used',
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'year' => $data['year'],
            'fuel_type' => $data['fuel_type'] ?? null,
            'transmission' => $data['transmission'] ?? null,
            'mileage' => $data['mileage'] ?? null,
            'color' => $data['color'] ?? null,
            'engine' => $data['engine'] ?? null,
            'vin' => $data['vin'] ?? null,
            'location' => $data['location'] ?? 'Unknown',
            'is_featured' => false,
            'is_approved' => false,
            'slug' => Str::slug($data['title']) . '-' . Str::random(5),
        ]);

        // Process images if available
        if (isset($data['images']) && is_array($data['images'])) {
            $this->processImages($vehicle, $data['images']);
        }

        // Process brochure if available
        if (isset($data['brochure'])) {
            $this->processBrochure($vehicle, $data['brochure'], $data['brochure_title'] ?? 'Vehicle Brochure');
        }

        return $vehicle;
    }

    /**
     * Process and store vehicle images
     */
    private function processImages($vehicle, array $imagePaths)
    {
        $isPrimary = true;
        $sortOrder = 0;

        foreach ($imagePaths as $imagePath) {
            // Check if it's a URL or local path
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                $imageContent = file_get_contents($imagePath);
                $filename = basename($imagePath);
            } else {
                $imageContent = file_get_contents($imagePath);
                $filename = basename($imagePath);
            }

            // Generate a unique filename
            $newFilename = Str::random(10) . '_' . $filename;
            $storagePath = 'vehicles/' . $vehicle->id . '/' . $newFilename;
            
            // Store original image
            Storage::disk('public')->put($storagePath, $imageContent);
            
            // Create and store thumbnail
            $image = Image::make($imageContent);
            $image->fit(300, 200);
            $thumbnailPath = 'vehicles/' . $vehicle->id . '/thumbnails/' . $newFilename;
            Storage::disk('public')->put($thumbnailPath, (string) $image->encode());

            // Create image record
            VehicleImage::create([
                'vehicle_id' => $vehicle->id,
                'image_path' => $storagePath,
                'thumbnail_path' => $thumbnailPath,
                'is_primary' => $isPrimary,
                'sort_order' => $sortOrder,
            ]);

            $isPrimary = false;
            $sortOrder++;
        }
    }

    /**
     * Process and store vehicle brochure
     */
    private function processBrochure($vehicle, string $brochurePath, string $title)
    {
        // Check if it's a URL or local path
        if (filter_var($brochurePath, FILTER_VALIDATE_URL)) {
            $brochureContent = file_get_contents($brochurePath);
            $filename = basename($brochurePath);
        } else {
            $brochureContent = file_get_contents($brochurePath);
            $filename = basename($brochurePath);
        }

        // Generate a unique filename
        $newFilename = Str::random(10) . '_' . $filename;
        $storagePath = 'brochures/' . $vehicle->id . '/' . $newFilename;
        
        // Store brochure
        Storage::disk('public')->put($storagePath, $brochureContent);

        // Create brochure record
        Brochure::create([
            'vehicle_id' => $vehicle->id,
            'title' => $title,
            'file_path' => $storagePath,
        ]);
    }
} 