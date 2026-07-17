@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')

<div class="container-fluid">


    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>
            <h2 class="mb-1">
                <i class="fas fa-user-edit text-primary me-2"></i>
                Edit User
            </h2>

            <small class="text-muted">
                Update user information and access settings
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


                    <form action="{{ route('users.update',$user->id) }}"
                          method="POST"
                          enctype="multipart/form-data">


                        @csrf
                        @method('PUT')



                        <div class="row g-3">



                            {{-- Name --}}
                            <div class="col-md-6">

                                <label class="form-label">
                                    Name <span class="text-danger">*</span>
                                </label>


                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       value="{{ old('name',$user->name) }}">


                            </div>




                            {{-- Email --}}
                            <div class="col-md-6">


                                <label class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>


                                <input type="email"
                                       name="email"
                                       class="form-control"
                                       value="{{ old('email',$user->email) }}">


                            </div>





                            {{-- Password --}}
                            <div class="col-md-6">


                                <label class="form-label">

                                    Password

                                    <small class="text-muted">
                                        (Leave empty to keep old password)
                                    </small>

                                </label>


                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       placeholder="Enter new password">


                            </div>





                            {{-- Company --}}
                           @if(auth()->user()->hasRole('Company Admin'))

<div class="col-md-6">

    <label class="form-label">
        Company
    </label>

    <input type="text"
           class="form-control"
           value="{{ $user->company->name ?? '' }}"
           readonly>

    <input type="hidden"
           name="company_id"
           value="{{ $user->company_id }}">

</div>

@else

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
                {{ $user->company_id == $company->id ? 'selected' : '' }}>

                {{ $company->name }}

            </option>

        @endforeach

    </select>

</div>

@endif






                            {{-- Department --}}
                            <div class="col-md-6">


                                <label class="form-label">
                                    Department
                                </label>



                                <select name="department_id"
                                         id="department_id"
                                        class="form-select">



                                    <option value="">
                                        Select Department
                                    </option>




                                    @foreach($departments as $department)


                                    <option value="{{ $department->id }}"
                                    {{ $user->department_id == $department->id ? 'selected':'' }}>


                                        {{ $department->name }}


                                    </option>


                                    @endforeach



                                </select>


                            </div>







                            {{-- Role --}}
                            <div class="col-md-6">


                                <label class="form-label">
                                    Role
                                    <span class="text-danger">*</span>
                                </label>




                                <select name="role"
                                        class="form-select">



                                    <option value="">
                                        Select Role
                                    </option>




                                    @foreach($roles as $role)


                                    <option value="{{ $role->name }}"
                                    {{ $user->hasRole($role->name) ? 'selected':'' }}>


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



                                    <option value="1"
                                    {{ $user->status == 1 ? 'selected':'' }}>

                                        Active

                                    </option>



                                    <option value="0"
                                    {{ $user->status == 0 ? 'selected':'' }}>

                                        Inactive

                                    </option>



                                </select>


                            </div>







                            {{-- Profile --}}
                            <div class="col-md-6">


                                <label class="form-label">
                                    Profile Photo
                                </label>



                                <input type="file"
                                       name="profile_photo"
                                       class="form-control">



                            </div>







                            {{-- Current Image --}}
                            <div class="col-md-6">


                                @if($user->profile_photo)

                                <label class="form-label d-block">
                                    Current Photo
                                </label>


                                <img src="{{ asset('storage/'.$user->profile_photo) }}"
                                     class="rounded-circle"
                                     width="70"
                                     height="70">


                                @endif


                            </div>




                        </div>





                        <hr class="my-4">





                        <div class="text-end">


                            <button type="submit"
                                    class="btn btn-primary px-4">


                                <i class="fas fa-save me-1"></i>

                                Update User


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
document.addEventListener('DOMContentLoaded', function () {

    const companySelect = document.getElementById('company_id');
    const departmentSelect = document.getElementById('department_id');

    if (!companySelect || !departmentSelect) {
        return;
    }

    companySelect.addEventListener('change', function () {

        const companyId = this.value;

        fetch(`/users/companies/${companyId}/departments`)
        .then(response => response.json())
        .then(data => {

            departmentSelect.innerHTML =
                '<option value="">Select Department</option>';

            data.forEach(function (department) {

                departmentSelect.innerHTML +=
                    `<option value="${department.id}">
                        ${department.name}
                    </option>`;
            });

        })
        .catch(error => {
            console.error(error);
        });

    });

});
</script>
@endpush