@extends('layouts.app')

@section('title', 'Dokumen Permohonan')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables/dataTables.bootstrap5.css') }}" />
@endsection

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Dokumen Permohonan</h5>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" id="search" placeholder="Cari dokumen...">
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">Semua Status</option>
                            <option value="required">Wajib</option>
                            <option value="not_required">Tidak Wajib</option>
                            <option value="submitted">Sudah Diupload</option>
                            <option value="missing">Belum Diupload</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="permohonanDokumenTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Dokumen</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                    <th>File</th>
                                    <th>Verifikasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permohonanDokumen as $index => $dokumen)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $dokumen->persyaratanDokumen->nama ?? 'Dokumen Tidak Ditemukan' }}</td>
                                    <td>
                                        @if($dokumen->persyaratanDokumen->is_wajib)
                                            <span class="badge bg-label-danger">Wajib</span>
                                        @else
                                            <span class="badge bg-label-secondary">Tidak Wajib</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dokumen->file_path)
                                            <span class="badge bg-label-success">Sudah Diupload</span>
                                        @else
                                            <span class="badge bg-label-warning">Belum Diupload</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dokumen->file_path)
                                            <a href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-download"></i> Lihat File
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dokumen->status_verifikasi)
                                            <span class="badge bg-label-{{ $dokumen->status_verifikasi_badge_class }}">{{ $dokumen->status_verifikasi_label }}</span>
                                            @if($dokumen->catatan_verifikasi)
                                                <small class="text-muted d-block">Catatan: {{ $dokumen->catatan_verifikasi }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">Belum diverifikasi</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" 
                                                    data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if(!$dokumen->file_path || $dokumen->status_verifikasi == 'rejected')
                                                <a class="dropdown-item" href="{{ route('permohonan-dokumen.edit', $dokumen) }}">
                                                    <i class="bx bx-upload me-1"></i> Upload
                                                </a>
                                                @endif
                                                @if($dokumen->file_path)
                                                <a class="dropdown-item" href="{{ asset('storage/' . $dokumen->file_path) }}" target="_blank">
                                                    <i class="bx bx-show me-1"></i> Lihat
                                                </a>
                                                @endif
                                                <a class="dropdown-item text-danger" href="javascript:void(0);" 
                                                   onclick="deleteDokumen({{ $dokumen->id }})">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Tidak ada dokumen persyaratan</td>
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

@section('scripts')
<script src="{{ asset('assets/vendor/libs/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/datatables/dataTables.bootstrap5.js') }}"></script>
<script>
$(document).ready(function() {
    $('#permohonanDokumenTable').DataTable({
        pageLength: 10,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.12.1/i18n/id.json'
        }
    });

    // Filter
    $('#search').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#filterStatus').on('change', function() {
        var status = this.value;
        if(status) {
            table.column(2).search(status).draw();
        } else {
            table.column(2).search('').draw();
        }
    });
});

function deleteDokumen(id) {
    if(confirm('Yakin ingin menghapus dokumen ini?')) {
        $.ajax({
            url: '/permohonan-dokumen/' + id,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('Gagal menghapus dokumen');
            }
        });
    }
}
</script>
@endsection