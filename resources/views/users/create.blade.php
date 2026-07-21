@extends('layouts.admin')

@section('title', 'Create User')

@section('content')

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

    <div>
        <h2 class="mb-1">
            <i class="fas fa-user-plus text-primary me-2"></i>
            Create User
        </h2>

        <small class="text-muted">
            Add a new user to your company
        </small>
    </div>


    <a href="{{ route('users.index') }}"
       class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        Back
    </a>

</div>


<div class="row justify-content-center">

<div class="col-xl-9 col-lg-10 col-md-12">

<div class="card shadow-sm border-0">


<div class="card-header bg-white">

<h5 class="mb-0">
<i class="fas fa-user-circle text-primary me-2"></i>
User Information
</h5>

</div>


<div class="card-body">


<form action="{{ route('users.store') }}"
      method="POST"
      enctype="multipart/form-data">

@csrf


<div class="row g-3">


{{-- Name --}}
<div class="col-md-6">

<label class="form-label">
Name <span class="text-danger">*</span>
</label>

<input type="text"
name="name"
class="form-control"
placeholder="Enter full name"
value="{{ old('name') }}">

@error('name')
<small class="text-danger">{{ $message }}</small>
@enderror

</div>



{{-- Email --}}
<div class="col-md-6">

<label class="form-label">
Email <span class="text-danger">*</span>
</label>

<input type="email"
name="email"
class="form-control"
autocomplete="off"
placeholder="Enter email address"
value="{{ old('email') }}">

@error('email')
<small class="text-danger">{{ $message }}</small>
@enderror

</div>




{{-- Password --}}
<div class="col-md-6">

<label class="form-label">
Password <span class="text-danger">*</span>
</label>

<input type="password"
name="password"
class="form-control">

@error('password')
<small class="text-danger">{{ $message }}</small>
@enderror

</div>



{{-- Employee Information Header --}}
<div class="col-12 mt-4">

<h6 class="text-primary border-bottom pb-2">

<i class="fas fa-id-card me-1"></i>

Employee Information

</h6>

</div>



{{-- Designation --}}
<div class="col-md-6">

<label class="form-label">
Designation
</label>

<input type="text"
name="designation"
class="form-control"
placeholder="Enter designation"
value="{{ old('designation') }}">

@error('designation')
<small class="text-danger">{{ $message }}</small>
@enderror

</div>




{{-- Phone --}}
<div class="col-md-6">

<label class="form-label">
Phone
</label>

<input type="text"
name="phone"
class="form-control"
placeholder="Enter phone number"
value="{{ old('phone') }}">

@error('phone')
<small class="text-danger">{{ $message }}</small>
@enderror

</div>





{{-- Gender --}}
<div class="col-md-6">

<label class="form-label">
Gender
</label>

<select name="gender"
class="form-select">

<option value="">
Select Gender
</option>

<option value="Male">
Male
</option>

<option value="Female">
Female
</option>

<option value="Other">
Other
</option>

</select>

</div>





{{-- DOB --}}
<div class="col-md-6">

<label class="form-label">
Date of Birth
</label>

<input type="date"
name="dob"
class="form-control"
value="{{ old('dob') }}">

</div>




{{-- Joining Date --}}
<div class="col-md-6">

<label class="form-label">
Joining Date
</label>

<input type="date"
name="joining_date"
class="form-control"
value="{{ old('joining_date') }}">

</div>




{{-- Emergency Contact --}}
<div class="col-md-6">

<label class="form-label">
Emergency Contact
</label>

<input type="text"
name="emergency_contact"
class="form-control"
placeholder="Emergency contact number">

</div>





{{-- Company --}}
<div class="col-md-6">

<label class="form-label">
Company <span class="text-danger">*</span>
</label>


<select name="company_id"
id="company_id"
class="form-select">

<option value="">
Select Company
</option>


@foreach($companies as $company)

<option value="{{ $company->id }}"
{{ old('company_id') == $company->id ? 'selected' : '' }}>

{{ $company->name }}

</option>

@endforeach


</select>


@error('company_id')
<small class="text-danger">{{ $message }}</small>
@enderror


</div>





{{-- Department --}}
<div class="col-md-6">


<label class="form-label">
Department
</label>


<select id="department_id"
name="department_id"
class="form-select">

<option value="">
Select Department
</option>

</select>


@error('department_id')
<small class="text-danger">{{ $message }}</small>
@enderror


</div>





{{-- Role --}}
<div class="col-md-6">


<label class="form-label">
Role <span class="text-danger">*</span>
</label>


<select name="role"
class="form-select">


<option value="">
Select Role
</option>


@foreach($roles as $role)

<option value="{{ $role->name }}"
{{ old('role') == $role->name ? 'selected' : '' }}>

{{ $role->name }}

</option>

@endforeach


</select>


</div>






{{-- Status --}}
<div class="col-md-6">


<label class="form-label">
Status
</label>


<select name="status"
class="form-select">


<option value="1">
Active
</option>


<option value="0">
Inactive
</option>


</select>


</div>





{{-- Address --}}
<div class="col-12">

<label class="form-label">
Address
</label>


<textarea name="address"
class="form-control"
rows="3"
placeholder="Enter address">{{ old('address') }}</textarea>


</div>





{{-- Profile --}}
<div class="col-md-6">

<label class="form-label">
Profile Photo
</label>


<input type="file"
name="profile_photo"
class="form-control">


@error('profile_photo')
<small class="text-danger">{{ $message }}</small>
@enderror


</div>




</div>



<hr class="my-4">



<div class="text-end">

<button type="submit"
class="btn btn-primary px-4">

<i class="fas fa-save me-1"></i>

Save User

</button>

</div>



</form>


</div>

</div>


</div>

</div>


</div>


@endsection



@push('scripts')

<script>

document.getElementById('company_id')
.addEventListener('change', function(){

const departmentUrl = "{{ url('users/companies') }}";

let companyId = this.value;

let departmentDropdown =
document.getElementById('department_id');


departmentDropdown.innerHTML =
'<option>Loading...</option>';



if(!companyId){

departmentDropdown.innerHTML =
'<option value="">Select Department</option>';

return;

}



fetch(`${departmentUrl}/${companyId}/departments`)

.then(response => response.json())

.then(data => {


let options =
'<option value="">Select Department</option>';


data.forEach(function(department){


options += `

<option value="${department.id}">
${department.name}
</option>

`;


});


departmentDropdown.innerHTML = options;


})


.catch(error => {


console.error(error);


departmentDropdown.innerHTML =
'<option value="">No Department Found</option>';


});


});


</script>


@endpush