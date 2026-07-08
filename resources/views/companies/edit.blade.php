@extends('layouts.admin')

@section('title', 'Edit Company')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header">

    <div class="d-flex justify-content-between align-items-center">

        <h3 class="mb-0">
            <i class="fas fa-edit me-2"></i>
            Edit Company
        </h3>

        <a href="{{ route('companies.index') }}"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left me-1"></i>
            Back

        </a>

    </div>

</div>

        <form action="{{ route('companies.update', $company) }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Company Name *</label>

                        <input type="text"
                               name="name"
                               value="{{ old('name', $company->name) }}"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Email</label>

                        <input type="email"
                               name="email"
                               value="{{ old('email', $company->email) }}"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Phone</label>

                        <input type="text"
                               name="phone"
                               value="{{ old('phone', $company->phone) }}"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Website</label>

                        <input type="url"
                               name="website"
                               value="{{ old('website', $company->website) }}"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Logo</label>

                        <input type="file"
                               name="logo"
                               class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Status</label>

                        <select name="status"
                                class="form-select">

                            <option value="1"
                                {{ $company->status ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="0"
                                {{ !$company->status ? 'selected' : '' }}>
                                Inactive
                            </option>

                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Address</label>

                        <textarea name="address"
                                  rows="4"
                                  class="form-control">{{ old('address', $company->address) }}</textarea>
                    </div>

                </div>

            </div>

            <div class="card-footer">

                <a href="{{ route('companies.index') }}"
                   class="btn btn-secondary">
                    Cancel
                </a>

                <button type="submit"
                        class="btn btn-primary">
                    Update Company
                </button>

            </div>

        </form>

    </div>

</div>

@endsection