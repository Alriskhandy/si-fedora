@push('styles')
    <!-- CSS untuk hover effect -->
    <style>
        .step-clickable:hover .step-circle {
            transform: scale(1.1);
            box-shadow: 0 10px 25px rgba(33, 150, 243, 0.4) !important;
        }

        .step-clickable:hover .step-name {
            color: #1976D2 !important;
        }

        .step-disabled .step-circle {
            background: #F5F5F5 !important;
            border: 2px solid #E0E0E0 !important;
            color: #BDBDBD !important;
        }

        .step-disabled .step-name {
            color: #9E9E9E !important;
        }

        .step-item {
            transition: all 0.3s ease;
        }
    </style>
@endpush

<div class="progress-tracker-navigation">
    @php
        $steps = $permohonan->getProgressSteps();
        $currentIndex = $permohonan->getCurrentStepIndex();

        // Map tahapan ke tab ID
        $tahapanMap = [
            'Permohonan' => 'permohonan',
            'Verifikasi' => 'verifikasi',
            'Penetapan Jadwal' => 'jadwal',
            'Pelaksanaan' => 'pelaksanaan',
            'Hasil Fasilitasi' => 'hasil',
            'Tindak Lanjut' => 'tindak-lanjut',
            'Penetapan PERDA' => 'penetapan',
        ];
    @endphp

    <!-- Desktop View - Horizontal -->
    <div class="d-none d-lg-block">
        <div class="d-flex justify-content-between align-items-start position-relative"
            style="max-width: 1000px; margin: 0 auto; padding: 0 50px;">
            @foreach ($steps as $index => $step)
                @php
                    $tabId = $tahapanMap[$step['name']] ?? strtolower(str_replace(' ', '-', $step['name']));
                    // Step Permohonan dan Verifikasi selalu bisa diklik, step lain disabled jika belum sampai tahapannya
                    $isDisabled = $index > $currentIndex && !in_array($step['name'], ['Permohonan', 'Verifikasi']);
                    $isActive = $index === $currentIndex;
                @endphp

                <div class="text-center position-relative step-item {{ $isDisabled ? 'step-disabled' : 'step-clickable' }}"
                    style="flex: 1; margin: 0 20px; cursor: {{ $isDisabled ? 'not-allowed' : 'pointer' }};"
                    @if (!$isDisabled) data-bs-toggle="tab" 
                        data-bs-target="#{{ $tabId }}"
                        role="tab" @endif>
                    @if ($index < count($steps) - 1)
                        @php
                            $nextStep = $steps[$index + 1] ?? null;
                            $isConnectorActive = $step['completed'] && ($nextStep && $nextStep['completed']);
                            $gradientColor = $isConnectorActive
                                ? 'linear-gradient(90deg, #42A5F5 0%, #64B5F6 50%, #90CAF9 100%)'
                                : ($step['completed']
                                    ? 'linear-gradient(90deg, #42A5F5 0%, #64B5F6 100%)'
                                    : '#E0E0E0');
                        @endphp
                        <div class="position-absolute top-0 start-50 translate-middle-y"
                            style="left: 50%; right: -100%; width: calc(200% + 40px); height: 4px; background: {{ $gradientColor }}; z-index: 0; margin-top: 30px; border-radius: 2px; box-shadow: {{ $step['completed'] ? '0 3px 8px rgba(66, 165, 245, 0.25)' : 'none' }};">
                        </div>
                    @endif

                    <div class="position-relative d-inline-block" style="z-index: 1;">
                        @php
                            $circleGradient = $step['completed']
                                ? 'linear-gradient(135deg, #2196F3 0%, #42A5F5 50%, #64B5F6 100%)'
                                : ($index === $currentIndex
                                    ? 'linear-gradient(135deg, #64B5F6 0%, #90CAF9 100%)'
                                    : 'linear-gradient(135deg, #E0E0E0 0%, #EEEEEE 100%)');
                            $circleShadow = $step['completed']
                                ? '0 8px 20px rgba(33, 150, 243, 0.35), 0 0 0 4px rgba(100, 181, 246, 0.2)'
                                : ($index === $currentIndex
                                    ? '0 8px 20px rgba(100, 181, 246, 0.3), 0 0 0 4px rgba(144, 202, 249, 0.25)'
                                    : '0 4px 10px rgba(224, 224, 224, 0.3)');
                        @endphp
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3 step-circle"
                            style="width: 60px; height: 60px; font-size: 28px; background: {{ $circleGradient }}; box-shadow: {{ $circleShadow }}; transition: all 0.3s ease;">
                            @if ($step['completed'])
                                <i class='bx bx-check' style="font-weight: bold;"></i>
                            @elseif($index === $currentIndex)
                                <i class='bx bx-time-five'></i>
                            @else
                                <i class='bx bx-lock-alt' style="font-size: 12px;"></i>
                            @endif
                        </div>
                    </div>

                    <div class="mt-2">
                        @php
                            $textColor = $step['completed']
                                ? '#1565C0'
                                : ($index === $currentIndex
                                    ? '#2196F3'
                                    : '#9E9E9E');
                            $textWeight = $step['completed'] || $index === $currentIndex ? '600' : '400';
                        @endphp
                        <h6 class="mb-1 step-name"
                            style="font-size: 0.9rem; color: {{ $textColor }}; font-weight: {{ $textWeight }};">
                            {{ $step['name'] }}
                        </h6>

                        @if ($index === $currentIndex && !$step['completed'])
                            <div class="mt-2">
                                <span class="badge bg-warning d-block mb-1">Sedang Berjalan</span>
                            </div>
                        @endif

                        @if ($step['date'])
                            <small class="text-muted" style="font-size: 0.75rem;">
                                {{ \Carbon\Carbon::parse($step['date'])->format('d M Y') }}
                            </small>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Mobile View - Vertical -->
    <div class="d-block d-lg-none">
        @foreach ($steps as $index => $step)
            @php
                $tabId = $tahapanMap[$step['name']] ?? strtolower(str_replace(' ', '-', $step['name']));
                // Step Permohonan dan Verifikasi selalu bisa diklik
                $isDisabled = $index > $currentIndex && !in_array($step['name'], ['Permohonan', 'Verifikasi']);
            @endphp

            <div class="d-flex mb-3 step-item {{ $isDisabled ? 'step-disabled' : 'step-clickable' }}"
                style="cursor: {{ $isDisabled ? 'not-allowed' : 'pointer' }};"
                @if (!$isDisabled) data-bs-toggle="tab" 
                    data-bs-target="#{{ $tabId }}"
                    role="tab" @endif>
                <div class="me-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center step-circle {{ $step['completed'] ? 'bg-primary text-white' : ($index === $currentIndex ? 'bg-info text-white' : 'bg-light text-muted') }}"
                        style="width: 40px; height: 40px;">
                        @if ($step['completed'])
                            <i class='bx bx-check'></i>
                        @elseif($index === $currentIndex)
                            <i class='bx bx-time-five'></i>
                        @else
                            <i class='bx bx-circle' style="font-size: 8px;"></i>
                        @endif
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6
                        class="mb-0 step-name {{ $step['completed'] ? 'text-primary fw-semibold' : ($index === $currentIndex ? 'text-info fw-semibold' : 'text-muted') }}">
                        {{ $step['name'] }}
                        @if ($index === $currentIndex && !$step['completed'])
                            <span class="badge bg-warning ms-1">Aktif</span>
                        @endif
                    </h6>
                    @if ($step['date'])
                        <small class="text-muted">{{ \Carbon\Carbon::parse($step['date'])->format('d M Y') }}</small>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
