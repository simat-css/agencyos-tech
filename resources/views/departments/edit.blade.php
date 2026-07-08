@extends('layouts.admin')

@section('title', 'Edit Department')

@section('content')

<div class="container-fluid">

    <div class="card card-primary card-outline shadow-sm">

        <div class="card-header">

            <div class="d-flex justify-content-between align-items-center">

                <h3 class="card-title mb-0">

                    <i class="fas fa-edit me-2"></i>

                    Edit Department

                </h3>

                <a href="{{ route('departments.index') }}"
                   class="btn btn-secondary">

                    <i class="fas fa-arrow-left me-1"></i>

                    Back

                </a>

            </div>

        </div>

        <form action="{{ route('departments.update',$department) }}"
              method="POST">

            @csrf

            @method('PUT')

            <div class="card-body">

                @include('departments._form')

            </div>

            <div class="card-footer d-flex justify-content-between">

                <a href="{{ route('departments.index') }}"
                   class="btn btn-secondary">

                    Cancel

                </a>

                <button class="btn btn-primary">

                    <i class="fas fa-save me-1"></i>

                    Update Department

                </button>

            </div>

        </form>

    </div>

</div>

@endsection