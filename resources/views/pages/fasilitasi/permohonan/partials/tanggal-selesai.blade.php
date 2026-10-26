{{--
    Panel tanggal selesai tahapan (PRD #5). Hanya untuk pemegang tahapan.update-tanggal-selesai
    (Admin PERAN, Superadmin); di-include dengan @can.

    Menampilkan semua tahapan (status, mulai, selesai, durasi). Tanggal selesai diisi otomatis oleh sistem
    dan dapat diubah langsung di baris, dengan pilihan tanggal dibatasi ke rentang yang sah (urut terhadap
    tahapan sebelum/sesudahnya, tidak di masa depan, tidak sebelum permohonan dibuat). Perubahan tetap
    tercatat di permohonan_tahapan_riwayat dan activity log, tetapi tidak ditampilkan di panel.
    Variabel: $permohonan (relasi tahapan.masterTahapan sudah dimuat).
--}}
@php
    $tahapanPerMaster = $permohonan->tahapan->keyBy('tahapan_id');
    $baris = App\Models\MasterTahapan::orderBy('urutan')->get()
        ->map(fn ($master) => ['master' => $master, 'tahapan' => $tahapanPerMaster->get($master->id)])
        ->values();

    $selesai = fn ($row) => $row['tahapan']?->status === 'selesai' && $row['tahapan']->tanggal_selesai;
    $jumlahSelesai = $baris->filter($selesai)->count();

    $hariIni = now()->startOfDay();
    $tanggalDibuat = $permohonan->created_at->copy()->startOfDay();
    $formatTanggal = fn ($tanggal) => $tanggal?->locale('id')->translatedFormat('d M Y');
    $statusLabel = [
        'selesai' => ['Selesai', 'bg-label-success'],
        'proses' => ['Proses', 'bg-label-warning'],
        'revisi' => ['Revisi', 'bg-label-danger'],
        'belum' => ['Belum', 'bg-label-secondary'],
    ];
    $barisDibuka = (int) old('permohonan_tahapan_id');
@endphp

<div class="card mb-4" id="tanggal-selesai-tahapan">
    <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h5 class="mb-1"><i class='bx bx-calendar-check me-2'></i>Kelola Tanggal</h5>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-label-primary px-3 py-2">{{ $jumlahSelesai }}/{{ $baris->count() }} tahapan selesai</span>
        </div>
    </div>

    @foreach (['success' => 'success', 'info' => 'info'] as $key => $class)
        @if (session($key))
            <div class="alert alert-{{ $class }} alert-dismissible mx-3 mt-3 mb-0" role="alert">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif
    @endforeach

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 32%">Tahapan</th>
                    <th>Mulai</th>
                    <th style="width: 30%">Selesai</th>
                    <th>Durasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($baris as $index => $row)
                    @php
                        $master = $row['master'];
                        $tahapan = $row['tahapan'];
                        [$label, $badge] = $statusLabel[$tahapan->status ?? 'belum'] ?? $statusLabel['belum'];
                        $mulai = $tahapan?->created_at;
                        $tanggalSelesai = $selesai($row) ? $tahapan->tanggal_selesai : null;

                        // Rentang sah: setelah tahapan selesai sebelumnya, sebelum tahapan selesai sesudahnya
                        $sebelum = $baris->take($index)->filter($selesai)->last();
                        $sesudah = $baris->slice($index + 1)->filter($selesai)->first();
                        $min = collect([$tanggalDibuat, $sebelum['tahapan']->tanggal_selesai ?? null])->filter()->max()->copy()->startOfDay();
                        $max = collect([$hariIni, $sesudah['tahapan']->tanggal_selesai ?? null])->filter()->min()->copy()->startOfDay();

                        $durasi = $mulai && $tanggalSelesai ? (int) $mulai->copy()->startOfDay()->diffInDays($tanggalSelesai->copy()->startOfDay()) : null;
                        $sedangDiubah = $tahapan && $barisDibuka === $tahapan->id;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $master->urutan }}. {{ $master->nama_tahapan }}</div>
                            <span class="badge {{ $badge }}">{{ $label }}</span>
                        </td>
                        <td class="text-nowrap">{{ $formatTanggal($mulai) ?? '—' }}</td>
                        <td>
                            @if ($tanggalSelesai)
                                <div class="js-tampil-tanggal {{ $sedangDiubah ? 'd-none' : '' }}">
                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                        <span class="text-nowrap">{{ $formatTanggal($tanggalSelesai) }}</span>
                                    </div>
                                    <div class="d-flex gap-1 mt-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary js-buka-ubah">
                                            <i class='bx bx-edit-alt me-1'></i>Ubah
                                        </button>
                                    </div>
                                </div>

                                <form method="POST"
                                    action="{{ route('permohonan.tahapan.update-tanggal-selesai', [$permohonan, $tahapan]) }}"
                                    class="js-form-ubah {{ $sedangDiubah ? '' : 'd-none' }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="permohonan_tahapan_id" value="{{ $tahapan->id }}">
                                    <label class="visually-hidden" for="tanggal-selesai-{{ $tahapan->id }}">Tanggal selesai {{ $master->nama_tahapan }}</label>
                                    <div class="input-group input-group-sm">
                                        <input type="date" class="form-control {{ $sedangDiubah && $errors->has('tanggal_selesai') ? 'is-invalid' : '' }}"
                                            id="tanggal-selesai-{{ $tahapan->id }}" name="tanggal_selesai"
                                            value="{{ $sedangDiubah ? old('tanggal_selesai') : $tanggalSelesai->toDateString() }}"
                                            min="{{ $min->toDateString() }}" max="{{ $max->toDateString() }}" required>
                                        <button type="submit" class="btn btn-primary" title="Simpan"><i class='bx bx-check'></i></button>
                                        <button type="button" class="btn btn-outline-secondary js-batal-ubah" title="Batal"><i class='bx bx-x'></i></button>
                                    </div>
                                    @if ($sedangDiubah && $errors->has('tanggal_selesai'))
                                        <div class="text-danger small mt-1">{{ $errors->first('tanggal_selesai') }}</div>
                                    @endif
                                    <div class="form-text">Rentang: {{ $formatTanggal($min) }} – {{ $formatTanggal($max) }}</div>
                                </form>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-nowrap">{{ $durasi !== null ? $durasi.' hari' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
    <script>
        // Ubah tanggal langsung di baris: tampilkan form, sembunyikan tampilan tanggal
        $(document).on('click', '#tanggal-selesai-tahapan .js-buka-ubah', function () {
            const cell = $(this).closest('td');
            cell.find('.js-tampil-tanggal').addClass('d-none');
            cell.find('.js-form-ubah').removeClass('d-none').find('input[type=date]').trigger('focus');
        });

        $(document).on('click', '#tanggal-selesai-tahapan .js-batal-ubah', function () {
            const cell = $(this).closest('td');
            cell.find('.js-form-ubah').addClass('d-none');
            cell.find('.js-tampil-tanggal').removeClass('d-none');
        });
    </script>
@endpush
