@extends('layouts.app')

@section('title', 'Persetujuan Draft Hasil Fasilitasi / Evaluasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Persetujuan Draft Hasil Fasilitasi / Evaluasi
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.index') }}">Persetujuan Draft Hasil Fasilitasi
                                / Evaluasi</a>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Draft Final Menunggu Persetujuan</h5>
                        <span class="badge bg-warning">{{ $permohonan->total() }} Dokumen</span>
                    </div>
                    <div class="card-body">
                        <!-- Filter -->
                        <form action="{{ route('approval.index') }}" method="GET" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Cari kabupaten/kota atau jenis dokumen..."
                                        value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="tahun" class="form-select">
                                        <option value="">Semua Tahun</option>
                                        @for ($year = date('Y'); $year >= 2020; $year--)
                                            <option value="{{ $year }}"
                                                {{ request('tahun') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bx bx-search me-1"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('approval.index') }}" class="btn btn-outline-secondary w-100">
                                        <i class="bx bx-reset me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        @if ($permohonan->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="25%">Kabupaten/Kota</th>
                                            <th width="20%">Jenis Dokumen</th>
                                            <th width="12%">Tahun</th>
                                            <th width="15%">Tanggal Diajukan</th>
                                            <th width="13%">Status</th>
                                            <th width="10%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($permohonan as $index => $item)
                                            <tr>
                                                <td>{{ $permohonan->firstItem() + $index }}</td>
                                                <td>
                                                    <strong>{{ $item->kabupatenKota->nama ?? '-' }}</strong>
                                                </td>
                                                <td>{{ $item->jenisDokumen->nama ?? '-' }}</td>
                                                <td>{{ $item->tahun }}</td>
                                                <td>
                                                    @if ($item->hasilFasilitasi && $item->hasilFasilitasi->tanggal_diajukan_kaban)
                                                        <small>{{ $item->hasilFasilitasi->tanggal_diajukan_kaban->format('d M Y') }}</small><br>
                                                        <small
                                                            class="text-muted">{{ $item->hasilFasilitasi->tanggal_diajukan_kaban->format('H:i') }}</small>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-warning">
                                                        <i class="bx bx-time-five me-1"></i> Menunggu Persetujuan
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('approval.show', $item) }}"
                                                        class="btn btn-sm btn-primary" title="Review & Approve">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                {{ $permohonan->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class='bx bx-file-find bx-lg mb-3 text-muted' style="font-size: 4rem;"></i>
                                <h5 class="text-muted">Tidak Ada Dokumen yang Menunggu Persetujuan</h5>
                                <p class="text-muted">
                                    Belum ada draft final yang diajukan oleh Admin untuk disetujui.
                                </p>
                                <p class="text-muted small">
                                    <em>Admin perlu mengupload draft final dan klik "Submit ke Kepala Badan" terlebih dahulu.</em>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
