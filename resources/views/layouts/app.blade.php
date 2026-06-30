<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — KIPS College ERP</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    @stack('styles')

    <style>
    /* ══════════════════════════════════════════════════
       ROOT VARIABLES
    ══════════════════════════════════════════════════ */
    :root {
        --primary:    #1E3A5F;
        --primary-d:  #16304F;
        --secondary:  #2ECC71;
        --accent:     #F39C12;
        --danger:     #E74C3C;
        --success:    #27AE60;
        --info:       #3498DB;
        --dark:       #2C3E50;
        --light:      #F8F9FA;
        --sidebar-w:  260px;
        --navbar-h:   60px;
        --transition: 0.25s ease;
    }

    * { font-family: 'Poppins', sans-serif; }
    body { margin: 0; background: #f0f2f5; color: var(--dark); }

    /* ══════════════════════════════════════════════════
       SIDEBAR
    ══════════════════════════════════════════════════ */
    #sidebar {
        width: var(--sidebar-w);
        height: 100vh;
        background: var(--primary);
        position: fixed; top: 0; left: 0; z-index: 1000;
        display: flex; flex-direction: column;
        transition: width var(--transition);
        overflow: hidden;
        box-shadow: 4px 0 20px rgba(0,0,0,0.15);
    }

    #sidebar.collapsed { width: 68px; }

    /* Sidebar Brand */
    .sidebar-brand {
        padding: 0 16px;
        height: var(--navbar-h);
        display: flex; align-items: center; gap: 12px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        text-decoration: none; flex-shrink: 0;
    }

    .brand-icon {
        width: 36px; height: 36px; border-radius: 10px;
        background: rgba(255,255,255,0.15);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 16px; flex-shrink: 0;
    }

    .brand-text { color: #fff; font-weight: 700; font-size: 13.5px;
                  line-height: 1.3; white-space: nowrap;
                  transition: opacity var(--transition); }
    .brand-sub  { color: rgba(255,255,255,0.55); font-size: 10px;
                  font-weight: 400; display: block; }
    #sidebar.collapsed .brand-text { opacity: 0; width: 0; }

    /* Sidebar Nav */
    .sidebar-nav {
        flex: 1; overflow-y: auto; overflow-x: hidden;
        padding: 12px 0;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.1) transparent;
    }
    .sidebar-nav::-webkit-scrollbar { width: 4px; }
    .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

    /* Nav Section Label */
    .nav-section {
        padding: 16px 16px 6px;
        font-size: 10px; font-weight: 600;
        color: rgba(255,255,255,0.35);
        letter-spacing: 1.2px; text-transform: uppercase;
        white-space: nowrap;
        transition: opacity var(--transition);
    }
    #sidebar.collapsed .nav-section { opacity: 0; }

    /* Nav Items */
    .nav-item-wrapper { padding: 2px 8px; }

    .nav-link-custom {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 12px; border-radius: 10px;
        color: rgba(255,255,255,0.7); text-decoration: none;
        font-size: 13.5px; font-weight: 500;
        transition: all var(--transition);
        white-space: nowrap; position: relative;
        overflow: hidden;
    }

    .nav-link-custom:hover {
        background: rgba(255,255,255,0.12);
        color: #fff;
    }

    .nav-link-custom.active {
        background: rgba(255,255,255,0.18);
        color: #fff;
        border-left: 3px solid var(--accent);
    }

    .nav-link-custom .nav-icon {
        width: 20px; text-align: center;
        font-size: 15px; flex-shrink: 0;
    }

    .nav-link-custom .nav-label {
        transition: opacity var(--transition);
    }
    #sidebar.collapsed .nav-link-custom .nav-label { opacity: 0; width: 0; }

    /* Badge on nav item */
    .nav-badge {
        margin-left: auto;
        background: var(--accent); color: #fff;
        font-size: 10px; font-weight: 700;
        padding: 2px 7px; border-radius: 20px;
        transition: opacity var(--transition);
    }
    #sidebar.collapsed .nav-badge { opacity: 0; }

    /* Tooltip on collapsed */
    #sidebar.collapsed .nav-link-custom[data-bs-toggle="tooltip"] { position: relative; }

    /* Sidebar Footer */
    .sidebar-footer {
        padding: 12px 8px;
        border-top: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }

    .sidebar-user {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 10px;
        background: rgba(255,255,255,0.08);
        cursor: default;
    }

    .user-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: var(--accent);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 700; font-size: 13px;
        flex-shrink: 0;
        object-fit: cover;
    }

    .user-info { min-width: 0; transition: opacity var(--transition); }
    .user-info .u-name { color: #fff; font-size: 13px; font-weight: 600;
                          white-space: nowrap; overflow: hidden;
                          text-overflow: ellipsis; max-width: 140px; }
    .user-info .u-role { color: rgba(255,255,255,0.5); font-size: 11px; }
    #sidebar.collapsed .user-info { opacity: 0; width: 0; }

    /* ══════════════════════════════════════════════════
       MAIN CONTENT
    ══════════════════════════════════════════════════ */
    #main-wrapper {
        margin-left: var(--sidebar-w);
        transition: margin-left var(--transition);
        min-height: 100vh;
        display: flex; flex-direction: column;
    }
    #sidebar.collapsed ~ #main-wrapper { margin-left: 68px; }

    /* ══════════════════════════════════════════════════
       TOP NAVBAR
    ══════════════════════════════════════════════════ */
    #topNavbar {
        height: var(--navbar-h);
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        display: flex; align-items: center;
        padding: 0 24px; gap: 16px;
        position: sticky; top: 0; z-index: 999;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    /* Toggle sidebar button */
    .sidebar-toggle {
        background: none; border: none; cursor: pointer;
        color: var(--dark); font-size: 18px; padding: 6px 8px;
        border-radius: 8px; transition: background var(--transition);
    }
    .sidebar-toggle:hover { background: var(--light); }

    /* Breadcrumb */
    .top-breadcrumb {
        display: flex; align-items: center; gap: 6px;
        font-size: 13px; color: #6C757D;
    }
    .top-breadcrumb .bc-item { color: #6C757D; text-decoration: none; }
    .top-breadcrumb .bc-item:hover { color: var(--primary); }
    .top-breadcrumb .bc-sep { color: #dee2e6; }
    .top-breadcrumb .bc-current { color: var(--dark); font-weight: 600; }

    /* Global Search */
    .global-search-wrap {
        flex: 1; max-width: 380px; position: relative;
    }
    .global-search-wrap input {
        width: 100%; padding: 8px 16px 8px 38px;
        border: 2px solid #e9ecef; border-radius: 20px;
        font-size: 13px; background: var(--light);
        outline: none; transition: all 0.2s;
    }
    .global-search-wrap input:focus {
        border-color: var(--primary);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(30,58,95,0.08);
    }
    .global-search-wrap .search-icon {
        position: absolute; left: 12px; top: 50%;
        transform: translateY(-50%);
        color: #adb5bd; font-size: 13px;
    }
    .search-results-dropdown {
        position: absolute; top: calc(100% + 6px); left: 0; right: 0;
        background: #fff; border: 1px solid #e9ecef;
        border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        display: none; z-index: 9999; max-height: 320px; overflow-y: auto;
    }
    .search-results-dropdown.show { display: block; }
    .search-group-title {
        padding: 8px 14px 4px; font-size: 10px; font-weight: 700;
        color: #adb5bd; letter-spacing: 1px; text-transform: uppercase;
    }
    .search-result-item {
        padding: 8px 14px; cursor: pointer; font-size: 13px;
        display: flex; align-items: center; gap: 10px;
        transition: background 0.15s;
    }
    .search-result-item:hover { background: var(--light); }
    .search-result-item .s-icon { width: 28px; height: 28px; border-radius: 8px;
        background: rgba(30,58,95,0.1); display: flex; align-items: center;
        justify-content: center; color: var(--primary); font-size: 12px; }

    /* Navbar Right */
    .navbar-right { display: flex; align-items: center; gap: 8px; margin-left: auto; }

    /* Notification Bell */
    .notif-btn {
        position: relative; background: none; border: none;
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: var(--dark); font-size: 16px; cursor: pointer;
        transition: background var(--transition);
    }
    .notif-btn:hover { background: var(--light); }
    .notif-badge {
        position: absolute; top: 4px; right: 4px;
        width: 18px; height: 18px; border-radius: 50%;
        background: var(--danger); color: #fff;
        font-size: 10px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid #fff;
    }

    /* User Dropdown */
    .user-dropdown-btn {
        display: flex; align-items: center; gap: 8px;
        background: none; border: 1px solid #e9ecef;
        padding: 6px 12px 6px 6px; border-radius: 20px;
        cursor: pointer; transition: all var(--transition);
        color: var(--dark);
    }
    .user-dropdown-btn:hover { background: var(--light); border-color: #dee2e6; }
    .user-dropdown-btn .nav-avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: var(--primary); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700;
    }
    .user-dropdown-btn .nav-uname { font-size: 13px; font-weight: 600; }
    .user-dropdown-btn .nav-urole { font-size: 11px; color: #6C757D; display: block; }

    .user-dropdown-menu {
        min-width: 200px; border: 1px solid #e9ecef;
        border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        padding: 8px;
    }
    .user-dropdown-menu .dropdown-item {
        border-radius: 8px; font-size: 13px; padding: 8px 12px;
        display: flex; align-items: center; gap: 8px; color: var(--dark);
    }
    .user-dropdown-menu .dropdown-item:hover { background: var(--light); }
    .user-dropdown-menu .dropdown-item.text-danger:hover { background: #fff5f5; }
    .user-dropdown-menu .dropdown-divider { margin: 6px 0; }

    /* ══════════════════════════════════════════════════
       PAGE CONTENT
    ══════════════════════════════════════════════════ */
    #pageContent {
        padding: 24px; flex: 1;
    }

    /* Page Header */
    .page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
    }
    .page-title {
        font-size: 22px; font-weight: 700; color: var(--primary);
        margin: 0; display: flex; align-items: center; gap: 10px;
    }
    .page-title .page-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: rgba(30,58,95,0.1);
        display: flex; align-items: center; justify-content: center;
        color: var(--primary); font-size: 18px;
    }

    /* ══════════════════════════════════════════════════
       STAT CARDS
    ══════════════════════════════════════════════════ */
    .stat-card {
        background: #fff; border-radius: 16px;
        padding: 20px; border: 1px solid #f0f0f0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        transition: all var(--transition);
        position: relative; overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.10);
    }
    .stat-card .stat-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; margin-bottom: 14px;
    }
    .stat-card .stat-value {
        font-size: 28px; font-weight: 700; color: var(--dark);
        line-height: 1; margin-bottom: 4px;
    }
    .stat-card .stat-label {
        font-size: 12px; color: #6C757D;
        font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .stat-card .stat-trend {
        position: absolute; top: 16px; right: 16px;
        font-size: 12px; font-weight: 600; display: flex;
        align-items: center; gap: 4px;
    }
    .stat-card .stat-bg-icon {
        position: absolute; right: -10px; bottom: -10px;
        font-size: 70px; opacity: 0.05;
    }
    /* Stat Card Color Variants */
    .stat-primary .stat-icon { background: rgba(30,58,95,0.1); color: var(--primary); }
    .stat-danger  .stat-icon { background: rgba(231,76,60,0.1);  color: var(--danger); }
    .stat-success .stat-icon { background: rgba(39,174,96,0.1);  color: var(--success); }
    .stat-warning .stat-icon { background: rgba(243,156,18,0.1); color: var(--accent); }
    .stat-info    .stat-icon { background: rgba(52,152,219,0.1); color: var(--info); }

    /* ══════════════════════════════════════════════════
       CARDS
    ══════════════════════════════════════════════════ */
    .card-custom {
        background: #fff; border-radius: 16px;
        border: 1px solid #f0f0f0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .card-custom .card-header-c {
        padding: 16px 20px; border-bottom: 1px solid #f0f0f0;
        display: flex; align-items: center; justify-content: space-between;
        background: #fff;
    }
    .card-custom .card-title-c {
        font-size: 15px; font-weight: 600; color: var(--dark);
        margin: 0; display: flex; align-items: center; gap: 8px;
    }
    .card-custom .card-body-c { padding: 20px; }

    /* ══════════════════════════════════════════════════
       QUICK ACTIONS
    ══════════════════════════════════════════════════ */
    .quick-action-btn {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: 8px;
        padding: 20px 16px; border-radius: 14px;
        background: #fff; border: 2px solid #f0f0f0;
        cursor: pointer; transition: all var(--transition);
        text-decoration: none; color: var(--dark);
        font-size: 13px; font-weight: 600; text-align: center;
    }
    .quick-action-btn:hover {
        border-color: var(--primary);
        background: rgba(30,58,95,0.04);
        color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(30,58,95,0.12);
    }
    .quick-action-btn .qa-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }

    /* ══════════════════════════════════════════════════
       ACTIVITY FEED
    ══════════════════════════════════════════════════ */
    .activity-item {
        display: flex; align-items: flex-start; gap: 12px;
        padding: 12px 0; border-bottom: 1px solid #f5f5f5;
    }
    .activity-item:last-child { border-bottom: none; }
    .activity-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: rgba(30,58,95,0.1); color: var(--primary);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
    }
    .activity-text { font-size: 13px; color: var(--dark); margin-bottom: 2px; }
    .activity-time { font-size: 11px; color: #adb5bd; }

    /* ══════════════════════════════════════════════════
       ALERTS PANEL
    ══════════════════════════════════════════════════ */
    .alert-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px; border-radius: 10px; margin-bottom: 8px;
        font-size: 13px;
    }
    .alert-item.danger  { background: #fff5f5; color: #c0392b; border-left: 3px solid var(--danger); }
    .alert-item.warning { background: #fffbf0; color: #856404; border-left: 3px solid var(--accent); }
    .alert-item.info    { background: #f0f8ff; color: #1a5276; border-left: 3px solid var(--info); }

    /* ══════════════════════════════════════════════════
       NOTIFICATION DROPDOWN
    ══════════════════════════════════════════════════ */
    .notif-dropdown {
        width: 340px; border: 1px solid #e9ecef;
        border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        padding: 0; overflow: hidden;
    }
    .notif-header {
        padding: 14px 18px; border-bottom: 1px solid #f0f0f0;
        display: flex; align-items: center; justify-content: space-between;
        font-weight: 600; font-size: 14px;
    }
    .notif-item {
        padding: 12px 18px; border-bottom: 1px solid #f9f9f9;
        font-size: 13px; cursor: pointer; transition: background 0.15s;
    }
    .notif-item:hover { background: #f8f9fa; }
    .notif-item.unread { background: rgba(30,58,95,0.04); }
    .notif-footer { padding: 10px 18px; text-align: center; border-top: 1px solid #f0f0f0; }
    .notif-footer a { font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 500; }

    /* ══════════════════════════════════════════════════
       MOBILE
    ══════════════════════════════════════════════════ */
    #sidebarOverlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.4); z-index: 999;
    }

    @media (max-width: 991px) {
        #sidebar { transform: translateX(-100%); }
        #sidebar.mobile-open { transform: translateX(0); }
        #main-wrapper { margin-left: 0 !important; }
        #sidebarOverlay { display: none; }
        #sidebarOverlay.show { display: block; }
        .global-search-wrap { max-width: 200px; }
        #pageContent { padding: 16px; }
    }

    @media (max-width: 576px) {
        .global-search-wrap { display: none; }
        .user-dropdown-btn .nav-uname,
        .user-dropdown-btn .nav-urole { display: none; }
    }
    </style>
</head>
<body>

{{-- Sidebar Overlay (mobile) --}}
<div id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- ══════════════════════════════════════════════════
     SIDEBAR
══════════════════════════════════════════════════ --}}
<nav id="sidebar">

    {{-- Brand --}}
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="brand-icon">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="brand-text">
            KIPS COLLEGE
            <span class="brand-sub">ERP System</span>
        </div>
    </a>

    {{-- Navigation --}}
    <div class="sidebar-nav" id="sidebarNav">

        {{-- MAIN --}}
        <div class="nav-section">Main</div>

        <div class="nav-item-wrapper">
            <a href="{{ route('dashboard') }}"
               class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge nav-icon"></i>
                <span class="nav-label">Dashboard</span>
            </a>
        </div>

        {{-- STUDENTS — visible to Admin+ --}}
        @canany(['view students', 'manage students'])
        <div class="nav-section">Students</div>

        <div class="nav-item-wrapper">
            <a href="{{ route('students.create') }}" class="nav-link-custom {{ request()->routeIs('students.create') ? 'active' : '' }}">
                <i class="fa-solid fa-user-plus nav-icon"></i>
                <span class="nav-label">New Admission</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('students.index') }}" class="nav-link-custom {{ request()->routeIs('students.index') ? 'active' : '' }}">
                <i class="fa-solid fa-users nav-icon"></i>
                <span class="nav-label">All Students</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('students.index') }}" class="nav-link-custom">
                <i class="fa-solid fa-layer-group nav-icon"></i>
                <span class="nav-label">By Section</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('students.transfer') }}" class="nav-link-custom {{ request()->routeIs('students.transfer') ? 'active' : '' }}">
                <i class="fa-solid fa-arrow-right-arrow-left nav-icon"></i>
                <span class="nav-label">Move / Transfer</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('students.promote') }}" class="nav-link-custom {{ request()->routeIs('students.promote') ? 'active' : '' }}">
                <i class="fa-solid fa-level-up-alt nav-icon"></i>
                <span class="nav-label">Promote Year</span>
            </a>
        </div>
        @endcanany

        {{-- TEACHERS --}}
        @canany(['view teachers', 'manage teachers'])
        <div class="nav-section">Teachers</div>

        <div class="nav-item-wrapper">
            <a href="{{ route('teachers.index') }}" class="nav-link-custom {{ request()->routeIs('teachers.index') ? 'active' : '' }}">
                <i class="fa-solid fa-chalkboard-teacher nav-icon"></i>
                <span class="nav-label">All Teachers</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('teachers.create') }}" class="nav-link-custom {{ request()->routeIs('teachers.create') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days nav-icon"></i>
                <span class="nav-label">Add Teacher</span>
            </a>
        </div>
        @endcanany

        {{-- ATTENDANCE --}}
        <div class="nav-section">Attendance</div>

        @canany(['mark attendance'])
        <div class="nav-item-wrapper">
            <a href="{{ route('attendance.mark') }}" class="nav-link-custom {{ request()->routeIs('attendance.mark') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-check nav-icon"></i>
                <span class="nav-label">Mark Attendance</span>
            </a>
        </div>
        @endcanany

        @canany(['view attendance'])
        <div class="nav-item-wrapper">
            <a href="{{ route('attendance.reports') }}" class="nav-link-custom {{ request()->routeIs('attendance.reports') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line nav-icon"></i>
                <span class="nav-label">Att. Reports</span>
            </a>
        </div>
        @endcanany

        @canany(['manage holidays'])
        <div class="nav-item-wrapper">
            <a href="{{ route('attendance.holidays') }}" class="nav-link-custom {{ request()->routeIs('attendance.holidays') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-xmark nav-icon"></i>
                <span class="nav-label">Holidays</span>
            </a>
        </div>
        @endcanany

        {{-- EXAMS --}}
        <div class="nav-section">Examinations</div>

        <div class="nav-item-wrapper">
            <a href="{{ route('exams.index') }}" class="nav-link-custom {{ request()->routeIs('exams.index') ? 'active' : '' }}">
                <i class="fa-solid fa-file-alt nav-icon"></i>
                <span class="nav-label">All Exams</span>
            </a>
        </div>

        @canany(['create exam'])
        <div class="nav-item-wrapper">
            <a href="{{ route('exams.create') }}" class="nav-link-custom {{ request()->routeIs('exams.create') ? 'active' : '' }}">
                <i class="fa-solid fa-file-circle-plus nav-icon"></i>
                <span class="nav-label">Create Exam</span>
            </a>
        </div>
        @endcanany

        @canany(['enter marks'])
        <div class="nav-item-wrapper">
            <a href="{{ route('exams.marks-entry.index') }}" class="nav-link-custom {{ request()->routeIs('exams.marks-entry.*') ? 'active' : '' }}">
                <i class="fa-solid fa-pen-to-square nav-icon"></i>
                <span class="nav-label">Marks Entry</span>
            </a>
        </div>
        @endcanany

        <div class="nav-item-wrapper">
            <a href="{{ route('exams.grading.index') }}" class="nav-link-custom {{ request()->routeIs('exams.grading.*') ? 'active' : '' }}">
                <i class="fa-solid fa-star-half-stroke nav-icon"></i>
                <span class="nav-label">Grading</span>
            </a>
        </div>

        {{-- RESULTS --}}
        <div class="nav-section">Results</div>

        <div class="nav-item-wrapper">
            <a href="{{ route('exams.results.index') }}" class="nav-link-custom {{ request()->routeIs('exams.results.index') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-bar nav-icon"></i>
                <span class="nav-label">Progress Reports</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('exams.roll-slips.index') }}" class="nav-link-custom {{ request()->routeIs('exams.roll-slips.index') ? 'active' : '' }}">
                <i class="fa-solid fa-id-card nav-icon"></i>
                <span class="nav-label">Roll Slips</span>
            </a>
        </div>

        {{-- FEES --}}
        @canany(['manage fees'])
        <div class="nav-section">Fees</div>

        <div class="nav-item-wrapper">
            <a href="{{ route('fees.structure') }}" class="nav-link-custom {{ request()->routeIs('fees.structure') ? 'active' : '' }}">
                <i class="fa-solid fa-money-bill-wave nav-icon"></i>
                <span class="nav-label">Fee Management</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('fees.reports') }}" class="nav-link-custom {{ request()->routeIs('fees.reports') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar nav-icon"></i>
                <span class="nav-label">Fee Reports</span>
            </a>
        </div>
        @endcanany

        {{-- COLLEGE --}}
        <div class="nav-section">College</div>

        <div class="nav-item-wrapper">
            <a href="{{ route('notices.index') }}" class="nav-link-custom {{ request()->routeIs('notices.index') ? 'active' : '' }}">
                <i class="fa-solid fa-bullhorn nav-icon"></i>
                <span class="nav-label">Notice Board</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('calendar.index') }}" class="nav-link-custom {{ request()->routeIs('calendar.index') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-alt nav-icon"></i>
                <span class="nav-label">Academic Calendar</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('timetable.index') }}" class="nav-link-custom {{ request()->routeIs('timetable.index') ? 'active' : '' }}">
                <i class="fa-solid fa-table-cells nav-icon"></i>
                <span class="nav-label">Timetable</span>
            </a>
        </div>

        {{-- SYSTEM --}}
        @canany(['manage settings'])
        <div class="nav-section">System</div>

        <div class="nav-item-wrapper">
            <a href="{{ route('settings.users.page') }}" class="nav-link-custom {{ request()->routeIs('settings.users.page') ? 'active' : '' }}">
                <i class="fa-solid fa-users-gear nav-icon"></i>
                <span class="nav-label">User Management</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('settings.index') }}" class="nav-link-custom {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                <i class="fa-solid fa-cog nav-icon"></i>
                <span class="nav-label">Settings</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('audit-trail.index') }}" class="nav-link-custom {{ request()->routeIs('audit-trail.index') ? 'active' : '' }}">
                <i class="fa-solid fa-clock-rotate-left nav-icon"></i>
                <span class="nav-label">Audit Trail</span>
            </a>
        </div>

        <div class="nav-item-wrapper">
            <a href="{{ route('backup.index') }}" class="nav-link-custom {{ request()->routeIs('backup.index') ? 'active' : '' }}">
                <i class="fa-solid fa-database nav-icon"></i>
                <span class="nav-label">Backup & Restore</span>
            </a>
        </div>
        @endcanany

    </div>{{-- /sidebar-nav --}}

    {{-- Sidebar Footer / User --}}
    <div class="sidebar-footer">
        <div class="sidebar-user">
            @if(Auth::user()->photo)
                <img src="{{ Auth::user()->photo_url }}" class="user-avatar">
            @else
                <div class="user-avatar">{{ Auth::user()->initials }}</div>
            @endif
            <div class="user-info">
                <div class="u-name">{{ Auth::user()->name }}</div>
                <div class="u-role">{{ Auth::user()->primaryRole() }}</div>
            </div>
        </div>
    </div>

