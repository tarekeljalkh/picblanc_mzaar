<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="w-px-150 h-auto">
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        <!-- Category Selector -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Category</span>
        </li>
        <li class="menu-item">
            <form id="category-form" method="POST" action="{{ route('set.category') }}" class="px-3 py-2">
                @csrf
                <div class="menu-link d-flex align-items-center">
                    <input type="radio" name="category" value="daily" id="daily-category"
                        class="form-check-input me-2" {{ session('category', 'daily') === 'daily' ? 'checked' : '' }}
                        onchange="document.getElementById('category-form').submit()">
                    <label for="daily-category" class="form-check-label text-truncate mb-0">
                        Daily
                    </label>
                </div>
                <div class="menu-link d-flex align-items-center">
                    <input type="radio" name="category" value="season" id="season-category"
                        class="form-check-input me-2" {{ session('category') === 'season' ? 'checked' : '' }}
                        onchange="document.getElementById('category-form').submit()">
                    <label for="season-category" class="form-check-label text-truncate mb-0">
                        Season
                    </label>
                </div>
            </form>
        </li>
        <!-- End of Category Selector -->


        <!-- Dashboard Header -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Dashboard</span>
        </li>


        <!-- Dashboard -->
        <li class="menu-item {{ Route::is('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate" data-i18n="Dashboards">Dashboard</div>
            </a>
        </li>

        <!-- POS -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">POS</span>
        </li>

        <li class="menu-item {{ Route::is('pos.index') ? 'active' : '' }}">
            <a href="{{ route('pos.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div class="text-truncate" data-i18n="POS">POS</div>
                <div class="badge rounded-pill bg-label-primary text-uppercase fs-tiny ms-auto"></div>
            </a>
        </li>

        <!-- Apps & Pages -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Apps &amp; Pages</span>
        </li>

        <!-- Customers -->
        <li class="menu-item {{ Route::is('customers.index') ? 'active' : '' }}">
            <a href="{{ route('customers.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                <div class="text-truncate" data-i18n="Customers">Customers</div>
            </a>
        </li>

        <!-- Products -->
        <li class="menu-item {{ Route::is('products.index') ? 'active' : '' }}">
            <a href="{{ route('products.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-chat"></i>
                <div class="text-truncate" data-i18n="Products">Products</div>
                <div class="badge rounded-pill bg-label-primary text-uppercase fs-tiny ms-auto"></div>
            </a>
        </li>

        <!-- Invoices -->
        <li class="menu-item {{ Route::is('invoices.index') ? 'active' : '' }}">
            <a href="{{ route('invoices.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar"></i>
                <div class="text-truncate" data-i18n="Invoices">Invoices</div>
                <div class="badge rounded-pill bg-label-primary text-uppercase fs-tiny ms-auto"></div>
            </a>
        </li>

        <!-- Drafts -->
        <!-- Drafts -->
        <li class="menu-item {{ Route::is('drafts.index') ? 'active' : '' }}">
            <a href="{{ route('drafts.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-trash text-danger"></i> <!-- Changed icon and color to red -->
                <div class="text-truncate" data-i18n="Invoices">Drafts</div>
                <div class="badge rounded-pill bg-label-danger text-uppercase fs-tiny ms-auto">Drafts</div>
                <!-- Changed badge color to red -->
            </a>
        </li>

        <!-- Balance -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Balance</span>
        </li>


        <!-- Trial Balance -->
        <li class="menu-item {{ Route::is('trialbalance.index') ? 'active' : '' }}">
            <a href="{{ route('trialbalance.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div class="text-truncate" data-i18n="Trial Balance">Trial Balance</div>
                <div class="badge rounded-pill bg-label-primary text-uppercase fs-tiny ms-auto"></div>
            </a>
        </li>

        <!-- Trial Balance by Products -->
        <li class="menu-item {{ Route::is('trialbalance.products') ? 'active' : '' }}">
            <a href="{{ route('trialbalance.products') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div class="text-truncate" data-i18n="Trial Balance by Products">Trial Balance by Products</div>
                <div class="badge rounded-pill bg-label-primary text-uppercase fs-tiny ms-auto"></div>
            </a>
        </li>

        @if (auth()->user()->role === 'admin')
            <!-- Users -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">System Users</span>
            </li>
            <!-- Users -->
            <li class="menu-item {{ Route::is('users.index') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i> <!-- Updated icon for users -->
                    <div class="text-truncate" data-i18n="Users">Users</div>
                    <div class="badge rounded-pill bg-label-primary text-uppercase fs-tiny ms-auto"></div>
                </a>
            </li>
        @endif

        <!-- Profile -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Profile</span>
        </li>


        <!-- Admin Profile Dropdown -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                <div class="text-truncate" data-i18n="Admin">Profile</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Route::is('profile.edit') ? 'active' : '' }}">
                    <a href="{{ route('profile.edit') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Edit Profile">Edit Profile</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="javascript:void(0);" onclick="document.getElementById('logout-form').submit();"
                        class="menu-link">
                        <div class="text-truncate" data-i18n="Logout">Logout</div>
                    </a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>

        <!-- End Admin Profile Dropdown -->

    </ul>
</aside>
