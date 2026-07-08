@extends('layouts.admin')

@section('title', 'Create Department')

@section('content')

<div class="container-fluid">

    <div class="row">

        <div class="col-lg-8 mx-auto">
@if(session('warning'))

    <div class="alert alert-warning">

        <i class="fas fa-exclamation-triangle me-2"></i>

        {{ session('warning') }}

    </div>

@endif
            <div class="card card-primary card-outline shadow-sm">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-sitemap me-2"></i>

                        Create Department

                    </h3>

                </div>

                <form
                    action="{{ route('departments.store') }}"
                    method="POST">

                    @csrf

                    <div class="card-body">

                        @include('departments._form')

                    </div>

                    <div class="card-footer d-flex justify-content-between">

                        <a
                            href="{{ route('departments.index') }}"
                            class="btn btn-secondary">

                            Cancel

                        </a>

                        <button
                            class="btn btn-primary">

                            <i class="fas fa-save me-1"></i>

                            Save Department

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection