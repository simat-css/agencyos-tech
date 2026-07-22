@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')

@php
$user = auth()->user();
@endphp


<div class="container-fluid">


{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

    <div>
        <h2 class="mb-1">
            <i class="fas fa-user-circle text-primary me-2"></i>
            My Profile
        </h2>

        <small class="text-muted">
            View and manage your personal information and account details
        </small>
    </div>


    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
        <i class="fas fa-edit me-1"></i>
        Edit Profile
    </a>

</div>



<div class="row">


{{-- Profile Card --}}
<div class="col-lg-4">


<div class="card shadow-sm border-0">

<div class="card-body text-center">


@if($user->profile_photo)

<img src="{{ asset('storage/'.$user->profile_photo) }}"
class="rounded-circle shadow mb-3"
width="130"
height="130">


@else

<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3 shadow"
style="width:130px;height:130px;font-size:45px;">

{{ strtoupper(substr($user->name,0,1)) }}

</div>

@endif



<h4>
{{ $user->name }}
</h4>


<p class="text-muted">
{{ $user->email }}
</p>



@if($user->designation)

<span class="badge bg-info mb-3">
{{ $user->designation }}
</span>

@endif



<div>

@foreach($user->roles as $role)

<span class="badge bg-primary me-1">
{{ $role->name }}
</span>

@endforeach

</div>


<hr>


<div class="text-start">


@if($user->company)

<p>
<i class="fas fa-building text-primary me-2"></i>

<strong>Company:</strong>

{{ $user->company->name }}

</p>

@endif



@if($user->department)

<p>
<i class="fas fa-sitemap text-primary me-2"></i>

<strong>Department:</strong>

{{ $user->department->name }}

</p>

@endif



<p>

<strong>Status:</strong>


@if($user->status)

<span class="badge bg-success">
Active
</span>

@else

<span class="badge bg-danger">
Inactive
</span>

@endif


</p>


</div>


</div>

</div>


</div>





{{-- Right Section --}}
<div class="col-lg-8">



<div class="card shadow-sm border-0 mb-4">


<div class="card-header bg-white">

<h5>
<i class="fas fa-id-card text-primary me-2"></i>

Personal Information

</h5>

</div>



<div class="card-body">


<div class="row">



<div class="col-md-6 mb-3">

<label class="text-muted">
Full Name
</label>

<h6>
{{ $user->name }}
</h6>

</div>




<div class="col-md-6 mb-3">

<label class="text-muted">
Email Address
</label>

<h6>
{{ $user->email }}
</h6>

</div>



@if($user->employee_id)

<div class="col-md-6 mb-3">

<label class="text-muted">
Employee ID
</label>

<h6>
{{ $user->employee_id }}
</h6>

</div>

@endif




@if($user->phone)

<div class="col-md-6 mb-3">

<label class="text-muted">
Phone
</label>

<h6>
{{ $user->phone }}
</h6>

</div>

@endif




@if($user->designation)

<div class="col-md-6 mb-3">

<label class="text-muted">
Designation
</label>

<h6>
{{ $user->designation }}
</h6>

</div>

@endif




@if($user->gender)

<div class="col-md-6 mb-3">

<label class="text-muted">
Gender
</label>

<h6>
{{ $user->gender }}
</h6>

</div>

@endif




@if($user->dob)

<div class="col-md-6 mb-3">

<label class="text-muted">
Date of Birth
</label>

<h6>
{{ \Carbon\Carbon::parse($user->dob)->format('d M Y') }}
</h6>

</div>

@endif




@if($user->joining_date)

<div class="col-md-6 mb-3">

<label class="text-muted">
Joining Date
</label>

<h6>
{{ \Carbon\Carbon::parse($user->joining_date)->format('d M Y') }}
</h6>

</div>

@endif




@if($user->emergency_contact)

<div class="col-md-6 mb-3">

<label class="text-muted">
Emergency Contact
</label>

<h6>
{{ $user->emergency_contact }}
</h6>

</div>

@endif




@if($user->address)

<div class="col-md-12 mb-3">

<label class="text-muted">
Address
</label>

<h6>
{{ $user->address }}
</h6>

</div>

@endif



</div>


</div>


</div>





{{-- Organization Details --}}

@if($user->company || $user->department)

<div class="card shadow-sm border-0 mb-4">


<div class="card-header bg-white">

<h5>

<i class="fas fa-sitemap text-primary me-2"></i>

Organization Details

</h5>


</div>



<div class="card-body">


<div class="row">


@if($user->company)

<div class="col-md-6 mb-3">

<label class="text-muted">
Company
</label>

<h6>
{{ $user->company->name }}
</h6>

</div>

@endif



@if($user->department)

<div class="col-md-6 mb-3">

<label class="text-muted">
Department
</label>

<h6>
{{ $user->department->name }}
</h6>

</div>

@endif



<div class="col-md-6 mb-3">

<label class="text-muted">
Account Created
</label>

<h6>
{{ $user->created_at->format('d M Y') }}
</h6>

</div>



<div class="col-md-6 mb-3">

<label class="text-muted">
Last Updated
</label>

<h6>
{{ $user->updated_at->format('d M Y') }}
</h6>

</div>


</div>


</div>


</div>

@endif


{{-- Security Information --}}
<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">
            <i class="fas fa-shield-alt text-primary me-2"></i>
            Security Information
        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            {{-- Last Login --}}
            <div class="col-md-6 mb-3">

                <label class="text-muted">
                    Last Login
                </label>

                <h6>
                    {{ $lastSession?->login_at
                        ? \Carbon\Carbon::parse($lastSession->login_at)->format('d M Y h:i A')
                        : 'Never Logged In'
                    }}
                </h6>

            </div>

            {{-- Current Browser --}}
            <div class="col-md-6 mb-3">

                <label class="text-muted">
                    Current Browser
                </label>

                <h6>
                    {{ $lastSession?->browser ?? 'Unknown' }}
                </h6>

            </div>

            {{-- Current Device --}}
            <div class="col-md-6 mb-3">

                <label class="text-muted">
                    Current Device
                </label>

                <h6>
                    {{ $lastSession?->platform ?? 'Unknown' }}
                </h6>

            </div>

            {{-- IP Address --}}
            <div class="col-md-6 mb-3">

                <label class="text-muted">
                    IP Address
                </label>

                <h6>
                    {{ $lastSession?->ip_address ?? 'Unknown' }}
                </h6>

            </div>

            {{-- Session Status --}}
            <div class="col-md-6 mb-3">

                <label class="text-muted">
                    Session Status
                </label>

                <h6>

                    @if($lastSession?->is_active)

                        <span class="badge bg-success">
                            Active Session
                        </span>

                    @else

                        <span class="badge bg-secondary">
                            Logged Out
                        </span>

                    @endif

                </h6>

            </div>

            {{-- Login Time --}}
            <div class="col-md-6 mb-3">

                <label class="text-muted">
                    Login Time
                </label>

                <h6>
                    {{ $lastSession?->login_at
                        ? \Carbon\Carbon::parse($lastSession->login_at)->diffForHumans()
                        : '-'
                    }}
                </h6>

            </div>

        </div>

    </div>

</div>

{{-- Quick Stats --}}

<div class="row">


<div class="col-md-4">

<div class="card shadow-sm border-0 text-center">

<div class="card-body">

<i class="fas fa-history fa-2x text-primary mb-2"></i>

<h5>
{{ $user->created_at->format('d M Y') }}
</h5>

<small>
Member Since
</small>

</div>

</div>

</div>




<div class="col-md-4">

<div class="card shadow-sm border-0 text-center">

<div class="card-body">

<i class="fas fa-shield-alt fa-2x text-success mb-2"></i>

<h5>
{{ $user->roles->count() }}
</h5>

<small>
Assigned Roles
</small>

</div>

</div>

</div>





<div class="col-md-4">

<div class="card shadow-sm border-0 text-center">

<div class="card-body">

<i class="fas fa-user-check fa-2x text-warning mb-2"></i>

<h5>
{{ $user->status ? 'Active':'Inactive' }}
</h5>

<small>
Account Status
</small>

</div>

</div>

</div>


</div>



</div>


</div>


</div>


@endsection