@extends('layouts.admin')

@section('title', 'Create User')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
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

                                <label for="name" class="form-label">
    Name <span class="text-danger">*</span>
</label>

<input type="text"
       id="name"
       name="name"
       class="form-control"
       placeholder="Enter full name"
       value="{{ old('name') }}">
                                @error('name')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>




                            {{-- Email --}}
                            <div class="col-md-6">

                                <label for="email" class="form-label">
    Email <span class="text-danger">*</span>
</label>

<input type="email"
       id="email"
       name="email"
       class="form-control"
       autocomplete="off"
       placeholder="Enter email address">


                                @error('email')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>




                            {{-- Password --}}
                            <div class="col-md-6">

                                <label for="password" class="form-label">
    Password <span class="text-danger">*</span>
</label>

<input type="password"
       id="password"
       name="password"
       class="form-control">
       @error('password')
    <small class="text-danger">
        {{ $message }}
    </small>
@enderror


                            </div>




                            {{-- Company --}}
                            <div class="col-md-6">

                                <label for="company_id" class="form-label">
    Company <span class="text-danger">*</span>
</label>


                                <select name="company_id"
        id="company_id"
        class="form-select @error('company_id') is-invalid @enderror">

    <option value="">
        Select Company
    </option>

    @foreach($companies as $company)

        <option value="{{ $company->id }}">

            {{ $company->name }}

        </option>

    @endforeach

                                </select>
                                 @error('company_id')
    <small class="text-danger">
        {{ $message }}
    </small>
@enderror


                            </div>




                            {{-- Department --}}
                            <div class="col-md-6">


                            <label for="department_id" class="form-label">
    Department <span class="text-danger"></span>
</label>

<select 
    id="department_id"
    name="department_id"
    class="form-select @error('department_id') is-invalid @enderror">

    <option value="">
        Select Department
    </option>

</select>


@error('department_id')
    <small class="text-danger">
        {{ $message }}
    </small>
@enderror


                            </div>




                            {{-- Role --}}
                            <div class="col-md-6">


                               <label for="role" class="form-label">
    Role <span class="text-danger">*</span>
</label>


<select name="role"
        id="role"
        class="form-select">


                                    <option value="">
                                        Select Role
                                    </option>


                                    @foreach($roles as $role)

                                    <option value="{{ $role->name }}">

                                        {{ $role->name }}

                                    </option>

                                    @endforeach


                                </select>
                                @error('role')
    <small class="text-danger">
        {{ $message }}
    </small>
@enderror


                            </div>




                            {{-- Status --}}
                            <div class="col-md-6">


                                <label for="status" class="form-label">
    Status
</label>

<select name="status"
        id="status"
        class="form-select">


                                    <option value="1">
                                        Active
                                    </option>


                                    <option value="0">
                                        Inactive
                                    </option>


                                </select>


                            </div>




                            {{-- Profile --}}
                            <div class="col-md-6">


                                <label for="profile_photo" class="form-label">
    Profile Photo
</label>


<input type="file"
       id="profile_photo"
       name="profile_photo"
       class="form-control">
       @error('profile_photo')
    <small class="text-danger">
        {{ $message }}
    </small>
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
const departmentUrl =
    "{{ url('users/companies') }}";
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