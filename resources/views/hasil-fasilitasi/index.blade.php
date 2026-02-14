@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Input Hasil Fasilitasi / Evaluasi
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hasil-fasilitasi.index') }}">Input Hasil Fasilitasi</a></li>
                    </ol>
                </nav>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search" class="form-control" placeholder="Cari Kabupaten/Kota..."
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ route('hasil-fasilitasi.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-reset"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="25%">Kabupaten/Kota</th>
                                <th width="25%">Jenis Dokumen</th>
                                <th width="10%">Tahun</th>
                                <th width="15%">Status</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($permohonan as $item)
                                <tr>
                                    <td>{{ $loop->iteration + ($permohonan->currentPage() - 1) * $permohonan->perPage() }}
                                    </td>
                                    <td>
                                        <strong>{{ $item->kabupatenKota->nama ?? '-' }}</strong>
                                    </td>
                                    <td>{{ $item->jenisDokumen->nama ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-label-primary">{{ $item->tahun }}</span>
                                    </td>
                                    <td>
                                        @if ($item->hasilFasilitasi)
                                            @if ($item->hasilFasilitasi->status_validasi === 'tervalidasi')
                                                <span class="badge bg-success">
                                                    <i class='bx bx-check-circle'></i> Tervalidasi
                                                </span>
                                            @elseif ($item->hasilFasilitasi->status_validasi === 'diajukan')
                                                <span class="badge bg-warning">
                                                    <i class='bx bx-time'></i> Menunggu Validasi
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class='bx bx-edit'></i> Draft
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-danger">
                                                <i class='bx bx-x-circle'></i> Belum Input
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if (auth()->user()->hasRole('fasilitator'))
                                            <a href="{{ route('hasil-fasilitasi.create', $item) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="bx bx-edit"></i>
                                                @if ($item->hasilFasilitasi)
                                                    Edit Hasil
                                                @else
                                                    Input Hasil
                                                @endif
                                            </a>
                                        @endif
                                        @if ($item->hasilFasilitasi)
                                            <a href="{{ route('hasil-fasilitasi.show', $item) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="bx bx-show"></i> Lihat
                                            </a>
                                        @else
                                            @if (auth()->user()->hasRole('verifikator'))
                                                <span class="badge bg-secondary">Belum Ada Hasil</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data permohonan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $permohonan->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
