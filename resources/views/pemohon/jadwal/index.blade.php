@extends('layouts.app')

@section('title', 'Jadwal Fasilitasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Jadwal Fasilitasi</h4>
                <p class="text-muted mb-0">Daftar jadwal fasilitasi/evaluasi dokumen perencanaan yang tersedia</p>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('pemohon.jadwal.index') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="jenis_dokumen" class="form-label">Jenis Dokumen</label>
                            <select name="jenis_dokumen" id="jenis_dokumen" class="form-select">
                                <option value="">Semua Jenis Dokumen</option>
                                @foreach ($filterOptions['jenisDokumen'] as $key => $value)
                                    <option value="{{ $key }}"
                                        {{ request('jenis_dokumen') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select name="tahun" id="tahun" class="form-select">
                                <option value="">Semua Tahun</option>
                                @foreach ($filterOptions['tahunList'] as $tahun)
                                    <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                        {{ $tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-search me-1'></i> Filter
                            </button>
                            <a href="{{ route('pemohon.jadwal.index') }}" class="btn btn-secondary">
                                <i class='bx bx-reset me-1'></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Jadwal List -->
        <div class="row">
            @forelse($jadwalList as $jadwal)
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">{{ $jadwal->permohonan?->kabupatenKota?->nama ?? '-' }}</h5>
                                    <span
                                        class="badge bg-label-{{ $jadwal->permohonan?->jenis_dokumen === 'perda' ? 'primary' : 'info' }}">
                                        {{ strtoupper($jadwal->permohonan?->jenis_dokumen ?? '-') }}
                                    </span>
                                </div>
                                <span
                                    class="badge bg-label-{{ $jadwal->tanggal_selesai > now() ? 'success' : 'secondary' }}">
                                    {{ $jadwal->tanggal_selesai > now() ? 'Akan Datang' : 'Selesai' }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Tahun</small>
                                        <strong>{{ $jadwal->permohonan?->tahun ?? '-' }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Tanggal Pelaksanaan</small>
                                        <strong
                                            class="text-{{ $jadwal->tanggal_pelaksanaan > now()->addDays(7) ? 'success' : 'warning' }}">
                                            {{ $jadwal->tanggal_pelaksanaan->format('d M Y') }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            @if ($jadwal->tempat)
                                <div class="mb-3">
                                    <small class="text-muted d-block">Tempat</small>
                                    <span>{{ $jadwal->tempat }}</span>
                                </div>
                            @endif

                            @if ($jadwal->keterangan)
                                <div class="mb-3">
                                    <small class="text-muted d-block">Keterangan</small>
                                    <p class="mb-0 small">{{ Str::limit($jadwal->keterangan, 100) }}</p>
                                </div>
                            @endif

                            @if ($jadwal->undangan_file)
                                <div class="mb-3">
                                    <a href="{{ Storage::url($jadwal->undangan_file) }}" target="_blank"
                                        class="btn btn-sm btn-outline-info">
                                        <i class='bx bx-file-blank me-1'></i> Undangan
                                    </a>
                                </div>
                            @endif

                            <div class="d-flex gap-2 mt-3">
                                <a href="{{ route('pemohon.jadwal.show', $jadwal->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class='bx bx-show me-1'></i> Detail
                                </a>
                                <a href="{{ route('permohonan.show', $jadwal->permohonan_id) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class='bx bx-file me-1'></i> Lihat Permohonan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class='bx bx-calendar-x bx-lg text-muted mb-3'></i>
                            <p class="text-muted mb-0">Belum ada jadwal fasilitasi yang tersedia</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($jadwalList->hasPages())
            <div class="mt-4">
                {{ $jadwalList->links() }}
            </div>
        @endif
    </div>
@endsection
