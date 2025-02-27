<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function models()
    {
        return $this->hasMany(VehicleModel::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
} 