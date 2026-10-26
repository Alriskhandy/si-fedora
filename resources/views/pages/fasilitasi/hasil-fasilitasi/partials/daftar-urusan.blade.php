{{--
    Daftar masukan urusan pemerintahan, dikelompokkan per urusan (halaman input dan detail hasil fasilitasi).
    Variabel: $hasilFasilitasi, $permohonan, $bolehKelola, $userId, $formatWaktu, $renderCatatan, $denganAksi, $denganSaya,
    $denganOpd (bool, tampilkan saringan "Dari OPD").
--}}
@php
    $urusanPerKode ??= $hasilFasilitasi->hasilUrusan->groupBy('master_urusan_id')->map(fn ($items) => $items->sortBy('created_at')->values());
@endphp
@if ($urusanPerKode->isNotEmpty())
    @include('pages.fasilitasi.hasil-fasilitasi.partials.input-filter', [
        'bagian' => 'urusan',
        'pilihanBagian' => $urusanPerKode->mapWithKeys(fn ($items, $id) => [$id => $items->first()->masterUrusan->kode_urusan.' '.$items->first()->masterUrusan->nama_urusan])->all(),
        'labelBagian' => 'Urusan',
        'denganOpd' => $denganOpd ?? true,
        'denganSaya' => $denganSaya ?? true,
    ])
    <div class="js-list" data-bagian="urusan">
        @foreach ($urusanPerKode as $urusanId => $items)
            @php($masterUrusan = $items->first()->masterUrusan)
            <div class="border rounded mb-3 js-grup">
                <button type="button" class="btn w-100 d-flex justify-content-between align-items-center text-start grup-urusan-header rounded-0 px-3 py-2"
                    data-bs-toggle="collapse" data-bs-target="#grup-urusan-{{ $urusanId }}" aria-expanded="true">
                    <span class="fw-semibold">{{ $masterUrusan->kode_urusan }} {{ $masterUrusan->nama_urusan }}</span>
                    <span class="badge rounded-pill bg-label-primary js-grup-jumlah">{{ $items->count() }}</span>
                </button>
                <div class="collapse show" id="grup-urusan-{{ $urusanId }}">
                    <div class="table-responsive">
                        <table class="table table-hover masukan-tabel mb-0">
                            <tbody>
                                @foreach ($items as $item)
                                    <tr class="js-item" data-jenis="urusan" data-id="{{ $item->id }}"
                                        data-url="{{ route('hasil-fasilitasi.urusan.update', [$permohonan, $item->id]) }}"
                                        data-user-id="{{ $item->user_id }}" data-opd="{{ $item->user?->opd_id ? 1 : 0 }}"
                                        data-bagian-id="{{ $item->master_urusan_id }}"
                                        data-bagian-label="{{ $masterUrusan->kode_urusan }} {{ $masterUrusan->nama_urusan }}"
                                        data-urutan="{{ $masterUrusan->urutan_kode }}" data-created-at="{{ $item->created_at->timestamp }}">
                                        <td class="text-center js-no" style="width: 5%">{{ $loop->iteration }}</td>
                                        <td class="catatan-cell">{!! $renderCatatan($item->catatan_masukan) !!}</td>
                                        <td style="width: 18%">@include('pages.fasilitasi.hasil-fasilitasi.partials.input-oleh')</td>
                                        @if ($denganAksi ?? true)
                                            <td class="text-center" style="width: 5%">@include('pages.fasilitasi.hasil-fasilitasi.partials.input-aksi', ['bolehKelola' => $bolehKelola($item)])</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    @include('pages.fasilitasi.hasil-fasilitasi.partials.input-kosong')
@endif
