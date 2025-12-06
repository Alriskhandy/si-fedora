@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Detail Hasil Fasilitasi</h4>
            <div>
                @if ($hasilFasilitasi && ($hasilFasilitasi->hasilSistematika->count() > 0 || $hasilFasilitasi->hasilUrusan->count() > 0))
                    <a href="{{ route('hasil-fasilitasi.generate', $permohonan->id) }}" class="btn btn-success me-2">
                        <i class="bx bx-file"></i> Generate Word
                    </a>
                    <a href="{{ route('hasil-fasilitasi.generate-pdf', $permohonan->id) }}" class="btn btn-primary me-2">
                        <i class="bx bxs-file-pdf"></i> Generate PDF
                    </a>
                @endif
                <a href="{{ route('hasil-fasilitasi.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <!-- Informasi Permohonan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Kabupaten/Kota</dt>
                            <dd class="col-sm-7">{{ $permohonan->kabupatenKota->nama }}</dd>

                            <dt class="col-sm-5">No. Permohonan</dt>
                            <dd class="col-sm-7">{{ $permohonan->no_permohonan }}</dd>

                            <dt class="col-sm-5">Tanggal</dt>
                            <dd class="col-sm-7">{{ $permohonan->created_at->format('d M Y') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <!-- Ringkasan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Ringkasan</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-7">Sistematika</dt>
                            <dd class="col-sm-5">
                                <span class="badge bg-primary">{{ $hasilFasilitasi->hasilSistematika->count() }}</span> item
                            </dd>

                            <dt class="col-sm-7">Urusan</dt>
                            <dd class="col-sm-5">
                                <span class="badge bg-primary">{{ $hasilFasilitasi->hasilUrusan->count() }}</span> item
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <!-- Info Tambahan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Info Tambahan</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Dibuat Oleh</dt>
                            <dd class="col-sm-7">{{ $hasilFasilitasi->pembuat->name }}</dd>

                            <dt class="col-sm-5">Tanggal</dt>
                            <dd class="col-sm-7">
                                {{ $hasilFasilitasi->created_at->format('d M Y') }}
                            </dd>

                            <dt class="col-sm-5">Update</dt>
                            <dd class="col-sm-7">
                                {{ $hasilFasilitasi->updated_at->format('d M Y') }}
                            </dd>
                        </dl>
                        <div class="mt-2">
                            <a href="{{ route('hasil-fasilitasi.create', $permohonan) }}"
                                class="btn btn-sm btn-warning w-100">
                                <i class="bx bx-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <!-- Tabs untuk Sistematika dan Urusan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-list-check"></i> Masukan Fasilitasi</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="sistematika-tab" data-bs-toggle="tab"
                                    data-bs-target="#sistematika" type="button" role="tab">
                                    <i class="bx bx-book-content"></i> Sistematika & Rancangan Akhir
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="urusan-tab" data-bs-toggle="tab" data-bs-target="#urusan"
                                    type="button" role="tab">
                                    <i class="bx bx-list-ul"></i> Urusan Pemerintahan
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-4">
                            <!-- Tab 1: Sistematika -->
                            <div class="tab-pane fade show active" id="sistematika" role="tabpanel">
                                @forelse ($hasilFasilitasi->hasilSistematika as $item)
                                    <div class="card mb-2">
                                        <div class="card-body py-2">
                                            <h6 class="mb-1 text-primary">{{ $item->bab_sub_bab }}</h6>
                                            <p class="mb-0 small">{{ $item->catatan_penyempurnaan }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle"></i> Belum ada item sistematika.
                                    </div>
                                @endforelse
                            </div>

                            <!-- Tab 2: Urusan Pemerintahan -->
                            <div class="tab-pane fade" id="urusan" role="tabpanel">
                                @forelse ($hasilFasilitasi->hasilUrusan as $item)
                                    <div class="card mb-2">
                                        <div class="card-body py-2">
                                            <h6 class="mb-1 text-primary">
                                                {{ $item->masterUrusan->urutan }}. {{ $item->masterUrusan->nama_urusan }}
                                            </h6>
                                            <p class="mb-0 small">{{ $item->catatan_masukan }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle"></i> Belum ada item urusan.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Files -->
            </div>

            <!-- Files -->
            @if ($hasilFasilitasi->draft_file || $hasilFasilitasi->final_file)
                <div class="card mb-4">
                    <div class="card-body">
                        @if ($hasilFasilitasi->draft_file)
                            <div class="mb-2">
                                <strong>Draft File:</strong>
                                <a href="{{ url('storage/' . $hasilFasilitasi->draft_file) }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="bx bx-download"></i> Download
                                </a>
                            </div>
                        @endif

                        @if ($hasilFasilitasi->final_file)
                            <div class="mb-2">
                                <strong>Final File:</strong>
                                <a href="{{ url('storage/' . $hasilFasilitasi->final_file) }}" target="_blank"
                                    class="btn btn-sm btn-outline-success ms-2">
                                    <i class="bx bx-download"></i> Download
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Catatan -->
            @if ($hasilFasilitasi->catatan)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-note"></i> Catatan</h5>
                    </div>
                    <div class="card-body">
                        <div class="border rounded p-3" style="white-space: pre-line;">
                            {{ $hasilFasilitasi->catatan }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>
@endsection
