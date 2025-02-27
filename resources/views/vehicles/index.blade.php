@extends('layouts.app')

@section('title', 'Browse Vehicles')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vehicles.index') }}" method="GET">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        
                        <!-- Vehicle Type -->
                        <div class="mb-3">
                            <label class="form-label">Vehicle Type</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="typeCar" value="car" {{ request('type') == 'car' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typeCar">Car</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="typeBike" value="bike" {{ request('type') == 'bike' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typeBike">Bike</label>
                            </div>
                        </div>
                        
                        <!-- Brand -->
                        <div class="mb-3">
                            <label for="brand" class="form-label">Brand</label>
                            <select class="form-select" id="brand" name="brand">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Model (will be populated via JS based on brand) -->
                        <div class="mb-3">
                            <label for="model" class="form-label">Model</label>
                            <select class="form-select" id="model" name="model">
                                <option value="">All Models</option>
                                @foreach($models as $model)
                                    <option value="{{ $model->id }}" {{ request('model') == $model->id ? 'selected' : '' }}>
                                        {{ $model->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Condition -->
                        <div class="mb-3">
                            <label class="form-label">Condition</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="condition" id="conditionNew" value="new" {{ request('condition') == 'new' ? 'checked' : '' }}>
                                <label class="form-check-label" for="conditionNew">New</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="condition" id="conditionUsed" value="used" {{ request('condition') == 'used' ? 'checked' : '' }}>
                                <label class="form-check-label" for="conditionUsed">Used</label>
                            </div>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Price Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="min_price" placeholder="Min" value="{{ request('min_price') }}">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_price" placeholder="Max" value="{{ request('max_price') }}">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Year -->
                        <div class="mb-3">
                            <label for="year" class="form-label">Year</label>
                            <select class="form-select" id="year" name="year">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Fuel Type -->
                        <div class="mb-3">
                            <label for="fuel_type" class="form-label">Fuel Type</label>
                            <select class="form-select" id="fuel_type" name="fuel_type">
                                <option value="">All Fuel Types</option>
                                @foreach($fuelTypes as $fuelType)
                                    <option value="{{ $fuelType }}" {{ request('fuel_type') == $fuelType ? 'selected' : '' }}>
                                        {{ $fuelType }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Transmission -->
                        <div class="mb-3">
                            <label for="transmission" class="form-label">Transmission</label>
                            <select class="form-select" id="transmission" name="transmission">
                                <option value="">All Transmissions</option>
                                @foreach($transmissions as $transmission)
                                    <option value="{{ $transmission }}" {{ request('transmission') == $transmission ? 'selected' : '' }}>
                                        {{ $transmission }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Sort By -->
                        <div class="mb-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" {{ request('sort') == 'newest' || !request('sort') ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Vehicle Listings -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    @if(request('search'))
                        Search Results for "{{ request('search') }}"
                    @else
                        Browse Vehicles
                    @endif
                </h2>
                <span>{{ $vehicles->total() }} vehicles found</span>
            </div>
            
            @if($vehicles->isEmpty())
                <div class="alert alert-info">
                    No vehicles found matching your criteria. Try adjusting your filters.
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    @foreach($vehicles as $vehicle)
                        <div class="col">
                            <div class="card h-100">
                                <div class="position-relative">
                                    @if($vehicle->primaryImage)
                                        <img src="{{ asset('storage/' . $vehicle->primaryImage->thumbnail_path) }}" class="card-img-top" alt="{{ $vehicle->title }}">
                                    @else
                                        <img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top" alt="No Image">
                                    @endif
                                    
                                    @if($vehicle->is_featured)
                                        <span class="position-absolute top-0 start-0 badge bg-warning m-2">Featured</span>
                                    @endif
                                    
                                    <span class="position-absolute top-0 end-0 badge bg-{{ $vehicle->condition === 'new' ? 'success' : 'secondary' }} m-2">
                                        {{ ucfirst($vehicle->condition) }}
                                    </span>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title">{{ $vehicle->title }}</h5>
                                    <p class="card-text text-muted">
                                        {{ $vehicle->brand->name }} {{ $vehicle->model->name }} | {{ $vehicle->year }}
                                    </p>
                                    <p class="card-text">
                                        <strong class="text-primary">${{ number_format($vehicle->price, 2) }}</strong>
                                    </p>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> {{ $vehicle->location }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-eye"></i> {{ $vehicle->views }} views
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-white border-top-0">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('vehicles.show', $vehicle->slug) }}" class="btn btn-primary">View Details</a>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        @auth
                                            <button class="btn btn-sm btn-outline-danger wishlist-btn" data-vehicle-id="{{ $vehicle->id }}">
                                                <i class="bi bi-heart{{ Auth::user()->wishlist->contains($vehicle->id) ? '-fill' : '' }}"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary compare-btn" data-vehicle-id="{{ $vehicle->id }}">
                                                <i class="bi bi-bar-chart"></i> Compare
                                            </button>
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-heart"></i>
                                            </a>
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-bar-chart"></i> Compare
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-4">
                    {{ $vehicles->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Brand-Model dependency
        $('#brand').change(function() {
            const brandId = $(this).val();
            if (brandId) {
                $.ajax({
                    url: "{{ route('vehicles.models-by-brand') }}",
                    type: "GET",
                    data: { brand_id: brandId },
                    success: function(data) {
                        $('#model').empty();
                        $('#model').append('<option value="">All Models</option>');
                        $.each(data, function(key, value) {
                            $('#model').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                    }
                });
            } else {
                $('#model').empty();
                $('#model').append('<option value="">All Models</option>');
            }
        });
        
        // Wishlist functionality
        $('.wishlist-btn').click(function() {
            const vehicleId = $(this).data('vehicle-id');
            const button = $(this);
            const icon = button.find('i');
            
            if (icon.hasClass('bi-heart')) {
                // Add to wishlist
                $.ajax({
                    url: "{{ route('wishlist.add') }}",
                    type: "POST",
                    data: {
                        vehicle_id: vehicleId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            icon.removeClass('bi-heart').addClass('bi-heart-fill');
                        }
                    }
                });
            } else {
                // Remove from wishlist
                $.ajax({
                    url: "{{ route('wishlist.remove') }}",
                    type: "DELETE",
                    data: {
                        vehicle_id: vehicleId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            icon.removeClass('bi-heart-fill').addClass('bi-heart');
                        }
                    }
                });
            }
        });
        
        // Compare functionality
        $('.compare-btn').click(function() {
            const vehicleId = $(this).data('vehicle-id');
            
            $.ajax({
                url: "{{ route('comparison.add') }}",
                type: "POST",
                data: {
                    vehicle_id: vehicleId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        alert('Added to comparison list');
                    } else {
                        alert(response.message);
                    }
                }
            });
        });
    });
</script>
@endpush 