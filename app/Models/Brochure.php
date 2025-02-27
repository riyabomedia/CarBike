<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brochure extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'title',
        'file_path',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
} 