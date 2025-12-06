@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Kaban /</span> Penetapan PERDA/PERKADA
        </h4>

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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Permohonan dengan Tindak Lanjut</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Permohonan</th>
                                <th>Kabupaten/Kota</th>
                                <th>Perihal</th>
                                <th>Tindak Lanjut</th>
                                <th>Status Penetapan</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($permohonans as $index => $permohonan)
                                <tr>
                                    <td>{{ $permohonans->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $permohonan->nomor_permohonan }}</strong><br>
                                        <small class="text-muted">{{ $permohonan->created_at->format('d M Y') }}</small>
                                    </td>
                                    <td>{{ $permohonan->kabupatenKota->nama_kabkota ?? '-' }}</td>
                                    <td>
                                        <div style="max-width: 300px; white-space: normal;">
                                            {{ Str::limit($permohonan->perihal, 100) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($permohonan->tindakLanjut)
                                            <span class="badge bg-info">
                                                <i class="bx bx-check"></i>
                                                {{ $permohonan->tindakLanjut->tanggal_upload->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Belum ada</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($permohonan->penetapanPerda)
                                            <span class="badge bg-success">Sudah Ditetapkan</span>
                                        @else
                                            <span class="badge bg-warning">Belum Ditetapkan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if ($permohonan->penetapanPerda)
                                                    <a class="dropdown-item"
                                                        href="{{ route('penetapan-perda.show', $permohonan) }}">
                                                        <i class="bx bx-show me-1"></i> Lihat Detail
                                                    </a>
                                                    <a class="dropdown-item"
                                                        href="{{ route('penetapan-perda.download', $permohonan) }}">
                                                        <i class="bx bx-download me-1"></i> Download File
                                                    </a>
                                                @else
                                                    <a class="dropdown-item"
                                                        href="{{ route('penetapan-perda.create', $permohonan) }}">
                                                        <i class="bx bx-plus me-1"></i> Input Penetapan
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="py-4">
                                            <i class="bx bx-folder-open" style="font-size: 48px; color: #ddd;"></i>
                                            <p class="text-muted mt-2">Belum ada permohonan dengan tindak lanjut</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($permohonans->hasPages())
                <div class="card-footer">
                    {{ $permohonans->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
