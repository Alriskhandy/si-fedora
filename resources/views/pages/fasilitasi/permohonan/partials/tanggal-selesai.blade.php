{{--
    Panel tanggal selesai tahapan dan riwayat perubahan tanggal (PRD #5).
    Riwayat terlihat oleh semua pengguna yang boleh melihat permohonan (keputusan K1).
    Variabel: $permohonan (relasi tahapan.masterTahapan dan tahapan.riwayat.user sudah dimuat).
--}}
@php
    $tahapanSelesai = $permohonan->tahapan
        ->where('status', 'selesai')
        ->sortBy(fn ($item) => $item->masterTahapan?->urutan)
        ->values();
    $riwayat = $permohonan->tahapan
        ->flatMap(fn ($item) => $item->riwayat->each(fn ($row) => $row->setRelation('permohonanTahapan', $item)))
        ->sortByDesc(fn ($row) => [$row->created_at, $row->id])
        ->values();
    $bisaUbah = auth()->user()->can('tahapan.update-tanggal-selesai');
@endphp

@if ($tahapanSelesai->isNotEmpty() || $riwayat->isNotEmpty())
    <div class="card mb-4" id="tanggal-selesai-tahapan">
        <div class="card-header">
            <h5 class="mb-0"><i class='bx bx-calendar-check me-2'></i>Tanggal Selesai Tahapan</h5>
        </div>
        <div class="card-body">
            @foreach (['success' => 'success', 'info' => 'info'] as $key => $class)
                @if (session($key))
                    <div class="alert alert-{{ $class }} alert-dismissible" role="alert">
                        {{ session($key) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif
            @endforeach
            @if ($errors->has('tanggal_selesai') || $errors->has('alasan'))
                <div class="alert alert-danger">
                    @foreach (array_merge($errors->get('tanggal_selesai'), $errors->get('alasan')) as $message)
                        <div>{{ $message }}</div>
                    @endforeach
                </div>
            @endif

            @if ($tahapanSelesai->isNotEmpty())
                <div class="table-responsive mb-4">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tahapan</th>
                                <th>Tanggal Selesai</th>
                                @if ($bisaUbah)
                                    <th class="text-end">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tahapanSelesai as $item)
                                <tr>
                                    <td>{{ $item->masterTahapan?->nama_tahapan }}</td>
                                    <td>
                                        {{ $item->tanggal_selesai?->locale('id')->translatedFormat('d F Y') ?? '-' }}
                                        @if ($item->tanggal_selesai_perkiraan)
                                            <span class="badge bg-label-warning ms-1" title="Diisi otomatis dari data lama; dapat dikoreksi Admin">Perkiraan</span>
                                        @endif
                                    </td>
                                    @if ($bisaUbah)
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary js-ubah-tanggal-selesai"
                                                data-bs-toggle="modal" data-bs-target="#modalTanggalSelesai"
                                                data-action="{{ route('permohonan.tahapan.update-tanggal-selesai', [$permohonan, $item]) }}"
                                                data-id="{{ $item->id }}"
                                                data-tahapan="{{ $item->masterTahapan?->nama_tahapan }}"
                                                data-tanggal="{{ $item->tanggal_selesai?->toDateString() }}">
                                                <i class='bx bx-edit-alt me-1'></i> Ubah
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <h6 class="mb-2">Riwayat Perubahan Tanggal</h6>
            @forelse ($riwayat as $row)
                <div class="border-start border-3 ps-3 mb-3">
                    <div class="small text-muted">
                        {{ $row->created_at?->locale('id')->translatedFormat('d F Y H:i') }} · {{ $row->user?->name ?? 'Pengguna dihapus' }}
                    </div>
                    <div>
                        <strong>{{ $row->jenis_label }}</strong> tahapan {{ $row->permohonanTahapan->masterTahapan?->nama_tahapan }}:
                        {{ $row->nilai_lama?->locale('id')->translatedFormat($row->jenis === 'deadline' ? 'd F Y H:i' : 'd F Y') ?? 'belum ada' }}
                        → {{ $row->nilai_baru?->locale('id')->translatedFormat($row->jenis === 'deadline' ? 'd F Y H:i' : 'd F Y') ?? '-' }}
                    </div>
                    <div class="small">Alasan: {{ $row->alasan }}</div>
                </div>
            @empty
                <p class="text-muted small mb-0">Belum ada perubahan tanggal.</p>
            @endforelse
        </div>
    </div>

    @if ($bisaUbah)
        <div class="modal fade" id="modalTanggalSelesai" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" class="modal-content" id="formTanggalSelesai"
                    action="{{ old('permohonan_tahapan_id') ? route('permohonan.tahapan.update-tanggal-selesai', [$permohonan, old('permohonan_tahapan_id')]) : '' }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="permohonan_tahapan_id" value="{{ old('permohonan_tahapan_id') }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Tanggal Selesai <span class="js-nama-tahapan"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="tanggal_selesai">Tanggal selesai</label>
                            <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai"
                                max="{{ now()->toDateString() }}" value="{{ old('tanggal_selesai') }}" required>
                            <div class="form-text">Harus urut terhadap tanggal selesai tahapan sebelum dan sesudahnya.</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="alasan">Alasan perubahan</label>
                            <textarea class="form-control" id="alasan" name="alasan" rows="3" minlength="10" required>{{ old('alasan') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        @push('scripts')
            <script>
                $(document).on('click', '.js-ubah-tanggal-selesai', function () {
                    const form = $('#formTanggalSelesai');
                    form.attr('action', $(this).data('action'));
                    form.find('[name=permohonan_tahapan_id]').val($(this).data('id'));
                    form.find('[name=tanggal_selesai]').val($(this).data('tanggal'));
                    form.find('[name=alasan]').val('');
                    form.find('.js-nama-tahapan').text($(this).data('tahapan'));
                });

                @if (old('permohonan_tahapan_id'))
                    $(function () {
                        new bootstrap.Modal(document.getElementById('modalTanggalSelesai')).show();
                    });
                @endif
            </script>
        @endpush
    @endif
@endif
