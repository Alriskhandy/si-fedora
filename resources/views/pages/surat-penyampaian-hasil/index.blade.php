@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Kaban /</span> Surat Penyampaian Hasil Fasilitasi
        </h4>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Hasil Fasilitasi</h5>
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
                                <th>Status Surat</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hasilList as $index => $hasil)
                                <tr>
                                    <td>{{ $hasilList->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $hasil->permohonan->nomor_permohonan }}</strong><br>
                                        <small class="text-muted">{{ $hasil->created_at->format('d M Y') }}</small>
                                    </td>
                                    <td>{{ $hasil->permohonan->kabupatenKota->nama_kabkota ?? '-' }}</td>
                                    <td>
                                        <div style="max-width: 300px; white-space: normal;">
                                            {{ str()->limit($hasil->permohonan->perihal, 100) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($hasil->surat_penyampaian)
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
                                            <div class="dropdown-menu">
                                                @if ($hasil->surat_penyampaian)
                                                    <a class="dropdown-item"
                                                        href="{{ route('surat-penyampaian-hasil.show', $hasil->permohonan) }}">
                                                        <i class="bx bx-show me-1"></i> Lihat Detail
                                                    </a>
                                                @else
                                                    <a class="dropdown-item"
                                                        href="{{ route('surat-penyampaian-hasil.create', $hasil->permohonan) }}">
                                                        <i class="bx bx-upload me-1"></i> Upload Surat
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="bx bx-folder-open" style="font-size: 48px; color: #ddd;"></i>
                                        <p class="text-muted mt-2">Belum ada hasil fasilitasi</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($hasilList->hasPages())
                <div class="card-footer">
                    {{ $hasilList->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
