@extends('layouts.admin')

@section('title', 'Create Company')

@section('content')

<div class="container-fluid">

    <div class="row">
        <div class="col-lg-8 mx-auto">

            <div class="card card-primary card-outline shadow-sm">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building me-2"></i>
                        Create Company
                    </h3>
                </div>

                <form action="{{ route('companies.store') }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="card-body">

                        <div class="row">

                            {{-- Company Name --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Company Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="name"
                                       value="{{ old('name') }}"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Enter company name">

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Email
                                </label>

                                <input type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="form-control @error('email') is-invalid @enderror"
                                       placeholder="company@example.com">

                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Phone
                                </label>

                                <input type="text"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       placeholder="+91 9876543210">

                                @error('phone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Website --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Website
                                </label>

                                <input type="url"
                                       name="website"
                                       value="{{ old('website') }}"
                                       class="form-control @error('website') is-invalid @enderror"
                                       placeholder="https://example.com">

                                @error('website')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Logo --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Company Logo
                                </label>

                                <input type="file"
                                       name="logo"
                                       class="form-control @error('logo') is-invalid @enderror"
                                       accept="image/*">

                                @error('logo')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Status
                                </label>

                                <select name="status"
                                        class="form-select">

                                    <option value="1" selected>
                                        Active
                                    </option>

                                    <option value="0">
                                        Inactive
                                    </option>

                                </select>
                            </div>

                            {{-- Address --}}
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    Address
                                </label>

                                <textarea name="address"
                                          rows="4"
                                          class="form-control @error('address') is-invalid @enderror"
                                          placeholder="Enter company address">{{ old('address') }}</textarea>

                                @error('address')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        </div>

                    </div>

                    <div class="card-footer d-flex justify-content-between">

                        <a href="{{ route('companies.index') }}"
                           class="btn btn-secondary">
                            Cancel
                        </a>

                        <button type="submit"
                                class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Save Company
                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>

</div>

@endsection