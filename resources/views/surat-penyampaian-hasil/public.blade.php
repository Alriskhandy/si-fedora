@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Publik /</span> Surat Penyampaian Hasil Fasilitasi
        </h4>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Surat Penyampaian Hasil Fasilitasi</h5>
                <p class="text-muted small mb-0 mt-2">
                    <i class="bx bx-info-circle"></i> Surat penyampaian hasil fasilitasi yang telah diterbitkan
                </p>
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
                                <th>Tanggal Surat</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hasilList as $index => $hasil)
                                <tr>
                                    <td>{{ $hasilList->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $hasil->permohonan->nomor_permohonan }}</strong><br>
                                        <small
                                            class="text-muted">{{ $hasil->permohonan->created_at->format('d M Y') }}</small>
                                    </td>
                                    <td>{{ $hasil->permohonan->kabupatenKota->nama_kabkota ?? '-' }}</td>
                                    <td>
                                        <div style="max-width: 400px; white-space: normal;">
                                            {{ str()->limit($hasil->permohonan->perihal, 150) }}
                                        </div>
                                    </td>
                                    <td>{{ $hasil->surat_tanggal ? $hasil->surat_tanggal->format('d M Y') : '-' }}</td>
                                    <td>
                                        <a href="{{ route('public.surat-penyampaian-hasil.download', $hasil->permohonan) }}"
                                            class="btn btn-sm btn-primary" title="Download Surat">
                                            <i class="bx bx-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="bx bx-folder-open" style="font-size: 48px; color: #ddd;"></i>
                                        <p class="text-muted mt-2">Belum ada surat penyampaian yang dipublikasikan</p>
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
