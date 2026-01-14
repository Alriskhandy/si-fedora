@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Pemohon /</span> Tindak Lanjut Hasil Fasilitasi
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
                <div>
                    <h5 class="mb-0">Daftar Permohonan dengan Surat Penyampaian Hasil</h5>
                    <p class="text-muted small mb-0 mt-2">
                        <i class="bx bx-info-circle"></i> Download surat penyampaian terlebih dahulu, kemudian upload
                        laporan tindak lanjut
                    </p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap" style="overflow: visible;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kabupaten/Kota</th>
                                <th>Perihal</th>
                                <th>Surat Penyampaian</th>
                                <th>Status Tindak Lanjut</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($permohonans as $index => $permohonan)
                                <tr>
                                    <td>{{ $permohonans->firstItem() + $index }}</td>
                                    <td>{{ $permohonan->kabupatenKota->nama ?? '-' }}</td>
                                    <td>
                                        <div style="max-width: 300px; white-space: normal;">
                                            {{ str()->limit($permohonan->perihal, 100) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($permohonan->hasilFasilitasi && $permohonan->hasilFasilitasi->surat_penyampaian)
                                            <span class="badge bg-success">Tersedia</span>
                                        @else
                                            <span class="badge bg-secondary">Belum tersedia</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($permohonan->tindakLanjut)
                                            <span class="badge bg-success">Sudah Upload</span>
                                        @else
                                            <span class="badge bg-warning">Belum Upload</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end text-nowrap">
                                                @if ($permohonan->hasilFasilitasi && $permohonan->hasilFasilitasi->surat_penyampaian)
                                                    <a class="dropdown-item"
                                                        href="{{ route('public.surat-penyampaian-hasil.download', $permohonan) }}">
                                                        <i class="bx bx-download me-1"></i> Download Surat Penyampaian
                                                    </a>
                                                @endif
                                                @if ($permohonan->tindakLanjut)
                                                    <a class="dropdown-item"
                                                        href="{{ route('tindak-lanjut.show', $permohonan) }}">
                                                        <i class="bx bx-show me-1"></i> Lihat Tindak Lanjut
                                                    </a>
                                                    <a class="dropdown-item"
                                                        href="{{ route('tindak-lanjut.download', $permohonan) }}">
                                                        <i class="bx bx-download me-1"></i> Download Laporan
                                                    </a>
                                                @else
                                                    <a class="dropdown-item"
                                                        href="{{ route('tindak-lanjut.create', $permohonan) }}">
                                                        <i class="bx bx-upload me-1"></i> Upload Laporan Tindak Lanjut
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
                                            <p class="text-muted mt-2">Belum ada permohonan dengan surat penyampaian hasil
                                            </p>
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
