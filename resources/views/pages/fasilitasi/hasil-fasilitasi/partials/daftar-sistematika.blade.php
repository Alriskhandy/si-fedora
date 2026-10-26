{{--
    Daftar masukan sistematika (halaman input dan detail hasil fasilitasi).
    Variabel: $hasilFasilitasi, $permohonan, $bolehKelola (closure item => bool), $userId, $formatWaktu, $renderCatatan,
    $denganAksi (bool, kolom aksi Ubah/Hapus), $denganSaya (bool, tampilkan saringan "Masukan Saya").
--}}
@if ($hasilFasilitasi->hasilSistematika->isNotEmpty())
    @include('pages.fasilitasi.hasil-fasilitasi.partials.input-filter', [
        'bagian' => 'sistematika',
        'pilihanBagian' => $hasilFasilitasi->hasilSistematika->pluck('masterBab.nama_bab', 'master_bab_id')->filter()->all(),
        'labelBagian' => 'Bab',
        'denganUrutan' => true,
        'labelUrutan' => 'Bab',
        'denganSaya' => $denganSaya ?? true,
    ])
    <div class="table-responsive border rounded js-list" data-bagian="sistematika">
        <table class="table table-hover masukan-tabel mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 20%">Bab / Sub Bab</th>
                    <th>Catatan Penyempurnaan</th>
                    <th style="width: 18%">Oleh</th>
                    @if ($denganAksi ?? true)
                        <th style="width: 5%"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($hasilFasilitasi->hasilSistematika as $item)
                    <tr class="js-item" data-jenis="sistematika" data-id="{{ $item->id }}"
                        data-url="{{ route('hasil-fasilitasi.sistematika.update', [$permohonan, $item->id]) }}"
                        data-user-id="{{ $item->user_id }}" data-opd="{{ $item->user?->opd_id ? 1 : 0 }}"
                        data-bagian-id="{{ $item->master_bab_id }}" data-bagian-label="{{ $item->masterBab->nama_bab ?? '-' }}"
                        data-sub-bab="{{ $item->sub_bab }}" data-urutan="{{ $item->masterBab->urutan ?? 999 }}"
                        data-created-at="{{ $item->created_at->timestamp }}">
                        <td class="text-center js-no">{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $item->masterBab->nama_bab ?? '-' }}</strong>
                            @if ($item->sub_bab)
                                <br><span class="text-muted">{{ $item->sub_bab }}</span>
                            @endif
                        </td>
                        <td class="catatan-cell">{!! $renderCatatan($item->catatan_penyempurnaan) !!}</td>
                        <td>@include('pages.fasilitasi.hasil-fasilitasi.partials.input-oleh')</td>
                        @if ($denganAksi ?? true)
                            <td class="text-center">@include('pages.fasilitasi.hasil-fasilitasi.partials.input-aksi', ['bolehKelola' => $bolehKelola($item)])</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    @include('pages.fasilitasi.hasil-fasilitasi.partials.input-kosong')
@endif
