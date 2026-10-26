{{--
    Masukan urusan pemerintahan (fasilitator & OPD) untuk ditinjau Kaban sebelum memutuskan (PRD #3).
    Variabel: $hasilFasilitasi (relasi hasilUrusan.masterUrusan dan hasilUrusan.user.opd sudah dimuat).
--}}
@php($jumlahOpd = $hasilFasilitasi->hasilUrusan->filter(fn ($item) => $item->user?->opd_id)->count())

<div class="card mb-4" id="masukan-urusan">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0">
            <i class="bx bx-list-ul me-1"></i> Masukan Urusan Pemerintahan
            <span class="badge bg-label-primary ms-1">{{ $hasilFasilitasi->hasilUrusan->count() }}</span>
            <span class="badge bg-label-info ms-1">{{ $jumlahOpd }} dari OPD</span>
        </h5>
        <a href="{{ route('hasil-fasilitasi.show', $hasilFasilitasi->permohonan_id) }}" class="btn btn-sm btn-outline-primary ms-auto">
            <i class="bx bx-show me-1"></i>Lihat semua bagian masukan
        </a>
        <div class="btn-group btn-group-sm" role="group" aria-label="Filter masukan">
            <button type="button" class="btn btn-outline-primary active js-filter-masukan" data-filter="semua">Semua</button>
            <button type="button" class="btn btn-outline-primary js-filter-masukan" data-filter="opd">Hanya OPD</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th width="25%">Urusan Pemerintahan</th>
                    <th>Catatan Masukan / Saran</th>
                    <th width="22%">Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hasilFasilitasi->hasilUrusan as $item)
                    <tr class="js-baris-masukan" data-opd="{{ $item->user?->opd_id ? '1' : '0' }}">
                        <td><strong>{{ $item->masterUrusan?->kode_urusan }} {{ $item->masterUrusan?->nama_urusan }}</strong></td>
                        <td>{!! is_string($item->catatan_masukan) ? $item->catatan_masukan : $item->catatan_masukan->render() !!}</td>
                        <td>
                            <small class="text-muted">
                                @include('pages.fasilitasi.hasil-fasilitasi.partials.pengirim', ['user' => $item->user])<br>
                                {{ $item->created_at?->format('d/m/Y H:i') }}
                            </small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Belum ada masukan urusan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
    <script>
        $(document).on('click', '.js-filter-masukan', function () {
            const hanyaOpd = $(this).data('filter') === 'opd';
            $('.js-filter-masukan').removeClass('active');
            $(this).addClass('active');
            $('.js-baris-masukan').each(function () {
                $(this).toggle(! hanyaOpd || $(this).data('opd') === 1);
            });
        });
    </script>
@endpush
