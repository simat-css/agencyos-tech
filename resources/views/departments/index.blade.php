@extends('layouts.admin')

@section('title', 'Departments')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h3 class="mb-0">

                <i class="fas fa-sitemap me-2"></i>

                Departments

            </h3>

            <small class="text-muted">

                Manage all company departments

            </small>

        </div>

        @if($hasCompany)

    <a href="{{ route('departments.create') }}"
   class="btn btn-primary">
    <i class="fas fa-plus me-1"></i>
    Add Department
</a>

@else

    <div class="tooltip-wrapper">

    <button class="btn btn-primary" disabled>
        <i class="fas fa-plus me-1"></i>
        Add Department
    </button>

    <div class="custom-tooltip">
        <i class="fas fa-circle-info me-1"></i>
        Create a company first
    </div>

</div>

@endif

    </div>

    {{-- Statistics --}}
<div class="row mb-4">

    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Total Departments
                        </small>

                        <h2 class="fw-bold mb-0">
                            {{ $totalDepartments }}
                        </h2>

                    </div>

                    <div class="bg-primary text-white rounded p-3">

                        <i class="fas fa-sitemap fa-2x"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Active Departments
                        </small>

                        <h2 class="fw-bold text-success mb-0">
                            {{ $activeDepartments }}
                        </h2>

                    </div>

                    <div class="bg-success text-white rounded p-3">

                        <i class="fas fa-check-circle fa-2x"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Inactive Departments
                        </small>

                        <h2 class="fw-bold text-danger mb-0">
                            {{ $inactiveDepartments }}
                        </h2>

                    </div>

                    <div class="bg-danger text-white rounded p-3">

                        <i class="fas fa-ban fa-2x"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

    {{-- Filters --}}
    <div class="card mb-4">

        <div class="card-body">

            <form method="GET">

                <div class="row">

                    <div class="col-md-4 mb-2">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search Department..."
                            value="{{ request('search') }}">

                    </div>

                    <div class="col-md-3 mb-2">

                        <select
                            name="company"
                            class="form-select">

                            <option value="">

                                All Companies

                            </option>

                            @foreach($companies as $company)

                                <option
                                    value="{{ $company->id }}"
                                    {{ request('company')==$company->id ? 'selected':'' }}>

                                    {{ $company->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3 mb-2">

                        <select
                            name="status"
                            class="form-select">

                            <option value="">All Status</option>

                            <option value="1"
                                {{ request('status')==='1' ? 'selected':'' }}>

                                Active

                            </option>

                            <option value="0"
                                {{ request('status')==='0' ? 'selected':'' }}>

                                Inactive

                            </option>

                        </select>

                    </div>

                    <div class="col-md-2">

    <div class="d-flex gap-2">

        <button type="submit" class="btn btn-primary flex-fill">
            <i class="fas fa-search me-1"></i>
            Search
        </button>

        @if(request()->filled('search') || request()->filled('company') || request()->filled('status'))

            <a href="{{ route('departments.index') }}"
               class="btn btn-secondary">

                <i class="fas fa-times"></i>

            </a>

        @endif

    </div>

</div>

                </div>

            </form>

        </div>

    </div>

@if($companiesWithDepartments->count() > 0)

<div class="card mb-3 border-0 shadow-sm">

    <div class="card-body">

        <div class="d-flex align-items-center justify-content-between">

            <div>

                <h6 class="mb-0 fw-semibold">
                    Bulk Actions
                </h6>

                <small class="text-muted">
                    Apply actions on selected companies
                </small>

            </div>

            <div class="btn-group">

                <button id="bulk-activate"
                        class="btn btn-outline-success">

                    <i class="fas fa-check-circle me-1"></i>
                    Activate

                </button>

                <button id="bulk-deactivate"
                        class="btn btn-outline-warning">

                    <i class="fas fa-ban me-1"></i>
                    Deactivate

                </button>

                <button id="bulk-delete"
                        class="btn btn-outline-danger">

                    <i class="fas fa-trash me-1"></i>
                    Delete

                </button>

            </div>

        </div>

    </div>

</div>

@endif
    {{-- Company Wise Department List --}}

@forelse($companiesWithDepartments as $company)


<div class="card mb-4 shadow-sm">


    <div class="card-header bg-light">


        <div class="d-flex justify-content-between align-items-center">


            <div>

                <h5 class="mb-1 text-primary">

                    <i class="fas fa-building me-2"></i>

                   {{ $company->name }}

                </h5>


                <small class="text-muted">

                    Total Departments:
                    <strong>
                       {{ $company->departments->count() }}
                    </strong>

                </small>


            </div>



            <a href="{{ route('departments.create',['company'=>$company->id]) }}"
   class="btn btn-primary btn-sm">
    <i class="fas fa-plus me-1"></i>
    Add Department
</a>


        </div>


    </div>



    <div class="card-body p-0">


        <div class="table-responsive">


            <table class="table table-hover align-middle mb-0">


                <thead class="table-light">


                    <tr>

                        <th width="50">

    <input 
        type="checkbox"
        class="company-select-all"
       data-company="{{ $company->id }}">

</th>

                        <th>#</th>

                        <th>Department</th>

                        <th>Code</th>

                        <th>Status</th>

                        <th width="180">
                            Actions
                        </th>

                    </tr>


                </thead>



                <tbody>


               @foreach($company->departments as $department)


                <tr>


                    <td>

                        <input 
type="checkbox"
class="department-checkbox"
data-company="{{ $company->id }}"
value="{{ $department->id }}">

                    </td>



                    <td>

                        {{ $loop->iteration }}

                    </td>



                    <td>

                        <strong>
                            {{ $department->name }}
                        </strong>

                    </td>



                    <td>

                        {{ $department->code ?? '-' }}

                    </td>



                    <td>


                        <div class="form-check form-switch">


                            <input
                            class="form-check-input department-status"
                            type="checkbox"
                            data-id="{{ $department->id }}"
                            {{ $department->status ? 'checked':'' }}>


                        </div>


                    </td>




                    <td>


                        <a href="{{ route('departments.show',$department) }}"
                           class="btn btn-info btn-sm">

                            <i class="fas fa-eye"></i>

                        </a>



                        <a href="{{ route('departments.edit',$department) }}"
                           class="btn btn-warning btn-sm">

                            <i class="fas fa-edit"></i>

                        </a>




                        <form
                        action="{{ route('departments.destroy',$department) }}"
                        method="POST"
                        class="d-inline delete-department-form">


                            @csrf
                            @method('DELETE')


                            <button
    type="button"
    class="btn btn-danger btn-sm delete-department-btn">


                                <i class="fas fa-trash"></i>


                            </button>


                        </form>


                    </td>


                </tr>


                @endforeach


                </tbody>


            </table>


        </div>


    </div>


</div>


@empty


<div class="card">

    <div class="card-body text-center">

        No Departments Found

    </div>

</div>


@endforelse



{{-- Pagination --}}

@if($companiesWithDepartments->hasPages())

<div class="mt-3">

    {{ $companiesWithDepartments->links() }}

</div>

@endif


@endsection

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {


    // Toggle Status
    document.querySelectorAll('.department-status').forEach(function (toggle) {

    toggle.addEventListener('change', function () {

        let checkbox = this;
        let departmentId = this.dataset.id;

        let previousStatus = !checkbox.checked;


        fetch(`/departments/${departmentId}/toggle-status`, {

            method:'PATCH',

            headers:{
                'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content'),

                'Accept':'application/json'
            }

        })

        .then(async response => {

            let data = await response.json();


            if(!response.ok){

                checkbox.checked = previousStatus;

                throw new Error(data.message);

            }


            return data;

        })


        .then(data=>{


            Swal.fire({

                toast:true,
                position:'top-end',
                icon:'success',
                title:data.message,
                showConfirmButton:false,
                timer:1800

            });


        })


        .catch(error=>{


            Swal.fire({

                toast:true,
                position:'top-end',
                icon:'error',
                title:error.message,
                showConfirmButton:false,
                timer:2500

            });


        });


    });

});



    // Select All Checkbox

   // Company Wise Select All

document.querySelectorAll('.company-select-all')
.forEach(function(selectAll){

    selectAll.addEventListener('change',function(){

        let companyId = this.dataset.company;


        document
        .querySelectorAll(
            '.department-checkbox[data-company="'+companyId+'"]'
        )
        .forEach(function(cb){

            cb.checked = selectAll.checked;

        });


    });

});



    // Bulk Action

function bulkAction(action) {

    let selectedIds = [];

    document.querySelectorAll('.department-checkbox:checked')
        .forEach(cb => {
            selectedIds.push(cb.value);
        });

    if (selectedIds.length === 0) {

        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: 'Please select departments.'
        });

        return;
    }

    fetch("{{ route('departments.bulk-action') }}", {

        method: "POST",

        headers: {

            "Content-Type": "application/json",

            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .content,

            "Accept": "application/json"
        },

        body: JSON.stringify({

            action: action,
            ids: selectedIds

        })

    })

    .then(async response => {

        let data = {};

        try {
            data = await response.json();
        } catch (e) {
            data.message = "Something went wrong.";
        }

        if (!response.ok) {
            throw new Error(data.message);
        }

        return data;

    })

    .then(data => {

        Swal.fire({

            icon: 'success',
            title: data.message,
            showConfirmButton: false,
            timer: 1500

        });

        setTimeout(() => {

            window.location.reload();

        }, 1500);

    })

    .catch(error => {

        Swal.fire({
                icon: 'warning',
                title: 'Notice',
                text: error.message
            });

    });

}

    document.getElementById('bulk-delete')
    ?.addEventListener('click',()=>bulkAction('delete'));

    document.getElementById('bulk-activate')
    ?.addEventListener('click',()=>bulkAction('activate'));

    document.getElementById('bulk-deactivate')
    ?.addEventListener('click',()=>bulkAction('deactivate'));
document.querySelectorAll('.delete-department-btn')
.forEach(function(button){

    button.addEventListener('click', function(){

        let form = this.closest('form');

        Swal.fire({
            title: 'Delete Department?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        })
        .then((result) => {

            if (!result.isConfirmed) {
                return;
            }

            fetch(form.action, {

                method: 'POST',

                headers: {
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .content,

                    'Accept': 'application/json'
                },

                body: new FormData(form)

            })

            .then(async response => {

                let data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message);
                }

                return data;
            })

            .then(data => {

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message
                });

                setTimeout(() => {
                    location.reload();
                }, 1500);

            })

            .catch(error => {

                Swal.fire({
                    icon: 'warning',
                    title: 'Notice',
                    text: error.message
                });

            });

        });

    });

});

});

</script>

@endpush