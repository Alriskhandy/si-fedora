@extends('layouts.app')

@section('title', 'Edit Role')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Role: {{ $role->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label" for="name">Nama Role</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $role->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="permissions">Permissions</label>
                            <select class="form-select select2 @error('permissions') is-invalid @enderror" 
                                    id="permissions" name="permissions[]" multiple>
                                @foreach($permissions as $permission)
                                    <option value="{{ $permission->name }}" 
                                            {{ in_array($permission->name, $rolePermissions) ? 'selected' : '' }}>
                                        {{ $permission->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('permissions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary me-2">Update</button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Pilih permissions...",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection