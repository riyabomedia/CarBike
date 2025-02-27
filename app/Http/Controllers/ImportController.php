<?php

namespace App\Http\Controllers;

use App\Services\VehicleImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportController extends Controller
{
    protected $importService;
    
    public function __construct(VehicleImportService $importService)
    {
        $this->importService = $importService;
        $this->middleware(['auth', 'role:admin,dealer']);
    }
    
    public function index()
    {
        return view('admin.import.index');
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'format' => 'required|in:csv,json,xml',
        ]);
        
        $file = $request->file('file');
        $format = $request->format;
        $userId = Auth::id();
        
        try {
            $count = match($format) {
                'csv' => $this->importService->importFromCsv($file->getPathname(), $userId),
                'json' => $this->importService->importFromJson($file->getPathname(), $userId),
                'xml' => $this->importService->importFromXml($file->getPathname(), $userId),
            };
            
            return redirect()->back()->with('success', "Successfully imported {$count} vehicles.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
} 