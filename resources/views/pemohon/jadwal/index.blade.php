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
                            <label for="jenis_dokumen_id" class="form-label">Jenis Dokumen</label>
                            <select name="jenis_dokumen_id" id="jenis_dokumen_id" class="form-select">
                                <option value="">Semua Jenis Dokumen</option>
                                @foreach ($filterOptions['jenisDokumen'] as $jd)
                                    <option value="{{ $jd->id }}"
                                        {{ request('jenis_dokumen_id') == $jd->id ? 'selected' : '' }}>
                                        {{ $jd->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tahun_anggaran_id" class="form-label">Tahun Anggaran</label>
                            <select name="tahun_anggaran_id" id="tahun_anggaran_id" class="form-select">
                                <option value="">Semua Tahun</option>
                                @foreach ($filterOptions['tahunAnggaran'] as $ta)
                                    <option value="{{ $ta->id }}"
                                        {{ request('tahun_anggaran_id') == $ta->id ? 'selected' : '' }}>
                                        {{ $ta->tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filter_status" class="form-label">Status</label>
                            <select name="filter_status" id="filter_status" class="form-select">
                                <option value="">Jadwal Aktif</option>
                                <option value="aktif" {{ request('filter_status') == 'aktif' ? 'selected' : '' }}>Aktif
                                </option>
                                <option value="expired" {{ request('filter_status') == 'expired' ? 'selected' : '' }}>
                                    Expired</option>
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
                                <h5 class="card-title mb-0">{{ $jadwal->jenisDokumen->nama ?? '-' }}</h5>
                                <span
                                    class="badge bg-label-{{ $jadwal->batas_permohonan > now() ? 'success' : 'secondary' }}">
                                    {{ $jadwal->batas_permohonan > now() ? 'Aktif' : 'Expired' }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Tahun Anggaran</small>
                                        <strong>{{ $jadwal->tahunAnggaran->tahun ?? '-' }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Batas Permohonan</small>
                                        <strong
                                            class="text-{{ $jadwal->batas_permohonan > now()->addDays(7) ? 'success' : 'warning' }}">
                                            {{ $jadwal->batas_permohonan->format('d M Y') }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Jadwal Pelaksanaan</small>
                                <strong>{{ $jadwal->tanggal_mulai->format('d M Y') }} -
                                    {{ $jadwal->tanggal_selesai->format('d M Y') }}</strong>
                            </div>

                            @if ($jadwal->lokasi)
                                <div class="mb-3">
                                    <small class="text-muted d-block">Lokasi</small>
                                    <span>{{ $jadwal->lokasi }}</span>
                                </div>
                            @endif

                            @if ($jadwal->keterangan)
                                <div class="mb-3">
                                    <small class="text-muted d-block">Keterangan</small>
                                    <p class="mb-0 small">{{ Str::limit($jadwal->keterangan, 100) }}</p>
                                </div>
                            @endif

                            <div class="d-flex gap-2 mt-3">
                                <a href="{{ route('pemohon.jadwal.show', $jadwal->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class='bx bx-show me-1'></i> Detail
                                </a>
                                @if ($jadwal->batas_permohonan > now())
                                    <a href="{{ route('permohonan.create', ['jadwal_id' => $jadwal->id]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class='bx bx-plus me-1'></i> Buat Permohonan
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class='bx bx-time me-1'></i> Expired
                                    </button>
                                @endif
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
