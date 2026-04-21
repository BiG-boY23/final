<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SmartGate — @yield('title', 'Dashboard')</title>

    <!-- Icons & Fonts -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bulletproof Theme Styles (Vite Fallback) -->
    <style>
        :root[data-theme='dark'] {
            --bg-app: #0f172a;
            --bg-card: #1e293b;
            --bg-input: #0f172a;
            --bg-header: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --color-evsu-gold: #fbbf24;
            color-scheme: dark;
        }
        body { background-color: var(--bg-app); color: var(--text-main); }
        .sidebar { 
            background-color: #450a0a !important; 
            overflow-y: scroll !important; 
            display: flex !important;
            flex-direction: column !important;
            height: 100vh !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            z-index: 2000 !important;
            width: 280px !important;
        }
        .sidebar::-webkit-scrollbar {
            width: 14px !important;
        }
        .sidebar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2) !important;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #fdb913 !important; /* GOLD - High Contrast */
            border-radius: 20px !important;
            border: 3px solid #450a0a !important; /* Matches sidebar bg */
        }
        [data-theme='dark'] .glass { background: rgba(30, 41, 59, 0.8) !important; border-color: #334155 !important; }
        [data-theme='dark'] .modal-container { background-color: #1e293b !important; color: #f8fafc !important; }
        [data-theme='dark'] input, 
        [data-theme='dark'] select { background-color: #0f172a !important; color: #f8fafc !important; border-color: #334155 !important; }

        @media print {
            .no-print, .sidebar, .header, .app-footer, .header-actions, .nav-links { display: none !important; }
            html, body, main, .main-content, .page-body { 
                margin: 0 !important; 
                padding: 0 !important; 
                width: 100% !important; 
                height: auto !important;
                background: white !important; 
                position: relative !important;
                left: 0 !important;
                overflow: visible !important;
            }
        }
            }
        }
        

    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'success', title: 'Success!', text: "{{ addslashes(session('success')) }}", confirmButtonColor: '#741b1b' });
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'error', title: 'Error!', text: "{{ addslashes(session('error')) }}", confirmButtonColor: '#741b1b' });
            });
        </script>
    @endif

    {{-- ── Sidebar ── --}}
    <aside class="sidebar no-print">
        <div class="brand">
            <div class="brand-logo-container">
                <img src="{{ asset('images/evsu-logo.png') }}" alt="EVSU Logo">
            </div>
            <div class="brand-text">Smart<span style="color:white;">Gate</span></div>
        </div>

        <ul class="nav-links">
            @php
                $role = auth()->user()->role;
                $pendingCount = 0;
                if ($role === 'office') {
                    $pendingCount = \App\Models\VehicleRegistration::where('status','pending')->count();
                }
            @endphp

            {{-- ── Role-Based Links ── --}}
            @if($role === 'admin')
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="ph ph-squares-four"></i> Dashboard Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.rfid') }}" class="{{ request()->routeIs('admin.rfid') ? 'active' : '' }}">
                        <i class="ph ph-identification-card"></i> RFID Monitoring
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                        <i class="ph ph-files"></i> Reports &amp; Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.traffic-analytics') }}" class="{{ request()->routeIs('admin.traffic-analytics') ? 'active' : '' }}">
                        <i class="ph ph-chart-line-up"></i> Traffic Analytics
                    </a>
                </li>
                {{-- Administration Dropdown --}}
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="dropdown-trigger {{ request()->is('admin/manage*') || request()->routeIs('admin.users') ? 'active' : '' }}" onclick="this.parentElement.classList.toggle('open')">
                        <i class="ph ph-users-four"></i> Administration
                        <i class="ph ph-caret-down" style="font-size:0.7rem;"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}"><i class="ph ph-user-circle-gear"></i> User Management</a></li>
                        <li><a href="{{ route('admin.manage.fleet') }}" class="{{ request()->is('admin/manage/fleet*') ? 'active' : '' }}"><i class="ph ph-car-profile"></i> Manage Fleet Assets</a></li>
                        <li><a href="{{ route('admin.manage.academic') }}" class="{{ request()->is('admin/manage/academic*') ? 'active' : '' }}"><i class="ph ph-graduation-cap"></i> Academic Data</a></li>

                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="dropdown-trigger {{ request()->is('admin/stats*') ? 'active' : '' }}" onclick="this.parentElement.classList.toggle('open')">
                        <i class="ph ph-chart-pie"></i> Fleet Statistics
                        <i class="ph ph-caret-down" style="font-size:0.7rem;"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('admin.stats.demographics') }}" class="{{ request()->routeIs('admin.stats.demographics') ? 'active' : '' }}"><i class="ph ph-users-three"></i> Vehicle Demographics</a></li>
                        <li><a href="{{ route('admin.stats.expiry') }}" class="{{ request()->routeIs('admin.stats.expiry') ? 'active' : '' }}"><i class="ph ph-calendar-x"></i> Tag Expiry Tracking</a></li>
                        <li><a href="{{ route('admin.stats.behavior') }}" class="{{ request()->routeIs('admin.stats.behavior') ? 'active' : '' }}"><i class="ph ph-presentation-chart"></i> Owner Behavior</a></li>
                    </ul>
                </li>
                {{-- Issuance & Payment Status Dropdown (Available for Admin & Office) --}}
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="dropdown-trigger {{ request()->is('manage/payments*') ? 'active' : '' }}" onclick="this.parentElement.classList.toggle('open')">
                        <i class="ph ph-hand-coins"></i> Issuance & Status
                        <i class="ph ph-caret-down" style="font-size:0.7rem;"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('payments.pending') }}" class="{{ request()->routeIs('payments.pending') ? 'active' : '' }}"><i class="ph ph-credit-card"></i> Manage Issuance</a></li>
                        <li><a href="{{ route('payments.ledger') }}" class="{{ request()->routeIs('payments.ledger') ? 'active' : '' }}"><i class="ph ph-receipt"></i> Financial Ledger</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        <i class="ph ph-gear"></i> System Settings
                    </a>
                </li>
            @elseif($role === 'office')
                <li class="nav-item">
                    <a href="{{ route('office.dashboard') }}" class="{{ request()->routeIs('office.dashboard') ? 'active' : '' }}">
                        <i class="ph ph-squares-four"></i> Office Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('office.registration') }}" class="{{ request()->routeIs('office.registration') ? 'active' : '' }}" style="position:relative;">
                        <i class="ph ph-user-plus"></i> Owner Registration
                        @if($pendingCount > 0)
                            <span class="nav-badge">{{ $pendingCount > 99 ? '99+' : $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('office.users') }}" class="{{ request()->routeIs('office.users') ? 'active' : '' }}">
                        <i class="ph ph-users"></i> Registered Accounts
                    </a>
                </li>

                {{-- Issuance & Payment Status Dropdown (Available for Admin & Office) --}}
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="dropdown-trigger {{ request()->is('manage/payments*') ? 'active' : '' }}" onclick="this.parentElement.classList.toggle('open')">
                        <i class="ph ph-hand-coins"></i> Issuance & Status
                        <i class="ph ph-caret-down" style="font-size:0.7rem;"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('payments.pending') }}" class="{{ request()->routeIs('payments.pending') ? 'active' : '' }}"><i class="ph ph-credit-card"></i> Manage Issuance</a></li>
                        <li><a href="{{ route('payments.ledger') }}" class="{{ request()->routeIs('payments.ledger') ? 'active' : '' }}"><i class="ph ph-receipt"></i> Financial Ledger</a></li>
                    </ul>
                </li>
                
                {{-- Statistics Dropdown --}}
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="dropdown-trigger {{ request()->is('office/stats*') ? 'active' : '' }}" onclick="this.parentElement.classList.toggle('open')">
                        <i class="ph ph-chart-pie"></i> Fleet Statistics
                        <i class="ph ph-caret-down" style="font-size:0.7rem;"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('office.stats.demographics') }}" class="{{ request()->routeIs('office.stats.demographics') ? 'active' : '' }}"><i class="ph ph-users-three"></i> Vehicle Demographics</a></li>
                        <li><a href="{{ route('office.stats.expiry') }}" class="{{ request()->routeIs('office.stats.expiry') ? 'active' : '' }}"><i class="ph ph-calendar-x"></i> Tag Expiry Tracking</a></li>
                        <li><a href="{{ route('office.stats.behavior') }}" class="{{ request()->routeIs('office.stats.behavior') ? 'active' : '' }}"><i class="ph ph-presentation-chart"></i> Owner Behavior</a></li>
                    </ul>
                </li>


            @elseif($role === 'guard')
                <li class="nav-item">
                    <a href="{{ route('guard.dashboard') }}" class="{{ request()->routeIs('guard.dashboard') ? 'active' : '' }}">
                        <i class="ph ph-shield-check"></i> Guard Workspace
                    </a>
                </li>
                
                {{-- Visitor Dropdown --}}
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="dropdown-trigger {{ request()->routeIs('guard.entry') || request()->routeIs('guard.exit') || request()->routeIs('guard.visitor.analytics') ? 'active' : '' }}" onclick="this.parentElement.classList.toggle('open')">
                        <i class="ph ph-identification-badge"></i> Visitor Management
                        <i class="ph ph-caret-down" style="font-size:0.7rem;"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('guard.entry') }}" class="{{ request()->routeIs('guard.entry') ? 'active' : '' }}"><i class="ph ph-sign-in"></i> Log Visitor Entry</a></li>
                        <li><a href="{{ route('guard.exit') }}" class="{{ request()->routeIs('guard.exit') ? 'active' : '' }}"><i class="ph ph-sign-out"></i> Log Visitor Exit</a></li>
                        <li><a href="{{ route('guard.visitor.analytics') }}" class="{{ request()->routeIs('guard.visitor.analytics') ? 'active' : '' }}"><i class="ph ph-presentation-chart"></i> Visitor Insights</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="{{ route('guard.analytics') }}" class="{{ request()->routeIs('guard.analytics') ? 'active' : '' }}">
                        <i class="ph ph-chart-bar"></i> Traffic Analytics
                    </a>
                </li>
            @endif

            <li style="flex-grow:1;"></li>

            {{-- ── Sidebar Logout (Secondary) ── --}}
            <li class="nav-item">
                <a href="#" id="sidebarLogout">
                    <i class="ph ph-sign-out"></i> {{ __('messages.logout') }}
                </a>
            </li>
        </ul>
    </aside>

    <style>
        .nav-item.dropdown .dropdown-menu { display: none; list-style: none; padding-left: 1.5rem; margin-top: 0.5rem; background: rgba(0,0,0,0.1); border-radius: 8px; }
        .nav-item.dropdown.open .dropdown-menu { display: block; animation: slideDown 0.2s ease; }
        .nav-item.dropdown.open .ph-caret-down { transform: rotate(180deg); transition: 0.2s; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .dropdown-menu li a { padding: 0.6rem 1rem !important; font-size: 0.85rem !important; opacity: 0.85; border: none !important; }
        .dropdown-menu li a:hover { opacity: 1; background: rgba(255,255,255,0.05); }
        .dropdown-menu li a.active { background: #fdb913 !important; color: #741b1b !important; opacity: 1; }
    </style>

    {{-- ── Main Content Area ── --}}
    <main class="main-content">

        {{-- ── Professional Top Header ── --}}
        <header class="header no-print">
            <div class="page-title">
                <h1>@yield('title', __('messages.dashboard'))</h1>
                <p>@yield('subtitle', __('messages.monitoring_tag'))</p>
            </div>
            


            <div class="header-actions">
                @if(in_array(auth()->user()->role ?? '', ['admin', 'office']))
                <div class="notification-wrapper" style="position: relative; margin-right: 15px;">
                    <a href="{{ auth()->user()->role === 'admin' ? route('admin.rfid') : route('office.registration') }}" class="nav-bell" style="position: relative; border: none; background: #f8fafc; border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; color: #64748b; transition: all 0.2s; text-decoration: none;">
                        <i class="ph ph-bell" style="font-size: 1.5rem;"></i>
                        <span id="navNotifBadge" style="display: none; position: absolute; top: -2px; right: -2px; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 10px; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">0</span>
                    </a>
                </div>
                @endif
                {{-- User Profile / Dropdown Wrapper --}}
                <div class="user-dropdown-wrapper" id="userDropdownWrapper" style="position: relative; overflow: visible;">
                    <button class="user-profile" id="userProfileBtn">
                        <div class="avatar" id="headerAvatarContainer" style="display:flex; align-items:center; justify-content:center; overflow:hidden; border-radius:50%; width:44px; height:44px; background:#f1f5f9; border: 1px solid #e2e8f0;">
                            @if(auth()->check() && !empty(auth()->user()->profile_picture))
                                <img src="{{ Storage::url(auth()->user()->profile_picture) }}?v={{ time() }}" id="headerAvatarImg" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <div id="headerAvatarPlaceholder" style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                                    <i class="ph ph-user" style="font-size: 1.5rem; color: #94a3b8;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="user-info">
                            <span class="user-name" id="headerUserName">{{ auth()->user() ? (auth()->user()->first_name . ' ' . auth()->user()->last_name) : 'Security Guard' }}</span>
                        </div>
                        <i class="ph ph-caret-down dropdown-caret" id="dropdownCaret"></i>
                    </button>

                    {{-- ULTIMATE BULLETPROOF FLOATING WINDOW - FULLY INLINE STYLES --}}
                    <div class="user-menu-window" id="userDropdown" 
                         style="display: none; position: absolute; top: calc(100% + 15px); right: 0; width: 300px; background-color: white; color: #1e293b; z-index: 10000; box-shadow: 0 20px 60px -10px rgba(0,0,0,0.3); border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; text-align: left; font-family: 'Inter', sans-serif;" 
                         role="menu">
                        
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <div id="dropdownAvatarContainer" style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; color: #64748b; font-weight: 800; font-size: 1.25rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; border: 1px solid #e2e8f0;">
                                @if(auth()->user() && auth()->user()->profile_picture)
                                    <img src="{{ Storage::url(auth()->user()->profile_picture) }}?t={{ time() }}" id="dropdownAvatarImg" style="width:100%; height:100%; object-fit:cover;">
                                @else
                                    <i class="ph ph-user" id="dropdownAvatarFallback" style="font-size: 1.5rem;"></i>
                                @endif
                            </div>
                            <div style="flex:1; min-width: 0;">
                                <div id="dropdownUserName" style="font-weight: 800; font-size: 1rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user() ? (auth()->user()->first_name . ' ' . auth()->user()->last_name) : 'User' }}</div>
                                <div style="font-size: 0.75rem; color: #fdb913; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">{{ $role ? ucfirst($role) : 'User' }}</div>
                                <div id="dropdownUserEmail" style="font-size: 0.8rem; color: #64748b; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()?->email ?? auth()->user()?->username ?? '' }}</div>
                            </div>
                        </div>

                        {{-- Dark Mode Toggle Row --}}
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; font-weight: 600; color: #1e293b; border-bottom: 1px solid #e2e8f0;">
                            <div style="display: flex; align-items: center; gap: 0.875rem;">
                                <i class="ph ph-moon" style="font-size: 1.1rem;"></i> {{ __('messages.dark_mode') }}
                            </div>
                            <button id="darkModeToggle" class="theme-switch" title="Toggle Theme" style="width: 44px; height: 24px; background: #e2e8f0; border-radius: 30px; border: none; position: relative; cursor: pointer;">
                                <span class="theme-switch-slider" style="position: absolute; left: 3px; top: 3px; width: 18px; height: 18px; background: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.15);"></span>
                            </button>
                        </div>

                        {{-- Links --}}
                        {{-- Profile Modal Trigger --}}
                        <button id="btnAccountSettings" 
                           style="display: flex; align-items: center; gap: 0.875rem; padding: 1rem 1.25rem; font-weight: 600; color: #1e293b; border: none; background: none; width: 100%; cursor: pointer; text-align: left; border-bottom: 1px solid #e2e8f0; transition: background 0.2s;"
                           onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'">
                            <i class="ph ph-user-circle" style="font-size: 1.1rem;"></i> {{ __('messages.account_settings') }}
                        </button>
                        
                        {{-- Security Link (Admin/Office/MESO) --}}
                        @if(in_array(auth()->user()->role, ['admin', 'office']))
                            <a href="{{ route('2fa.setup') }}" 
                               style="display: flex; align-items: center; gap: 0.875rem; padding: 1rem 1.25rem; font-weight: 600; color: #1e293b; text-decoration: none; cursor: pointer; border-bottom: 1px solid #e2e8f0; transition: background-color 0.2s;"
                               onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'">
                                <i class="ph ph-shield-check" style="font-size: 1.1rem;"></i> Security & 2FA
                            </a>
                        @endif

                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.logs') }}" 
                               style="display: flex; align-items: center; gap: 0.875rem; padding: 1rem 1.25rem; font-weight: 600; color: #1e293b; text-decoration: none; cursor: pointer; border-bottom: 1px solid #e2e8f0; transition: background-color 0.2s;"
                               onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'">
                                <i class="ph ph-clock-clockwise" style="font-size: 1.1rem;"></i> Activity Log
                            </a>
                        @endif
                        
                        <button id="dropdownLogout" 
                                style="display: flex; align-items: center; gap: 0.875rem; padding: 1rem 1.25rem; font-weight: 600; color: #dc2626; border: none; background: none; width: 100%; cursor: pointer; text-align: left;"
                                onmouseover="this.style.backgroundColor='#fef2f2'" onmouseout="this.style.backgroundColor='transparent'">
                            <i class="ph ph-sign-out" style="font-size: 1.1rem;"></i> {{ __('messages.logout') }}
                        </button>

                    </div>
                </div>
            </div>
        </header>

        {{-- Page-Specific Content --}}
        <div class="page-body">
            @yield('content')
        </div>

        {{-- Professional Footer --}}
        <footer class="app-footer no-print">
            <p>&copy; 2026 SmartGate System &mdash; Developed by</p>
            <img src="{{ asset('images/chocobol-logo.png') }}" alt="Chocobol Logo">
        </footer>
    </main>

    {{-- ── ULTIMATE BULLETPROOF SETTINGS WINDOW (COMPREHENSIVE FLOATING) ── --}}
    <div id="settingsModal" class="modal-overlay" 
         style="display: none; position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); z-index: 1050; align-items: center; justify-content: center; padding: 1.5rem; font-family: 'Inter', sans-serif;">
        
        <div class="modal-container pulse-in" 
             style="width: 100%; max-width: 650px; background-color: #ffffff; color: #1e293b; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden; display: flex; flex-direction: column;">
            
            <div class="modal-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc;">
                <h3 style="display: flex; align-items: center; gap: 0.75rem; margin: 0; font-size: 1.25rem; font-weight: 800; color: #1e293b;">
                    <i class="ph ph-user-gear"></i> Account Settings
                </h3>
                <button class="close-modal" id="closeSettings" style="background: transparent; border: none; font-size: 1.75rem; color: #64748b; cursor: pointer;">&times;</button>
            </div>
            
            {{-- Tabs --}}
            <div class="modal-tabs" style="display: flex; gap: 1.5rem; padding: 0 2rem; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <button class="modal-tab active" data-tab="tab-identity" 
                        style="padding: 1rem 0.25rem; font-size: 0.9rem; font-weight: 800; color: #741b1b; cursor: pointer; border: none; background: none; border-bottom: 3px solid #fdb913; transition: 0.2s;">Identity</button>
                <button class="modal-tab" data-tab="tab-security" 
                        style="padding: 1rem 0.25rem; font-size: 0.9rem; font-weight: 700; color: #64748b; cursor: pointer; border: none; background: none; border-bottom: 3px solid transparent; transition: 0.2s;">Security</button>
                <button class="modal-tab" data-tab="tab-preferences" 
                        style="padding: 1rem 0.25rem; font-size: 0.9rem; font-weight: 700; color: #64748b; cursor: pointer; border: none; background: none; border-bottom: 3px solid transparent; transition: 0.2s;">Preferences</button>
                <button class="modal-tab" data-tab="tab-activity" 
                        style="padding: 1rem 0.25rem; font-size: 0.9rem; font-weight: 700; color: #64748b; cursor: pointer; border: none; background: none; border-bottom: 3px solid transparent; transition: 0.2s;">Activity</button>
            </div>

            <form id="profileUpdateForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" style="margin: 0;">
                @csrf
                <div class="modal-body" style="padding: 2rem; max-height: 65vh; overflow-y: auto; background-color: white;">
                    
                    {{-- 1. Identity Tab --}}
                    <div class="tab-content active" id="tab-identity">
                        <div class="profile-upload-container" style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; padding: 1.25rem; background-color: #fefce8; border-radius: 12px; border: 1px dashed #fdb913;">
                            <div class="profile-preview-wrapper" id="profileImagePreview" style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background-color: #e2e8f0; border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); flex-shrink: 0;">
                                @if(auth()->user()->profile_picture)
                                    <img src="{{ Storage::url(auth()->user()->profile_picture) }}?v={{ time() }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="width:100%; height:100%; background-color:#741b1b; color:white; display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:800;">
                                        {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div style="flex: 1;">
                                <h5 style="font-size: 1rem; font-weight: 800; margin: 0 0 0.25rem 0; color: #1e293b;">Profile Picture</h5>
                                <p style="font-size: 0.8rem; color: #64748b; margin: 0 0 0.75rem 0;">Help others identify you easily.</p>
                                <input type="file" name="profile_picture" accept="image/*" id="profileImageInput" style="font-size: 0.85rem; color: #64748b;">
                            </div>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <h4 style="font-size: 0.9rem; font-weight: 800; color: #fdb913; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 1.25rem;">Personal Information</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <label style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">First Name</label>
                                    <input type="text" name="first_name" value="{{ auth()->user()->first_name }}" required 
                                           style="padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid #e2e8f0; background-color: #f8fafc;">
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <label style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">Last Name</label>
                                    <input type="text" name="last_name" value="{{ auth()->user()->last_name }}" required
                                           style="padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid #e2e8f0; background-color: #f8fafc;">
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <label style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">Email Address</label>
                                <input type="email" name="email" value="{{ auth()->user()->email }}" required
                                       style="padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid #e2e8f0; background-color: #f8fafc;">
                            </div>
                        </div>
                    </div>

                    {{-- 2. Security Tab --}}
                    <div class="tab-content" id="tab-security" style="display: none;">
                        <div style="margin-bottom: 2rem;">
                            <h4 style="font-size: 0.9rem; font-weight: 800; color: #fdb913; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 0.5rem;">Change Password</h4>
                            <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 1.25rem;">Provide your current password to make changes.</p>
                            
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1.25rem;">
                                <label style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">Current Password</label>
                                <input type="password" name="current_password" placeholder="••••••••" style="padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <label style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">New Password</label>
                                    <input type="password" name="new_password" placeholder="Min. 8 chars" style="padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <label style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">Confirm Password</label>
                                    <input type="password" name="new_password_confirmation" placeholder="Repeat password" style="padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 style="font-size: 0.9rem; font-weight: 800; color: #fdb913; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 1.25rem;">Security Factors</h4>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <label style="display: block; font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0;">Two-Factor Authentication (2FA)</label>
                                    <p style="font-size: 0.8rem; color: #64748b; margin: 2px 0 0 0;">Require email verification code on login.</p>
                                </div>
                                <button type="button" id="toggle2FA" class="theme-switch {{ auth()->user()->two_factor_enabled ? 'active' : '' }}" 
                                        style="width: 44px; height: 24px; background: #e2e8f0; border-radius: 30px; border: none; position: relative; cursor: pointer;">
                                    <span class="theme-switch-slider" style="position: absolute; left: 3px; top: 3px; width: 18px; height: 18px; background: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.15); {{ auth()->user()->two_factor_enabled ? 'transform: translateX(20px);' : '' }}"></span>
                                </button>
                                <input type="hidden" name="two_factor_enabled" id="two_factor_val" value="{{ auth()->user()->two_factor_enabled ? '1' : '0' }}">
                            </div>
                        </div>
                    </div>

                    {{-- 3. Preferences Tab --}}
                    <div class="tab-content" id="tab-preferences" style="display: none;">
                        <div style="margin-bottom: 2rem;">
                            <h4 style="font-size: 0.9rem; font-weight: 800; color: #fdb913; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 1.25rem;">User Interface</h4>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <label style="display: block; font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0;">Persistent Dark Mode</label>
                                    <p style="font-size: 0.8rem; color: #64748b; margin: 2px 0 0 0;">Save your theme preference to the cloud.</p>
                                </div>
                                <button type="button" id="prefDarkModeToggle" class="theme-switch {{ auth()->user()->dark_mode ? 'active' : '' }}" 
                                        style="width: 44px; height: 24px; background: #e2e8f0; border-radius: 30px; border: none; position: relative; cursor: pointer;">
                                    <span class="theme-switch-slider" style="position: absolute; left: 3px; top: 3px; width: 18px; height: 18px; background: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.15); {{ auth()->user()->dark_mode ? 'transform: translateX(20px);' : '' }}"></span>
                                </button>
                                <input type="hidden" name="dark_mode" id="dark_mode_val" value="{{ auth()->user()->dark_mode ? '1' : '0' }}">
                            </div>
                        </div>

                        <div>
                            <h4 style="font-size: 0.9rem; font-weight: 800; color: #fdb913; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 1.25rem;">Language & Localization</h4>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <label style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">System Language</label>
                                <select name="language" style="width:100%; padding:0.875rem; border-radius:8px; border:1px solid #e2e8f0; background:white; color:#1e293b; font-weight:600;">
                                    <option value="en" {{ auth()->user()->language == 'en' ? 'selected' : '' }}>English (US)</option>
                                    <option value="tl" {{ auth()->user()->language == 'tl' ? 'selected' : '' }}>Tagalog (Pilipinas)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Activity Tab --}}
                    <div class="tab-content" id="tab-activity" style="display: none;">
                        <div>
                            <h4 style="font-size: 0.9rem; font-weight: 800; color: #fdb913; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 0.5rem;">Login History</h4>
                            <p style="font-size: 0.8rem; color: #64748b; margin-bottom: 1.25rem;">Recent account access events for your security.</p>
                            
                            <div id="loginHistoryList" style="display: flex; flex-direction: column;">
                                <div style="text-align:center; padding:3rem; color:#94a3b8;">
                                    <i class="ph ph-spinner animate-spin" style="font-size:2rem; margin-bottom: 1rem; display: block;"></i>
                                    <span>Syncing audit logs...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer" style="padding: 1.5rem 2rem; background-color: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn-secondary" id="cancelSettings" 
                            style="padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 700; background: transparent; border: 1px solid #e2e8f0; color: #64748b; cursor: pointer;">Cancel</button>
                    <button type="submit" class="btn-primary" id="saveSettings" 
                            style="padding: 0.75rem 2rem; border-radius: 8px; font-weight: 700; background-color: #741b1b; color: white; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                        <span class="btn-text">Save All Changes</span>
                        <i class="ph ph-circle-notch animate-spin" style="display:none;"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <form id="logoutForm" action="{{ route('logout') }}" method="GET" style="display:none;"></form>

    {{-- ── Core Layout Logic ── --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const html = document.documentElement;
        const toggleBtn = document.getElementById('darkModeToggle');
        const themeSlider = document.querySelector('.theme-switch-slider');
        const wrapper = document.getElementById('userDropdownWrapper');
        const profileBtn = document.getElementById('userProfileBtn');
        const dropdown = document.getElementById('userDropdown');
        const caret = document.getElementById('dropdownCaret');
        const btnAccountSettings = document.getElementById('btnAccountSettings');
        const dropdownLogout = document.getElementById('dropdownLogout');
        const sidebarLogout = document.getElementById('sidebarLogout');
        const settingsModal = document.getElementById('settingsModal');
        const closeSettings = document.getElementById('closeSettings');
        const cancelSettings = document.getElementById('cancelSettings');
        const profileForm = document.getElementById('profileUpdateForm');

        // 1. Theme Logic
        function updateTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('sg_theme', theme);
            
            // Sync all theme toggles on page
            const sliders = document.querySelectorAll('.theme-switch-slider');
            const btns = [toggleBtn, document.getElementById('prefDarkModeToggle')];
            
            btns.forEach(btn => {
                if(!btn) return;
                if(theme === 'dark') {
                    btn.classList.add('active');
                    btn.style.backgroundColor = '#741b1b';
                } else {
                    btn.classList.remove('active');
                    btn.style.backgroundColor = '#e2e8f0';
                }
            });

            if (themeSlider) {
                themeSlider.style.transform = theme === 'dark' ? 'translateX(20px)' : 'translateX(0)';
            }
            
            document.getElementById('dark_mode_val').value = (theme === 'dark' ? '1' : '0');
        }

        updateTheme(localStorage.getItem('sg_theme') || 'light');

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const current = html.getAttribute('data-theme');
            updateTheme(current === 'dark' ? 'light' : 'dark');
        });

        // Modal Specific Toggle
        document.getElementById('prefDarkModeToggle').addEventListener('click', function() {
            const current = html.getAttribute('data-theme');
            updateTheme(current === 'dark' ? 'light' : 'dark');
        });

        // 2. Dropdown Logic
        function toggleDropdown() {
            const isHidden = dropdown.style.display === 'none';
            if (isHidden) {
                dropdown.style.display = 'block';
                caret.style.transform = 'rotate(180deg)';
                profileBtn.classList.add('active');
            } else {
                dropdown.style.display = 'none';
                caret.style.transform = '';
                profileBtn.classList.remove('active');
            }
        }
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown();
        });
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                dropdown.style.display = 'none';
                caret.style.transform = '';
                profileBtn.classList.remove('active');
            }
        });

        // 3. Modal Toggles (2FA, Language, etc)
        document.getElementById('toggle2FA').addEventListener('click', function() {
            this.classList.toggle('active');
            const val = this.classList.contains('active') ? '1' : '0';
            document.getElementById('two_factor_val').value = val;
            this.style.backgroundColor = (val === '1' ? '#741b1b' : '#e2e8f0');
        });

        // Tab Switching
        const tabs = document.querySelectorAll('.modal-tab');
        const contents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;
                
                // Update Tab Styles
                tabs.forEach(t => {
                    t.style.color = '#64748b';
                    t.style.borderBottomColor = 'transparent';
                    t.style.fontWeight = '700';
                });
                tab.style.color = '#741b1b';
                tab.style.borderBottomColor = '#fdb913';
                tab.style.fontWeight = '800';

                // Update Content Visibility
                contents.forEach(c => c.style.display = 'none');
                const targetContent = document.getElementById(target);
                targetContent.style.display = 'block';
                
                if(target === 'tab-activity') fetchLoginHistory();
            });
        });


        // Image Preview
        document.getElementById('profileImageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('profileImagePreview').innerHTML = `<img src="${event.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                reader.readAsDataURL(file);
            }
        });

        // 4. Activity Log Fetcher
        async function fetchLoginHistory() {
            const container = document.getElementById('loginHistoryList');
            try {
                const response = await fetch('{{ route('profile.login-history') }}');
                const logs = await response.json();
                
                if(!logs.length) {
                    container.innerHTML = '<div style="text-align:center; padding:2rem; color:var(--text-muted);">No recent login activity found.</div>';
                    return;
                }

                container.innerHTML = logs.map(log => `
                    <div class="log-item">
                        <div class="log-info">
                            <h6>Success Login</h6>
                            <span>${new Date(log.login_at).toLocaleString()}</span>
                        </div>
                        <div class="log-meta">
                            <div>IP: ${log.ip_address}</div>
                            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:400;">${log.user_agent.split(' ')[0]}</div>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                container.innerHTML = '<div style="color:var(--color-danger); padding:1rem; text-align:center;">Failed to load history.</div>';
            }
        }

        // 5. Safety Logout
        function confirmLogout() {
            dropdown.style.display = 'none';
            Swal.fire({
                icon: 'warning',
                title: 'Logout from SmartGate?',
                html: '<p style="color:#64748b;font-size:0.95rem;">Are you sure? Any unsaved work will be lost.</p>',
                showCancelButton: true,
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Stay Connected',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#741b1b',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('logoutForm').submit();
            });
        }
        dropdownLogout.addEventListener('click', (e) => {
            e.stopPropagation();
            confirmLogout();
        });
        if (sidebarLogout) sidebarLogout.addEventListener('click', (e) => {
            e.preventDefault();
            confirmLogout();
        });

        // 6. Account Settings Modal lifecycle
        btnAccountSettings.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.style.display = 'none';
            settingsModal.style.display = 'flex';
        });
        const closeModal = () => settingsModal.style.display = 'none';
        closeSettings.addEventListener('click', closeModal);
        cancelSettings.addEventListener('click', closeModal);
        settingsModal.addEventListener('click', (e) => { if (e.target === settingsModal) closeModal(); });
        
        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('saveSettings');
            const btnText = btn.querySelector('.btn-text');
            const btnIcon = btn.querySelector('.animate-spin');
            btn.disabled = true;
            btnText.innerText = 'Saving...';
            btnIcon.style.display = 'inline-block';
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(profileForm.action, {
                    method: 'POST', 
                    body: new FormData(profileForm),
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    let errorMessage = 'Server returned ' + response.status;
                    try {
                        const errorJson = JSON.parse(errorText);
                        errorMessage = errorJson.message || errorMessage;
                    } catch(e) {}
                    throw new Error(errorMessage);
                }

                const result = await response.json();
                
                if (result.success) {
                    // Update Avatars Reactively (Immediate Feedback)
                    if (result.user.avatar) {
                        const newUrl = result.user.avatar + '?v=' + Date.now();
                        
                        // 1. Update Header Avatar
                        const headerContainer = document.getElementById('headerAvatarContainer');
                        if (headerContainer) {
                            headerContainer.innerHTML = `<img src="${newUrl}" id="headerAvatarImg" style="width:100%; height:100%; object-fit:cover;">`;
                        }
                        
                        // 2. Update Dropdown Avatar
                        const dropdownContainer = document.getElementById('dropdownAvatarContainer');
                        if (dropdownContainer) {
                            dropdownContainer.innerHTML = `<img src="${newUrl}" id="dropdownAvatarImg" style="width:100%; height:100%; object-fit:cover;">`;
                        }

                        // 3. Update Modal Preview too
                        const modalPreview = document.getElementById('profileImagePreview');
                        if (modalPreview) {
                            modalPreview.innerHTML = `<img src="${newUrl}" style="width:100%; height:100%; object-fit:cover;">`;
                        }
                    }

                    // Update User Info Labels
                    if (result.user.full_name) {
                        const headerName = document.getElementById('headerUserName');
                        if (headerName) headerName.innerText = result.user.full_name;
                        
                        const dropdownName = document.getElementById('dropdownUserName');
                        if (dropdownName) dropdownName.innerText = result.user.full_name;
                    }
                    
                    if (result.user.email) {
                        const dropdownEmail = document.getElementById('dropdownUserEmail');
                        if (dropdownEmail) dropdownEmail.innerText = result.user.email;
                    }

                    settingsModal.style.display = 'none';
                    Swal.fire({
                        icon: 'success', 
                        title: 'Profile Updated!', 
                        text: result.message,
                        timer: 2000, 
                        showConfirmButton: false
                    });
                } else {
                    // Handle validation errors or logic errors
                    let errorMsg = result.message || 'Please check your inputs.';
                    if (result.errors) {
                        errorMsg = Object.values(result.errors).flat().join('<br>');
                    }
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Update Failed', 
                        html: `<div style="font-size: 0.9rem; color: #dc2626;">${errorMsg}</div>`, 
                        confirmButtonColor: '#741b1b' 
                    });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'System Error', text: 'An unexpected error occurred. Please try again later.' });
            } finally {
                btn.disabled = false;
                btnText.innerText = 'Save Changes';
                btnIcon.style.display = 'none';
            }
        });

    });
    </script>
    
    @if(in_array(auth()->user()->role ?? '', ['admin', 'office']))
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let lastCheckTime = new Date().toISOString();
        const navNotifBadge = document.getElementById('navNotifBadge');
        const sidebarNotifBadge = document.querySelector('.nav-badge'); // Office sidebar badge
        
        async function checkNotifications() {
            try {
                const response = await fetch(`{{ route('api.notifications.pending') }}?last_check=${encodeURIComponent(lastCheckTime)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                
                // Update badges
                if (data.total_pending > 0) {
                    if (navNotifBadge) {
                        navNotifBadge.style.display = 'block';
                        navNotifBadge.innerText = data.total_pending > 99 ? '99+' : data.total_pending;
                    }
                    if (sidebarNotifBadge) {
                        sidebarNotifBadge.style.display = 'inline-block';
                        sidebarNotifBadge.innerText = data.total_pending > 99 ? '99+' : data.total_pending;
                    }
                } else {
                    if (navNotifBadge) navNotifBadge.style.display = 'none';
                    if (sidebarNotifBadge) sidebarNotifBadge.style.display = 'none';
                }
                
                // Show toast for new registrations
                if (data.new_registrations && data.new_registrations.length > 0) {
                    data.new_registrations.forEach(reg => {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'New Online Registration',
                            text: `${reg.full_name} (${reg.vehicle_type}) has submitted an application.`,
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                                toast.addEventListener('click', () => {
                                    window.location.href = "{{ auth()->user()->role === 'admin' ? route('admin.rfid') : route('office.registration') }}";
                                });
                            }
                        });
                    });
                }
                
                // Update last check time
                lastCheckTime = data.current_time;
                
            } catch (error) {
                console.error("Failed to check notifications:", error);
            }
        }
        
        // Initial check and then every 30 seconds
        checkNotifications();
        setInterval(checkNotifications, 30000);
    });
    </script>
    @endif
    
    @yield('scripts')
</body>
</html>
