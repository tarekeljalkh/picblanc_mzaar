<!-- Navbar -->




<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">











    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0   d-xl-none ">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="bx bx-menu bx-md"></i>
        </a>
    </div>


    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">


        <ul class="navbar-nav flex-row align-items-center ms-auto">

            {{-- Year Switcher --}}
            <li class="nav-item dropdown me-4 pe-3">

                @php
                    $years = getAvailableYears();
                    $activeYear = request()->cookie('active_year', date('Y'));
                @endphp

                <button class="btn btn-outline-primary dropdown-toggle py-1 px-3" type="button" id="yearSwitcherBtn"
                    data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 8px;">
                    <i class="bx bx-calendar me-1"></i> {{ $activeYear }}
                </button>

                <ul class="dropdown-menu" aria-labelledby="yearSwitcherBtn" style="cursor:pointer;">
                    @foreach ($years as $year)
                        <li>
                            <a class="dropdown-item year-option {{ $activeYear == $year ? 'active' : '' }}"
                                data-year="{{ $year }}">
                                {{ $year }}
                            </a>
                        </li>
                    @endforeach
                </ul>

            </li>


            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('logo.png') }}" alt class="w-px-40 h-auto rounded-circle">
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('logo.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                    <small class="text-muted">{{ auth()->user()->role }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bx bx-user bx-md me-3"></i><span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);"
                            onclick="document.getElementById('logout-form').submit();">
                            <i class="bx bx-power-off bx-md me-3"></i><span>Log Out</span>
                        </a>
                        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
            <!--/ User -->


        </ul>
    </div>



</nav>



<!-- / Navbar -->
