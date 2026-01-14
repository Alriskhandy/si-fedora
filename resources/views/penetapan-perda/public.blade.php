@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Publik /</span> Dokumen PERDA/PERKADA
        </h4>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Dokumen PERDA/PERKADA yang Telah Ditetapkan</h5>
                <p class="text-muted small mb-0 mt-2">
                    <i class="bx bx-info-circle"></i> Berikut adalah daftar Peraturan Daerah (PERDA) dan Peraturan Kepala
                    Daerah (PERKADA) yang telah ditetapkan dan dapat diunduh oleh publik.
                </p>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('public.penetapan-perda') }}">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Cari Kabupaten/Kota..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bx bx-search"></i> Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis</th>
                                <th>Nomor</th>
                                <th>Tentang</th>
                                <th>Kabupaten/Kota</th>
                                <th>Tanggal Penetapan</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($penetapans as $index => $penetapan)
                                <tr>
                                    <td>{{ $penetapans->firstItem() + $index }}</td>
                                    <td>
                                        <span class="badge bg-primary">PERDA/PERKADA</span>
                                    </td>
                                    <td>
                                        <strong>{{ $penetapan->nomor_perda }}</strong>
                                    </td>
                                    <td>
                                        <div style="max-width: 400px; white-space: normal;">
                                            {{ str()->limit($penetapan->keterangan, 150) }}
                                        </div>
                                    </td>
                                    <td>{{ $penetapan->permohonan->kabupatenKota->nama_kabkota ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($penetapan->tanggal_penetapan)->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('penetapan-perda.download', $penetapan->permohonan) }}"
                                            class="btn btn-sm btn-primary" title="Download Dokumen">
                                            <i class="bx bx-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="py-4">
                                            <i class="bx bx-folder-open" style="font-size: 48px; color: #ddd;"></i>
                                            <p class="text-muted mt-2">Belum ada dokumen penetapan yang dipublikasikan</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($penetapans->hasPages())
                <div class="card-footer">
                    {{ $penetapans->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        <!-- Informasi Tambahan -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-info" role="alert">
                    <h5 class="alert-heading">
                        <i class="bx bx-info-circle"></i> Informasi
                    </h5>
                    <p class="mb-0">
                        Dokumen-dokumen yang tersedia di halaman ini adalah hasil akhir dari proses fasilitasi penyusunan
                        RKPD yang telah ditetapkan menjadi PERDA atau PERKADA oleh Pemerintah Daerah masing-masing.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
