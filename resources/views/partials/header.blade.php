<!-- Navigation -->
<nav class="navbar navbar-default navbar-dark navbar-expand-lg" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <a class="navbar-brand col-md-2 col-xs-3 add-env" href="{{ route('dashboard') }}">
        <img src="{{ asset('/images/logo_white.svg') }}" class="img-responsive">
        @if (env('APP_ENV') != 'production')
        <p class="text-danger">{{ strtoupper(env('APP_ENV')) }}</p>
        @endif
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav">
            <!-- Dashboard -->
            <li class="nav-item {{ setActiveNav('dashboard') }}">

                <a class="nav-link" href="{{ route('dashboard') }}"> <i class="fa fa-tachometer-alt"></i> Dashboard</a>

            </li>

            <!-- Requisitions -->
            <li class="nav-item dropdown {{ setActiveNav('requisition', 'procurement', 'purchase', 'order', 'voucher') }}">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                    <i class="far fa-file"></i> Requisitions

                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('requisition', ['q' => 'mine']) }}"><i class="fa fa-caret-square-right"></i> My requisitions</a>
                    <a class="dropdown-item" href="{{ route('requisition', ['q' => 'confirmed']) }}"><i class="fa fa-pen"></i> To be confirmed</a>
                    <a class="dropdown-item" href="{{ route('requisition', ['q' => 'delegating']) }}"><i class="fa fa-user-check"></i> Delegating</a>
                    <a class="dropdown-item" href="{{ route('requisition', ['q' => 'all']) }}"><i class="fa fa-file-signature"></i> All</a>
                    {{--
                    <div class='dropdown-divider'></div>
                    <a class="dropdown-item" href="{{ route('procurement') }}"><i class="fa rplus-icon-procurement"></i> Procurement</a>
                    <a class="dropdown-item disabled" href="#"><i class="fa rplus-icon-travel"></i> Travel</a>
                    <a class="dropdown-item disabled" href="#"><i class="fa rplus-icon-maintenance"></i> Maintenance</a>
                    <a class="dropdown-item disabled" href="#"><i class="fa rplus-icon-booking"></i> Room Booking</a>
                    <a class="dropdown-item disabled" href="#"><i class="fa rplus-icon-claim"></i> Claim for Expenses</a>
                    <a class="dropdown-item disabled" href="#"><i class="fa rplus-icon-loan"></i> Loan</a>
                    --}}
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('requisition/delegation') }}">Delegated tasks </a>

                </div>
            </li>

            @can('preference')
            <!-- Preference -->
            <li class="nav-item dropdown {{ setActiveNav('preferences', 'vendors', 'grades', 'drivers', 'vehicles') }}">
                <a class="nav-link dropdown-toggle " href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-asterisk"></i> Preferences <span class="caret"></span>
                </a>
                @can('vendor')
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('vendors.index') }}"><i class="fa fa-store"></i> Vendors</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('grades.index') }}"><i class="fa fa-sitemap"></i> Grades</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('drivers.index') }}"><i class="far fa-address-card"></i> Drivers </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('vehicles.index') }}"><i class="fa fa-car"></i> Vehicles </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('priority.index') }}"><i class="fa fa-sitemap"></i>Priorities</a>
                    
                </div>
                @endcan
            </li>
            @endcan

            @can('admin')
            <!-- System Settings -->
            <li class="nav-item dropdown {{ setActiveNav('settings', 'companies', 'units', 'unit', 'user', 'users', 'roles', 'rolePermission', 'authflow') }}">
                <a class="nav-link dropdown-toggle" href="#" id="settingMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                    <i class="fas fa-cog"></i> System Settings <span class="caret"></span>

                </a>
                <div class="dropdown-menu" aria-labelledby="settingMenuButton">
                    <a class="dropdown-item" href="{{ route('companies.index') }}"><i class="fa fa-building"></i> Companies</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('units.index') }}"><i class="far fa-building"></i> Units/Departments</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('users.index') }}"><i class="fa fa-users"></i> Users</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('vehicleTypes.index') }}"><i class="fa fa-car-side"></i> Vehicle Types</a>
                    <div class="dropdown-divider"></div>

                    <a class="dropdown-item" href="{{ route('campuses.index') }}"><i class="fa fa-building"></i> Campus </a>
                    <div class="dropdown-divider"></div>

                    <a class="dropdown-item" href="{{ route('roles.index') }}"><i class="fa fa-dice"></i> Roles</a>


                </div>
            </li>
            @endcan

        </ul>

        <!-- To be confirmed counter -->
        <div class="ml-auto">
            <a href="{{ route('requisition', ['q' => 'confirmed']) }}" class="badge badge-primary" data-toggle="tooltip" data-placement="bottom" title="Requisitions to be confirmed ">
                <i class="fa fa-pen"></i>
                <span class="badge badge-secondary text-white">
                    <span id="confirmed-counter">0</span>
                </span>
            </a>
        </div>

        <!-- Notification counter -->
        <div class="dropdown ml-2">
            <a href="javascript:void(0);" class="badge badge-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="far fa-bell"></i>
                <span class="badge badge-secondary">
                    <span id="notification-counter">0</span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" style="width: 300px;">
                <div id="notificationsMenu">
                    <div class="dropdown-header">No notifications</div>
                </div>
                <a class="dropdown-item" href="{{ route('notifications') }}"><i class="fa fa-mail-bulk"></i> See all notifications</a>

            </div>
        </div>

        <!-- User profile -->
        <ul class="navbar-nav ml-3 mr-3">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu" role="menu">
                    <div class="dropdown-item">
                        <span class="" id="username">{{ Auth::user()->username }}</span>
                        <a class="btn btn-sm btn-outline-secondary ml-3" href="javascript:copyText('username');"><i class="fa fa-copy"></i> </a>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('userSetting/profile') }}">Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('userSetting') }}">User Settings</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('logout') }}">Log Out</a>
                </div>
            </li>
        </ul>

    </div>
</nav>
