@extends('layouts.app')

@section('title', 'Modul Pengguna')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Modul Pengguna</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Modul Pengguna</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class='bx bx-check-circle me-2'></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class='bx bx-error-circle me-2'></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($isSuperadmin)
            {{-- Form Upload — hanya superadmin --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class='bx bx-upload me-2'></i>Upload Modul Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('modul.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Judul Modul <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                                    value="{{ old('judul') }}" required>
                                @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ditujukan untuk Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    @foreach ($roles as $value => $label)
                                        <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">File <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                                <div class="form-text">PDF, Word, Excel, atau PowerPoint. Maks 50MB.</div>
                                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="2"
                                    placeholder="Penjelasan singkat isi modul (opsional)">{{ old('deskripsi') }}</textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class='bx bx-upload me-1'></i>Upload
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Daftar Modul --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class='bx bx-book-open me-2'></i>Daftar Modul</h5>
            </div>
            <div class="card-body">
                @if ($modul->isEmpty())
                    <div class="text-center py-5">
                        <i class='bx bx-file bx-lg text-muted mb-3 d-block'></i>
                        <p class="text-muted mb-0">Belum ada modul yang tersedia.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Judul</th>
                                    <th width="15%">Ditujukan untuk</th>
                                    <th width="10%">Ukuran</th>
                                    <th width="15%">Diupload</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modul as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $item->judul }}</strong>
                                            @if ($item->deskripsi)
                                                <br><small class="text-muted">{{ $item->deskripsi }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $roleLabels = [
                                                    'all'         => ['label' => 'Semua Role', 'class' => 'bg-primary'],
                                                    'pemohon'     => ['label' => 'Pemohon', 'class' => 'bg-info'],
                                                    'verifikator' => ['label' => 'Verifikator', 'class' => 'bg-warning text-dark'],
                                                    'fasilitator' => ['label' => 'Fasilitator', 'class' => 'bg-success'],
                                                    'admin_peran' => ['label' => 'Admin', 'class' => 'bg-secondary'],
                                                    'kaban'       => ['label' => 'Kaban', 'class' => 'bg-danger'],
                                                    'auditor'     => ['label' => 'Auditor', 'class' => 'bg-dark'],
                                                ];
                                                $badge = $roleLabels[$item->role] ?? ['label' => $item->role, 'class' => 'bg-secondary'];
                                            @endphp
                                            <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                        </td>
                                        <td><small>{{ $item->file_size_formatted }}</small></td>
                                        <td>
                                            <small>{{ $item->uploader->name ?? '-' }}</small><br>
                                            <small class="text-muted">{{ $item->created_at->format('d M Y') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('modul.download', $item) }}"
                                                class="btn btn-sm btn-primary" title="Download">
                                                <i class='bx bx-download me-1'></i>Download
                                            </a>
                                            @if ($isSuperadmin)
                                                <form action="{{ route('modul.destroy', $item) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Hapus modul ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Hapus">
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
