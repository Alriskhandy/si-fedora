@extends('layouts.app')

@section('title', 'Master Urusan')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Master Urusan Pemerintahan</h5>
                        <a href="{{ route('master-urusan.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Urusan
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Filter -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET">
                                    <select name="kategori" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Kategori</option>
                                        <option value="wajib_dasar"
                                            {{ request('kategori') == 'wajib_dasar' ? 'selected' : '' }}>
                                            Urusan Wajib Pelayanan Dasar
                                        </option>
                                        <option value="wajib_non_dasar"
                                            {{ request('kategori') == 'wajib_non_dasar' ? 'selected' : '' }}>
                                            Urusan Wajib Non Pelayanan Dasar
                                        </option>
                                        <option value="pilihan" {{ request('kategori') == 'pilihan' ? 'selected' : '' }}>
                                            Urusan Pilihan
                                        </option>
                                    </select>
                                </form>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Urutan</th>
                                        <th>Nama Urusan</th>
                                        <th>Kategori</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $currentKategori = null;
                                    @endphp
                                    @forelse($urusan as $index => $item)
                                        @if ($currentKategori !== $item->kategori)
                                            @php $currentKategori = $item->kategori; @endphp
                                            <tr class="table-secondary">
                                                <td colspan="5" class="fw-bold">
                                                    <i class="bx bx-folder me-2"></i>{{ $item->kategori_label }}
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <span class="badge bg-label-primary">{{ $item->urutan }}</span>
                                            </td>
                                            <td>{{ $item->nama_urusan }}</td>
                                            <td>
                                                @if ($item->kategori == 'wajib_dasar')
                                                    <span class="badge bg-label-success">Wajib Dasar</span>
                                                @elseif($item->kategori == 'wajib_non_dasar')
                                                    <span class="badge bg-label-info">Wajib Non Dasar</span>
                                                @else
                                                    <span class="badge bg-label-warning">Pilihan</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item"
                                                            href="{{ route('master-urusan.edit', $item) }}">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                                        </a>
                                                        <form action="{{ route('master-urusan.destroy', $item) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                onclick="return confirm('Yakin ingin menghapus?')">
                                                                <i class="bx bx-trash me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Tidak ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
