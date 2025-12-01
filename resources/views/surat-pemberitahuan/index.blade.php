@extends('layouts.app')

@section('title', 'Surat Pemberitahuan')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Surat Pemberitahuan</h5>
                    <a href="{{ route('surat-pemberitahuan.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Surat
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('surat-pemberitahuan.index') }}">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Cari surat..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="bx bx-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Surat</th>
                                    <th>Perihal</th>
                                    <th>Kabupaten/Kota</th>
                                    <th>Jadwal</th>
                                    <th>Tanggal Surat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suratPemberitahuan as $index => $item)
                                <tr>
                                    <td>{{ $index + $suratPemberitahuan->firstItem() }}</td>
                                    <td>{{ $item->nomor_surat ?? '-' }}</td>
                                    {{-- <td>{{ Str::limit($item->perihal, 30) }}</td> --}}
                                    <td>{{ $item->kabupatenKota->getFullNameAttribute() ?? '-' }}</td>
                                    <td>{{ $item->jadwalFasilitasi->nama_kegiatan ?? '-' }}</td>
                                    <td>{{ $item->tanggal_surat->format('d M Y') }}</td>
                                    <td>
                                        @if($item->status == 'draft')
                                            <span class="badge bg-label-warning">Draft</span>
                                        @elseif($item->status == 'sent')
                                            <span class="badge bg-label-success">Sent</span>
                                        @elseif($item->status == 'received')
                                            <span class="badge bg-label-info">Received</span>
                                        @else
                                            <span class="badge bg-label-secondary">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" 
                                                    data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('surat-pemberitahuan.show', $item) }}">
                                                    <i class="bx bx-show me-1"></i> Detail
                                                </a>
                                                <a class="dropdown-item" href="{{ route('surat-pemberitahuan.edit', $item) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                @if($item->file_path)
                                                <a class="dropdown-item" href="{{ route('surat-pemberitahuan.download', $item) }}" target="_blank">
                                                    <i class="bx bx-download me-1"></i> Download
                                                </a>
                                                @endif
                                                @if($item->status == 'draft')
                                                <form action="{{ route('surat-pemberitahuan.send', $item) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bx bx-send me-1"></i> Send
                                                    </button>
                                                </form>
                                                @endif
                                                <form action="{{ route('surat-pemberitahuan.destroy', $item) }}" method="POST" class="d-inline">
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
                                    <td colspan="8" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $suratPemberitahuan->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection