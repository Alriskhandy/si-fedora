@extends('layouts.app')

@section('title', 'Detail Permohonan')

@push('styles')
    <style>
        /* Disabled Tab Styling */
        .nav-tabs .nav-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Timeline Styling */
        .timeline {
            position: relative;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-icon {
            font-size: 1.2rem;
        }

        /* Sticky Progress */
        .sticky-top {
            z-index: 1020;
        }

        /* Badge adjustments */
        .nav-tabs .badge {
            font-size: 0.7rem;
            padding: 0.2em 0.4em;
        }

        /* Progress bar in card */
        .progress {
            background-color: #e9ecef;
        }

        /* Responsive tabs */
        @media (max-width: 768px) {
            .nav-tabs .nav-link {
                font-size: 0.85rem;
                padding: 0.5rem 0.3rem;
            }

            .nav-tabs .nav-link i {
                font-size: 1rem;
            }
        }

        /* Active step highlight */
        .active-step .step-circle {
            box-shadow: 0 0 0 4px rgba(33, 150, 243, 0.3) !important;
            transform: scale(1.05);
        }

        .active-step .step-name {
            color: #1565C0 !important;
            font-weight: 600 !important;
        }

        /* Fix button primary active/focus state */
        .btn-primary:active,
        .btn-primary.active,
        .btn-primary:focus,
        .btn-primary.focus {
            background-color: #7bc4e3 !important;
            border-color: #7bc4e3 !important;
            color: #0d3b4d !important;
            box-shadow: 0 0 0 0.2rem rgba(160, 217, 239, 0.5) !important;
        }

        .btn-primary:not(:disabled):not(.disabled):active,
        .btn-primary:not(:disabled):not(.disabled).active {
            background-color: #5eb5d4 !important;
            border-color: #5eb5d4 !important;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    Detail Permohonan
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Permohonan</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('permohonan.index') }}" class="btn btn-secondary">
                <i class='bx bx-arrow-back me-1'></i> Kembali
            </a>
        </div>

        <!-- Progress Tracker as Navigation -->
        <div class="card mb-4">
            <div class="card-body p-4">
                @include('permohonan.partials.progress-tracker')
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="permohonanTabsContent">
            <!-- Tab 1: Permohonan (Overview Only) -->
            <div class="tab-pane fade show active" id="permohonan" role="tabpanel" aria-labelledby="permohonan-tab">
                <div class="row">
                    <!-- Left Column: Info & Actions -->
                    <div class="col-lg-8">
                        <!-- Informasi Permohonan -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class='bx bx-info-circle me-2'></i>Informasi Permohonan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th width="45%">Kabupaten/Kota:</th>
                                                <td><strong>{{ $permohonan->kabupatenKota->nama ?? '-' }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>Tahun:</th>
                                                <td>{{ $permohonan->tahun }}</td>
                                            </tr>
                                            <tr>
                                                <th>Jenis Dokumen:</th>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        {{ $permohonan->jenisDokumen->nama ?? '-' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th width="45%">Status:</th>
                                                <td>
                                                    <span class="badge bg-{{ $permohonan->statusBadgeClass }}">
                                                        {{ strtoupper($permohonan->status_akhir) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Submit:</th>
                                                <td>{{ $permohonan->submitted_at ? $permohonan->submitted_at->format('d M Y, H:i') : 'Belum disubmit' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Dibuat:</th>
                                                <td>{{ $permohonan->created_at->format('d M Y') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Status -->
                        @if ($permohonan->status_akhir == 'belum')
                            <div class="alert alert-primary">
                                <i class='bx bx-info-circle me-2'></i>
                                <strong>Status:</strong> Permohonan telah dibuat. Silakan lengkapi dokumen persyaratan di
                                tab <strong>Verifikasi</strong> dan submit permohonan Anda.
                            </div>
                        @elseif ($permohonan->status_akhir == 'proses')
                            <div class="alert alert-info">
                                <i class='bx bx-time-five me-2'></i>
                                <strong>Status:</strong> Permohonan telah disubmit dan sedang dalam proses verifikasi oleh
                                verifikator.
                            </div>
                        @endif
                    </div>

                    <!-- Right Column: Timeline -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 100px;">
                            <div class="card-header">
                                <h6 class="mb-0"><i class='bx bx-time-five me-1'></i>Timeline Aktivitas</h6>
                            </div>
                            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                @if ($permohonan->activityLogs && $permohonan->activityLogs->count() > 0)
                                    <div class="timeline">
                                        @foreach ($permohonan->activityLogs->sortByDesc('created_at') as $log)
                                            <div class="timeline-item mb-3">
                                                <div class="d-flex">
                                                    <div class="timeline-icon me-2">
                                                        <i
                                                            class='bx bx-{{ $log->event == 'created' ? 'plus-circle' : 'edit' }} text-primary'></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <small class="text-muted d-block">
                                                            {{ $log->created_at->diffForHumans() }}
                                                        </small>
                                                        <p class="mb-0">{{ $log->description }}</p>
                                                        @if ($log->causer)
                                                            <small class="text-muted">oleh
                                                                {{ $log->causer->name }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class='bx bx-time-five bx-lg mb-2 d-block'></i>
                                        <p class="mb-0">Belum ada aktivitas</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Verifikasi & Dokumen -->
            <div class="tab-pane fade" id="verifikasi" role="tabpanel" aria-labelledby="verifikasi-tab">

                <!-- Upload & Kelengkapan Dokumen -->
                @include('permohonan.tabs.dokumen')

                <!-- Perpanjangan Waktu (jika dokumen belum lengkap dan batas waktu terlewat) -->
                @if (auth()->user()->hasRole(['pemohon', 'admin_peran', 'superadmin']) && $permohonan->jadwalFasilitasi)
                    @php
                        $batasWaktu = $permohonan->jadwalFasilitasi->batas_permohonan;
                        $batasWaktuTerlewat = $batasWaktu ? now()->gt($batasWaktu) : false;
                        $dokumenBelumLengkap = $permohonan->permohonanDokumen->where('is_ada', false)->count() > 0;
                    @endphp

                    @if ($batasWaktuTerlewat && $dokumenBelumLengkap)
                        <div class="mt-4">
                            @include('permohonan.tabs.perpanjangan')
                        </div>
                    @endif
                @endif
            </div>

            <!-- Tab 3: Penetapan Jadwal -->
            <div class="tab-pane fade" id="jadwal" role="tabpanel" aria-labelledby="jadwal-tab">
                @include('permohonan.tabs.jadwal')
            </div>

            <!-- Tab 4: Pelaksanaan (Dokumen Luring) -->
            <div class="tab-pane fade" id="pelaksanaan" role="tabpanel" aria-labelledby="pelaksanaan-tab">
                @include('permohonan.tabs.pelaksanaan')
            </div>

            <!-- Tab 5: Hasil Fasilitasi -->
            <div class="tab-pane fade" id="hasil" role="tabpanel" aria-labelledby="hasil-tab">
                @include('permohonan.tabs.hasil')
            </div>

            <!-- Tab 6: Tindak Lanjut -->
            <div class="tab-pane fade" id="tindak-lanjut" role="tabpanel" aria-labelledby="tindak-lanjut-tab">
                @include('permohonan.tabs.tindak-lanjut')
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize first tab as active
            let activeTab = window.location.hash || localStorage.getItem('permohonan_active_tab') || '#permohonan';

            if (activeTab && $(activeTab).length) {
                // Show the tab content
                $('.tab-pane').removeClass('show active');
                $(activeTab).addClass('show active');

                // Highlight progress step
                $('.step-item').removeClass('active-step');
                $('.step-item[data-bs-target="' + activeTab + '"]').addClass('active-step');
            }

            // Handle click on progress tracker steps
            $('.step-clickable').on('click', function() {
                const target = $(this).data('bs-target');
                if (target) {
                    // Save to localStorage and URL
                    localStorage.setItem('permohonan_active_tab', target);
                    window.location.hash = target;

                    // Remove active from all steps and tabs
                    $('.step-item').removeClass('active-step');
                    $('.tab-pane').removeClass('show active');

                    // Add active to clicked step and corresponding tab
                    $(this).addClass('active-step');
                    $(target).addClass('show active');
                }
            });

            // Save active tab to localStorage and URL
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                let tabId = $(e.target).attr('data-bs-target');
                localStorage.setItem('permohonan_active_tab', tabId);
                window.location.hash = tabId;
            });

            // Upload dokumen form handling
            $('.upload-dokumen-form').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let formData = new FormData(form[0]);
                let button = form.find('.btn-upload-trigger');
                let row = form.closest('tr');
                let dokumenId = form.data('dokumen-id');

                button.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Update file link
                            let fileCell = row.find('td').eq(1);
                            fileCell.html(`
                                    <a href="${response.file_url}" target="_blank" class="btn btn-xs btn-outline-primary" 
                                       style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                        <i class="bx bx-download" style="font-size: 0.875rem;"></i> Lihat
                                    </a>
                                `);

                            // Update status upload badge
                            let statusCell = row.find('td').eq(2);
                            statusCell.html(`
                                    <span class="badge bg-label-success" style="font-size: 0.65rem; padding: 0.2rem 0.4rem;">
                                        <i class='bx bx-check' style="font-size: 0.75rem;"></i> Tersedia
                                    </span>
                                `);

                            // Update action button to "Selesai"
                            let actionCell = row.find('td').last();
                            actionCell.html(`
                                    <span class="badge bg-success">
                                        <i class='bx bx-check'></i> Selesai
                                    </span>
                                `);

                            // Update progress bar
                            let progressText = $('.card-header small strong');
                            let currentComplete = parseInt(progressText.text().split('/')[0]) +
                                1;
                            let totalDocs = parseInt(progressText.text().split('/')[1]);
                            let newProgress = (currentComplete / totalDocs) * 100;

                            progressText.text(`${currentComplete}/${totalDocs}`);
                            progressText.removeClass('text-warning').addClass(newProgress ==
                                100 ? 'text-success' : 'text-warning');

                            $('.progress-bar').css('width', newProgress + '%')
                                .attr('aria-valuenow', newProgress);

                            // Enable submit button if 100%
                            if (newProgress == 100) {
                                let submitBtn = $('#submitPermohonanBtn');
                                if (submitBtn.length) {
                                    submitBtn.prop('disabled', false)
                                        .removeClass('btn-outline-secondary').addClass(
                                            'btn-success')
                                        .html(
                                            '<i class="bx bx-send me-1"></i>Kirim Permohonan');
                                } else {
                                    // Jika button tidak ada, replace disabled button dengan active button
                                    let form = `
                                            <form action="${$('#submitPermohonanForm').length ? $('#submitPermohonanForm').attr('action') : ''}" 
                                                  method="POST" id="submitPermohonanForm" class="d-inline">
                                                <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                                                <button type="button" class="btn btn-success w-100" id="submitPermohonanBtn">
                                                    <i class='bx bx-send me-1'></i>Kirim Permohonan
                                                </button>
                                            </form>
                                        `;
                                    $('.card-header button[disabled]').replaceWith(form);

                                    // Re-bind submit event
                                    bindSubmitPermohonan();
                                }
                            }

                            // Show success notification
                            showNotification('success', 'Dokumen berhasil diupload!');
                        }
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat upload';
                        showNotification('error', message);
                        button.prop('disabled', false).html(
                            '<i class="bx bx-upload"></i> Upload');
                    }
                });
            });

            $('.btn-upload-trigger').on('click', function() {
                $(this).closest('form').find('.file-input').click();
            });

            $('.file-input').on('change', function() {
                if (this.files.length > 0) {
                    $(this).closest('form').submit();
                }
            });

            // Notification helper
            function showNotification(type, message) {
                const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
                const icon = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';

                const notification = $(`
                        <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed" 
                             role="alert" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                            <i class='bx ${icon} me-2'></i>${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);

                $('body').append(notification);

                setTimeout(function() {
                    notification.fadeOut(function() {
                        $(this).remove();
                    });
                }, 3000);
            }

            // Function to bind submit permohonan handler
            function bindSubmitPermohonan() {
                $('#submitPermohonanBtn').off('click').on('click', function(e) {
                    e.preventDefault();

                    if (confirm(
                            'Apakah Anda yakin ingin mengirim permohonan ini? Setelah dikirim, Anda tidak dapat mengubah dokumen.'
                        )) {
                        const form = $('#submitPermohonanForm');
                        const button = $(this);
                        const originalText = button.html();

                        button.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...'
                        );

                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Accept': 'application/json'
                            },
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    showNotification('success', response.message ||
                                        'Permohonan berhasil dikirim');
                                    setTimeout(function() {
                                        location.reload();
                                    }, 1500);
                                } else {
                                    showNotification('error', response.message ||
                                        'Gagal mengirim permohonan');
                                    button.prop('disabled', false).html(originalText);
                                }
                            },
                            error: function(xhr) {
                                let message = xhr.responseJSON?.message || 'Terjadi kesalahan';
                                showNotification('error', message);
                                button.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            }

            // Initial bind for submit permohonan
            bindSubmitPermohonan();

            // ===== HANDLER UNTUK VERIFIKATOR =====
            // Handle verifikasi per dokumen dari dokumen-table
            $('.btn-verifikasi-dokumen-table').on('click', function() {
                const button = $(this);
                const dokumenId = button.data('dokumen-id');
                const status = $('.verifikasi-status[data-dokumen-id="' + dokumenId + '"]').val();
                const catatan = $('.catatan-verifikasi[data-dokumen-id="' + dokumenId + '"]').val();
                const buttonText = button.html();

                // Validasi
                if (!status || status === 'pending') {
                    alert('Silakan pilih status verifikasi terlebih dahulu');
                    return;
                }

                if (status === 'revision' && !catatan.trim()) {
                    alert('Catatan wajib diisi jika dokumen perlu revisi');
                    return;
                }

                // Disable button dan show loading
                button.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Menyimpan...');

                // Submit via AJAX
                $.ajax({
                    url: '{{ route('verifikasi.verifikasi-dokumen', $permohonan) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        dokumen_id: dokumenId,
                        status_verifikasi: status,
                        catatan: catatan
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update button to success state
                            button.removeClass('btn-primary').addClass('btn-success')
                                .html('<i class="bx bx-check-circle"></i> Selesai')
                                .prop('disabled', true);

                            // Disable dropdown dan textarea
                            const row = button.closest('tr');
                            row.find('.verifikasi-status[data-dokumen-id="' + dokumenId + '"]')
                                .prop('disabled', true);
                            row.find('.catatan-verifikasi[data-dokumen-id="' + dokumenId + '"]')
                                .prop('disabled', true);

                            // Update badge sesuai status
                            const badge = row.find('td').eq(3);
                            if (status === 'verified') {
                                badge.html('<span class="badge bg-label-success"><i class="bx bx-check"></i> Ada</span>');
                            } else if (status === 'revision') {
                                badge.html('<span class="badge bg-label-danger"><i class="bx bx-x"></i> Tidak</span>');
                            }

                            // Show notification
                            showNotification('success', 'Verifikasi dokumen berhasil disimpan');
                        }
                    },
                    error: function(xhr) {
                        // Show error
                        button.removeClass('btn-primary').addClass('btn-danger').html(
                            '<i class="bx bx-x-circle"></i> Gagal'
                        );

                        // Reset after 2 seconds
                        setTimeout(function() {
                            button.prop('disabled', false)
                                .removeClass('btn-danger')
                                .addClass('btn-primary')
                                .html(buttonText);
                        }, 2000);

                        let errorMessage = 'Terjadi kesalahan saat menyimpan verifikasi';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        showNotification('error', errorMessage);
                        console.error('Error:', errorMessage);
                    }
                });
            });

            // Show/hide catatan based on status
            $('input[name="status_verifikasi"]').on('change', function() {
                if ($(this).val() === 'revision') {
                    $('#catatanContainer').slideDown();
                    $('#catatan_verifikasi').prop('required', true);
                } else {
                    $('#catatanContainer').slideUp();
                    $('#catatan_verifikasi').prop('required', false);
                }
            });
        });
    </script>
@endpush
