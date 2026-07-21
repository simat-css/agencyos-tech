@extends('layouts.admin')

@section('title', 'User Profile')

@section('content')

@php
    $hasAudit = $user->creator || $user->updater;
@endphp


<div class="container-fluid">


{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

    <div>
        <h2 class="mb-1">
            <i class="fas fa-user-circle text-primary me-2"></i>
            User Profile
        </h2>

        <small class="text-muted">
            View complete user details and account information
        </small>
    </div>


    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        Back
    </a>

</div>




<div class="row g-4">



{{-- Left Profile Card --}}
<div class="col-xl-4 col-lg-5">


<div class="card shadow-sm border-0">


<div class="card-body text-center py-4">


@if($user->profile_photo)

<img src="{{ asset('storage/'.$user->profile_photo) }}"
class="rounded-circle shadow mb-3"
width="130"
height="130">

@else

<div class="rounded-circle bg-primary text-white mx-auto mb-3 d-flex align-items-center justify-content-center shadow"
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




@if($user->roles->count())

@foreach($user->roles as $role)

<span class="badge bg-primary px-3 py-2 me-1">
{{ $role->name }}
</span>

@endforeach

@endif



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




@if($hasAudit)

<div class="card shadow-sm border-0 mt-4">


<div class="card-header bg-white">

<h6>
<i class="fas fa-history text-primary me-2"></i>
Audit Information
</h6>

</div>


<div class="card-body">



@if($user->creator)

<p>
<strong>Created By:</strong>

{{ $user->creator->name }}

</p>

@endif



@if($user->updater)

<p>
<strong>Updated By:</strong>

{{ $user->updater->name }}

</p>

@endif



<p>

<strong>Created Date:</strong>

{{ $user->created_at->format('d M Y') }}

</p>



</div>

</div>

@endif



</div>







{{-- Right Information --}}
<div class="col-xl-8 col-lg-7">


<div class="card shadow-sm border-0">


<div class="card-header bg-white">

<h5>

<i class="fas fa-id-card text-primary me-2"></i>

Personal & Professional Information

</h5>


</div>




<div class="card-body">


<div class="row g-4">



<div class="col-md-6">

<label class="text-muted small">
Full Name
</label>

<h6>
{{ $user->name }}
</h6>

</div>




@if($user->employee_id)

<div class="col-md-6">

<label class="text-muted small">
Employee ID
</label>

<h6>
{{ $user->employee_id }}
</h6>

</div>

@endif





<div class="col-md-6">

<label class="text-muted small">
Email Address
</label>

<h6>
{{ $user->email }}
</h6>

</div>





@if($user->phone)

<div class="col-md-6">

<label class="text-muted small">
Phone Number
</label>

<h6>
{{ $user->phone }}
</h6>

</div>

@endif





@if($user->designation)

<div class="col-md-6">

<label class="text-muted small">
Designation
</label>

<h6>
{{ $user->designation }}
</h6>

</div>

@endif





@if($user->gender)

<div class="col-md-6">

<label class="text-muted small">
Gender
</label>

<h6>
{{ $user->gender }}
</h6>

</div>

@endif





@if($user->dob)

<div class="col-md-6">

<label class="text-muted small">
Date of Birth
</label>

<h6>
{{ \Carbon\Carbon::parse($user->dob)->format('d M Y') }}
</h6>

</div>

@endif





@if($user->joining_date)

<div class="col-md-6">

<label class="text-muted small">
Joining Date
</label>

<h6>
{{ \Carbon\Carbon::parse($user->joining_date)->format('d M Y') }}
</h6>

</div>

@endif





@if($user->emergency_contact)

<div class="col-md-6">

<label class="text-muted small">
Emergency Contact
</label>

<h6>
{{ $user->emergency_contact }}
</h6>

</div>

@endif





@if($user->address)

<div class="col-md-12">

<label class="text-muted small">
Address
</label>

<h6>
{{ $user->address }}
</h6>

</div>

@endif





@if($user->company)

<div class="col-md-6">

<label class="text-muted small">
Company
</label>

<h6>
{{ $user->company->name }}
</h6>

</div>

@endif





@if($user->department)

<div class="col-md-6">

<label class="text-muted small">
Department
</label>

<h6>
{{ $user->department->name }}
</h6>

</div>

@endif





@if($user->roles->count())

<div class="col-md-12">

<label class="text-muted small">
Assigned Roles
</label>


<div>

@foreach($user->roles as $role)

<span class="badge bg-primary me-1">
{{ $role->name }}
</span>

@endforeach

</div>


</div>

@endif



</div>


</div>


</div>






<div class="text-end mt-4">


<a href="{{ route('users.edit',$user->id) }}"
class="btn btn-warning px-4">


<i class="fas fa-edit me-1"></i>

Edit User


</a>


</div>



</div>



</div>



</div>


@endsection