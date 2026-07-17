<div class="row">

    {{-- Company --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Company
            <span class="text-danger">*</span>
        </label>

        <select
    name="company_id"
    class="form-select @error('company_id') is-invalid @enderror"
    {{ isset($selectedCompany) ? 'disabled' : '' }}>

    <option value="">
         {{ isset($selectedCompany) ? 'Company Inactive' : ' Select Company' }}
    </option>

    @foreach($companies as $company)

        <option
            value="{{ $company->id }}"
            {{ old('company_id', $department->company_id ?? ($selectedCompany ?? '')) == $company->id ? 'selected' : '' }}>

            {{ $company->name }}

        </option>

    @endforeach

</select>

@if(isset($selectedCompany))

    <input
        type="hidden"
        name="company_id"
        value="{{ $selectedCompany }}">

@endif

        @error('company_id')

            <div class="invalid-feedback">

                {{ $message }}

            </div>

        @enderror

    </div>

    {{-- Department Name --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Department Name
            <span class="text-danger">*</span>
        </label>

        <input
            type="text"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $department->name ?? '') }}"
            placeholder="Enter Department Name">

        @error('name')

            <div class="invalid-feedback">

                {{ $message }}

            </div>

        @enderror

    </div>

    {{-- Department Code --}}
    <div class="col-md-6 mb-3">

        <label class="form-label">

            Department Code

        </label>

        <input
            type="text"
            name="code"
            class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', $department->code ?? '') }}"
            placeholder="DEV / HR / ACC">

        @error('code')

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

        <select
            name="status"
            class="form-select">

            <option
                value="1"
                {{ old('status', $department->status ?? 1) == 1 ? 'selected' : '' }}>

                Active

            </option>

            <option
                value="0"
                {{ old('status', $department->status ?? 1) == 0 ? 'selected' : '' }}>

                Inactive

            </option>

        </select>

    </div>

    {{-- Description --}}
    <div class="col-md-12 mb-3">

        <label class="form-label">

            Description

        </label>

        <textarea
            name="description"
            rows="4"
            class="form-control @error('description') is-invalid @enderror"
            placeholder="Department Description">{{ old('description', $department->description ?? '') }}</textarea>

        @error('description')

            <div class="invalid-feedback">

                {{ $message }}

            </div>

        @enderror

    </div>

</div>