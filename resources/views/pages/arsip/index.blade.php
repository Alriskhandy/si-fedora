@extends('layouts.app')

@section('title', 'Arsip Dokumen')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Arsip Dokumen
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Arsip Dokumen</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <!-- Document Type Cards -->
        <div class="row g-4">
            @forelse($jenisDokumenList as $jenisDokumen)
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <a href="{{ route('arsip.listByJenis', $jenisDokumen->id) }}" class="text-decoration-none">
                        <div class="card arsip-card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="arsip-icon-wrapper me-3">
                                        <i class='bx bxs-folder-open'></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        @if ($jenisDokumen->permohonan_count > 0)
                                            <span class="badge bg-primary rounded-pill float-end">
                                                {{ $jenisDokumen->permohonan_count }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill float-end">0</span>
                                        @endif
                                    </div>
                                </div>

                                <h5 class="card-title mb-2 text-dark">{{ $jenisDokumen->nama }}</h5>

                                <p class="card-text text-muted small mb-3">
                                    @if ($jenisDokumen->nama_dokumen)
                                        {{ Str::limit($jenisDokumen->nama_dokumen, 80) }}
                                    @else
                                        Kumpulan dokumen {{ strtolower($jenisDokumen->nama) }} yang telah selesai diproses
                                        dan diarsipkan.
                                    @endif
                                </p>

                                <div class="d-flex align-items-center justify-content-between mt-auto">
                                    <span class="text-muted small">
                                        <i class='bx bx-file'></i>
                                        {{ $jenisDokumen->permohonan_count }} Dokumen
                                    </span>
                                    <i class='bx bx-right-arrow-alt text-primary fs-5'></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class='bx bx-folder-open text-muted' style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">Belum Ada Jenis Dokumen</h5>
                            <p class="text-muted">Silakan tambahkan jenis dokumen terlebih dahulu.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        /* Arsip Card Styling */
        .arsip-card {
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .arsip-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #4e73df 0%, #224abe 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .arsip-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .arsip-card:hover::before {
            transform: scaleX(1);
        }

        .arsip-icon-wrapper {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .arsip-icon-wrapper i {
            font-size: 26px;
            color: white;
        }

        .arsip-card:hover .arsip-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .arsip-card .card-title {
            font-weight: 600;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .arsip-card:hover .card-title {
            color: #4e73df !important;
        }

        .arsip-card .bx-right-arrow-alt {
            transition: transform 0.3s ease;
        }

        .arsip-card:hover .bx-right-arrow-alt {
            transform: translateX(5px);
        }

        /* Badge styling */
        .badge {
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .arsip-card {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection
