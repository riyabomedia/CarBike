<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand_id',
        'model_id',
        'type',
        'condition',
        'title',
        'description',
        'price',
        'year',
        'fuel_type',
        'transmission',
        'mileage',
        'color',
        'engine',
        'vin',
        'location',
        'is_featured',
        'is_approved',
        'slug',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'year' => 'integer',
        'mileage' => 'integer',
        'is_featured' => 'boolean',
        'is_approved' => 'boolean',
        'views' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vehicle) {
            $vehicle->slug = Str::slug($vehicle->title) . '-' . Str::random(5);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function model()
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function images()
    {
        return $this->hasMany(VehicleImage::class);
    }

    public function brochures()
    {
        return $this->hasMany(Brochure::class);
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images()->where('is_primary', true)->first() 
            ?? $this->images()->first();
    }

    public function incrementViews()
    {
        $this->increment('views');
    }
} 