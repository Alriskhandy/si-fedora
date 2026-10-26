@extends('layouts.app')

@section('title', 'Role & Hak Akses')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Role & Hak Akses</h5>
            @can('create', Spatie\Permission\Models\Role::class)
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Tambah Role
                </a>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Jumlah Hak Akses</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td><code>{{ $role->name }}</code></td>
                            <td>{{ $role->permissions_count }}</td>
                            <td class="text-end">
                                @can('update', $role)
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-edit-alt me-1"></i> Atur Hak Akses
                                    </a>
                                    @if (App\Policies\RolePolicy::isCatalogRole($role))
                                        <form action="{{ route('admin.roles.reset', $role) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                onclick="return confirm('Kembalikan hak akses role {{ $role->name }} ke default katalog?')">
                                                <i class="bx bx-reset me-1"></i> Default
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                @can('delete', $role)
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Yakin ingin menghapus role {{ $role->name }}?')">
                                            <i class="bx bx-trash me-1"></i> Hapus
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Tidak ada role</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