</nav>

{{-- ══════════════════════════════════════════════════
     MAIN WRAPPER
══════════════════════════════════════════════════ --}}
<div id="main-wrapper">

    {{-- TOP NAVBAR --}}
    <header id="topNavbar">

        {{-- Toggle --}}
        <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>

        {{-- Breadcrumb --}}
        <div class="top-breadcrumb d-none d-md-flex">
            <a href="{{ route('dashboard') }}" class="bc-item">
                <i class="fa-solid fa-house fa-xs"></i>
            </a>
            @hasSection('breadcrumb')
                <span class="bc-sep"><i class="fa-solid fa-chevron-right fa-xs"></i></span>
                @yield('breadcrumb')
            @endif
        </div>

        {{-- Global Search --}}
        <div class="global-search-wrap">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="globalSearch" placeholder="Search students, teachers…"
                   autocomplete="off">
            <div class="search-results-dropdown" id="searchDropdown"></div>
        </div>

        {{-- Right Side --}}
        <div class="navbar-right">

            {{-- Notifications --}}
            <div class="dropdown">
                <button class="notif-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        title="Notifications" id="notifBellBtn">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notif-badge d-none" id="notifBadge">0</span>
                </button>
                <div class="dropdown-menu notif-dropdown p-0" id="notifDropdown">
                    <div class="notif-header">
                        <span><i class="fa-solid fa-bell me-2 text-primary"></i>Notifications</span>
                        <button class="btn btn-link btn-sm p-0 text-muted" onclick="markAllRead()" style="font-size:12px">
                            Mark all read
                        </button>
                    </div>
                    <div id="notifList">
                        <div class="text-center py-4 text-muted" style="font-size:13px">
                            <i class="fa-solid fa-bell-slash fa-2x mb-2 d-block"></i>
                            No notifications
                        </div>
                    </div>
                    <div class="notif-footer">
                        @can('manage notices')
                        <a href="{{ route('notifications.index') }}">View All Notifications</a>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- User Dropdown --}}
            <div class="dropdown">
                <button class="user-dropdown-btn" data-bs-toggle="dropdown">
                    @if(Auth::user()->photo)
                        <img src="{{ Auth::user()->photo_url }}" class="nav-avatar">
                    @else
                        <div class="nav-avatar">{{ Auth::user()->initials }}</div>
                    @endif
                    <div class="text-start">
                        <span class="nav-uname">{{ Auth::user()->name }}</span>
                        <span class="nav-urole">{{ Auth::user()->primaryRole() }}</span>
                    </div>
                    <i class="fa-solid fa-chevron-down ms-1" style="font-size:11px"></i>
                </button>
                <ul class="dropdown-menu user-dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="fa-solid fa-user-circle text-primary"></i> My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.show') }}#passwordForm">
                            <i class="fa-solid fa-lock text-warning"></i> Change Password
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" id="logoutForm">
                            @csrf
                            <a class="dropdown-item text-danger" href="#"
                               onclick="confirmLogout(event)">
                                <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                            </a>
                        </form>
                    </li>
                </ul>
            </div>

        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main id="pageContent">
        @yield('content')
    </main>

