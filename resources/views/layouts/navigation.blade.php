<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('vehicles.index') }}">
            Vehicle Marketplace
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vehicles.index') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="vehicleTypeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Vehicles
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="vehicleTypeDropdown">
                        <li><a class="dropdown-item" href="{{ route('vehicles.by-type', 'car') }}">Cars</a></li>
                        <li><a class="dropdown-item" href="{{ route('vehicles.by-type', 'bike') }}">Bikes</a></li>
                    </ul>
                </li>
                @auth
                    @if(in_array(Auth::user()->role, ['admin', 'dealer', 'seller']))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('vehicles.create') ? 'active' : '' }}" href="{{ route('vehicles.create') }}">Add Listing</a>
                        </li>
                    @endif
                    
                    @if(Auth::user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                        </li>
                    @endif
                    
                    @if(Auth::user()->role === 'dealer')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dealer.dashboard') ? 'active' : '' }}" href="{{ route('dealer.dashboard') }}">Dealer Dashboard</a>
                        </li>
                    @endif
                @endauth
            </ul>
            
            <form class="d-flex me-2" action="{{ route('vehicles.index') }}" method="GET">
                <input class="form-control me-2" type="search" name="search" placeholder="Search vehicles..." aria-label="Search" value="{{ request('search') }}">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
            
            <ul class="navbar-nav">
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('wishlist.index') ? 'active' : '' }}" href="{{ route('wishlist.index') }}">
                            <i class="bi bi-heart"></i> Wishlist
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('comparison.index') ? 'active' : '' }}" href="{{ route('comparison.index') }}">
                            <i class="bi bi-bar-chart"></i> Compare
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav> 