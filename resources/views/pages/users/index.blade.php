 @extends('layouts.app')

@section('title', 'Manajemen User')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                Manajemen
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Manajemen User</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            <i class='bx bx-arrow-back me-1'></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Daftar User</h5>
                            <p class="text-muted small mb-0">Kelola pengguna sistem</p>
                        </div>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Data
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="card mb-4" style="background-color: #f8f9fa;">
                        <div class="card-body">
                            <form action="{{ route('users.index') }}" method="GET">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Cari User</label>
                                        <input type="text" class="form-control" name="search"
                                            value="{{ request('search') }}" placeholder="Cari nama atau email...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Role</label>
                                        <select name="role" class="form-select">
                                            <option value="">Semua Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}"
                                                    {{ request('role') == $role->name ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bx bx-search me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-reset me-1"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $user)
                                <tr>
                                    <td>{{ $index + $users->firstItem() }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->roles->first())
                                            <span class="badge bg-label-primary">
                                                {{ $user->roles->first()->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary">Tidak ada role</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" 
                                                    data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('users.show', $user) }}">
                                                    <i class="bx bx-show me-1"></i> Detail
                                                </a>
                                                <a class="dropdown-item" href="{{ route('users.edit', $user) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                <a class="dropdown-item text-danger" href="javascript:void(0);" 
                                                   onclick="deleteUser({{ $user->id }})">
                                                    <i class="bx bx-trash me-1"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada data user</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteUser(userId) {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        // Buat form untuk delete
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/users/${userId}`;
        form.style.display = 'none';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        
        form.appendChild(methodInput);
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection 