

 <header class="p-0 m-0">
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>
            {{-- <button type="button" class="burger-btn d-block border-0 bg-transparent">
                <i class="bi bi-justify fs-3"></i>
            </button> --}}
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                
                <ul class="navbar-nav ms-auto mb-lg-0 align-items-center">
                    @php
                    $isPremium   = session('isPremium');
                    $expireDate  = session('expireDate') ? \Carbon\Carbon::parse(session('expireDate')) : null;
                    $today       = \Carbon\Carbon::today();
                @endphp
                
                <h6 class="p-0 m-0 me-3 fw-bold"
                    style="color:
                        @if($isPremium)
                            green
                        @elseif($expireDate && $expireDate->isPast())
                            red
                        @else
                            red
                        @endif;
                    ">
                    @if($isPremium)
                        Vip Package
                    @elseif($expireDate)
                        @php
                            $daysLeft = $today->diffInDays($expireDate, false);
                        @endphp
                
                        @if($daysLeft <= 0)
                            Your subscription has expired.
                        @elseif($daysLeft <= 10)
                            Your subscription is about to expire in {{ $daysLeft }} day(s). Please renew soon.
                        @else
                            @php
                                $months = floor($daysLeft / 30);
                                $days   = $daysLeft % 30;
                            @endphp
                
                            @if($months > 0)
                                Next subscription ends in {{ $months }} month{{ $months > 1 ? 's' : '' }} and {{ $days }} day{{ $days != 1 ? 's' : '' }} from now.
                            @else
                                Next subscription ends in {{ $days }} day{{ $days != 1 ? 's' : '' }} on {{ $expireDate->format('d-M-Y') }}.
                            @endif
                        @endif
                    @endif
                </h6>
                    <li class="nav-item dropdown me-1">
                        <a class="nav-link active dropdown-toggle text-gray-600" href="#"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class='bi bi-bell bi-sub fs-6'></i>
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-lg-end shadow-sm border-top" aria-labelledby="dropdownMenuButton">
                            <li>
                                <h6 class="dropdown-header">Notifications</h6>
                            </li>
                            <li><a class="dropdown-item" href="#">No new notifications</a></li>
                        </ul>
                    </li>
                </ul>
                <div class="dropdown">
                    <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-menu d-flex">
                            
                            <div class="user-img d-flex align-items-center">
                                <div class="avatar avatar-md">
                                    <img src="{{ asset('mazer/images/noProfile.png') }}" alt="User">
                                </div>
                            </div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuButton"
                        style="min-width: 11rem;">
                        <li class="dropdown-header small text-muted px-3">
                                {{session("name")}}
                        </li>
                         <li>
                            <hr class="dropdown-divider">
                        </li>
                       
                        <li class="ms-2 text-danger">
                              <i class="icon-mid bi bi-box-arrow-left"></i>
                              {{-- <a class="dropdown-item text-danger" href="{{ route('logout') }}"> --}}
                                {{-- <a class="btn btn-sm" href="{{ route('logout') }}">
                                 Logout
                                </a> --}}

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                
                                <a class="btn btn-sm" href="#" 
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                   Logout
                                </a>
                        </li> 
                        <li class="ms-2">
                              <i class="icon-mid bi bi-lock"></i>
                              <button class="btn btn-sm">Change Password</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>

@push('scripts')
{{-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        const burger = document.querySelector('.burger-btn');
        const sidebar = document.querySelector('.sidebar-wrapper');

        burger.addEventListener('click', function (e) {
            e.preventDefault(); // 👈 stops href="#" from jumping to top
            sidebar.classList.toggle('active');
            console.log(sidebar);
        });
    });
</script> --}}
@endpush
