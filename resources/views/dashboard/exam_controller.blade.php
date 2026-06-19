@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="page-header">
    <h1 class="page-title"><span class="page-icon"><i class="fa-solid fa-gauge"></i></span> Welcome, {{ $user->name }}</h1>
</div>
<div class="card-custom">
    <div class="card-body-c">
        <p class="text-muted mb-0">
            <i class="fa-solid fa-circle-info text-info me-2"></i>
            This is the <strong>{{ $role }}</strong> dashboard. Detailed widgets for this role are coming in an upcoming module build.
        </p>
    </div>
</div>
@endsection
