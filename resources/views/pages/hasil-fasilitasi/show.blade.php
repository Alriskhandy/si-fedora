@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Detail Hasil Fasilitasi</h4>
            <div>
                @if (!isset($isVerifikator) || !$isVerifikator)
                    @if ($hasilFasilitasi && ($hasilFasilitasi->hasilSistematika->count() > 0 || $hasilFasilitasi->hasilUrusan->count() > 0))
                        <a href="{{ route('hasil-fasilitasi.generate', $permohonan->id) }}" class="btn btn-primary me-2">
                            <i class="bx bxs-file-doc"></i> Generate Word
                        </a>
                        <a href="{{ route('hasil-fasilitasi.generate-pdf', $permohonan->id) }}" class="btn btn-danger me-2">
                            <i class="bx bxs-file-pdf"></i> Generate PDF
                        </a>
                    @endif
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
                        @if (!isset($isVerifikator) || !$isVerifikator)
                            <div class="mt-2">
                                <a href="{{ route('hasil-fasilitasi.create', $permohonan) }}"
                                    class="btn btn-sm btn-warning w-100">
                                    <i class="bx bx-edit"></i> Edit
                                </a>
                            </div>
                        @endif
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
                                @if ($hasilFasilitasi->hasilSistematika->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th width="20%">Bab / Sub Bab</th>
                                                    <th width="55%">Catatan Penyempurnaan</th>
                                                    <th width="20%">Oleh</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($hasilFasilitasi->hasilSistematika as $item)
                                                    <tr>
                                                        <td class="text-center">{{ $loop->iteration }}</td>
                                                        <td>
                                                            <strong
                                                                class="text-primary">{{ $item->masterBab->nama_bab ?? '-' }}</strong>
                                                            @if ($item->sub_bab)
                                                                <br><small class="text-muted">{{ $item->sub_bab }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{!! is_string($item->catatan_penyempurnaan)
                                                            ? $item->catatan_penyempurnaan
                                                            : $item->catatan_penyempurnaan->render() !!}</td>
                                                        <td>
                                                            <small class="text-muted">
                                                                {{ $item->user->name ?? '-' }}<br>
                                                                <span
                                                                    class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle"></i> Belum ada item sistematika.
                                    </div>
                                @endif
                            </div>

                            <!-- Tab 2: Urusan Pemerintahan -->
                            <div class="tab-pane fade" id="urusan" role="tabpanel">
                                @if ($hasilFasilitasi->hasilUrusan->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th width="25%">Urusan Pemerintahan</th>
                                                    <th width="50%">Catatan Masukan / Saran</th>
                                                    <th width="20%">Oleh</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($hasilFasilitasi->hasilUrusan as $item)
                                                    <tr>
                                                        <td class="text-center">{{ $loop->iteration }}</td>
                                                        <td>
                                                            <strong class="text-primary">
                                                                {{ $item->masterUrusan->urutan }}.
                                                                {{ $item->masterUrusan->nama_urusan }}
                                                            </strong>
                                                        </td>
                                                        <td>{!! is_string($item->catatan_masukan) ? $item->catatan_masukan : $item->catatan_masukan->render() !!}</td>
                                                        <td>
                                                            <small class="text-muted">
                                                                {{ $item->user->name ?? '-' }}<br>
                                                                <span
                                                                    class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle"></i> Belum ada item urusan.
                                    </div>
                                @endif
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