</div>{{-- /main-wrapper --}}

{{-- ══════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
// ── CSRF for all AJAX ─────────────────────────────────────────────
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// ── Toastr Config ────────────────────────────────────────────────
toastr.options = {
    closeButton: true, progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 3500,
};

// ── Sidebar Toggle ───────────────────────────────────────────────
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const isMobile = window.innerWidth < 992;

    if (isMobile) {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('show');
    } else {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('mobile-open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

// Restore sidebar state
(function() {
    if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth >= 992) {
        document.getElementById('sidebar').classList.add('collapsed');
    }
})();

// ── Logout Confirmation ──────────────────────────────────────────
function confirmLogout(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Sign Out?',
        text: 'Are you sure you want to sign out of the system?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#E74C3C',
        cancelButtonColor: '#6C757D',
        confirmButtonText: '<i class="fa-solid fa-right-from-bracket me-1"></i> Yes, Sign Out',
        cancelButtonText:  '<i class="fa-solid fa-xmark me-1"></i> Cancel',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('logoutForm').submit();
        }
    });
}

// ── Global Search ────────────────────────────────────────────────
let searchTimeout;
const searchInput    = document.getElementById('globalSearch');
const searchDropdown = document.getElementById('searchDropdown');

searchInput.addEventListener('input', function () {
    clearTimeout(searchTimeout);
    const q = this.value.trim();
    if (q.length < 2) { searchDropdown.classList.remove('show'); return; }

    searchTimeout = setTimeout(async () => {
        try {
            // Placeholder: replace with actual search endpoint
            const res = await fetch(`/api/search?q=${encodeURIComponent(q)}`, {
                headers: { 'Accept': 'application/json',
                           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            // Render results …
        } catch(e) {}
    }, 300);
});

document.addEventListener('click', (e) => {
    if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
        searchDropdown.classList.remove('show');
    }
});

// ── Real Notifications (Module 12) ───────────────────────────────
function loadNotifications() {
    $.get('{{ route("notifications.bell") }}', function (res) {
        const badge = $('#notifBadge');
        if (res.unread_count > 0) {
            badge.removeClass('d-none').text(res.unread_count > 9 ? '9+' : res.unread_count);
        } else {
            badge.addClass('d-none');
        }

        if (!res.data.length) {
            $('#notifList').html('<div class="text-center py-4 text-muted" style="font-size:13px"><i class="fa-solid fa-bell-slash fa-2x mb-2 d-block"></i>No notifications</div>');
            return;
        }

        let html = '';
        res.data.forEach(n => {
            html += `<div class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}">
                <b>${n.title}</b><br>
                <span class="text-muted">${n.message}</span><br>
                <span class="text-muted" style="font-size:11px">${n.time_ago}</span>
            </div>`;
        });
        $('#notifList').html(html);
    });
}

$('#notifBellBtn').on('click', loadNotifications);
$(document).on('click', '.notif-item.unread', function () {
    const id = $(this).data('id');
    $.post(`/notifications/${id}/read`, { _token: $('meta[name="csrf-token"]').attr('content') });
    $(this).removeClass('unread');
});

function markAllRead() {
    $.post('{{ route("notifications.read-all") }}', { _token: $('meta[name="csrf-token"]').attr('content') })
        .done(() => loadNotifications());
}

// Poll every 60s for new notifications
loadNotifications();
setInterval(loadNotifications, 60000);

// ── Session Timeout Warning ──────────────────────────────────────
(function () {
    const TIMEOUT = 30 * 60 * 1000; // 30 minutes
    const WARNING = 5  * 60 * 1000; // warn at 25 min
    let warningShown = false;

    function resetTimer() { warningShown = false; }
    ['mousemove','keydown','click','scroll'].forEach(e => document.addEventListener(e, resetTimer));

    setInterval(() => {
        // Simplified — real implementation uses server-side session check
    }, 60000);
})();
</script>

@stack('scripts')
</body>
</html>
