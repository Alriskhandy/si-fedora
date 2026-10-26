{{--
    Daftar masukan berupa catatan saja (Konsistensi & Keselarasan, Rekomendasi) di halaman input dan detail.
    Variabel: $jenis (form|rekomendasi), $items, $denganAksi (bool, kolom aksi), $denganSaya (bool), ditambah variabel halaman input ($permohonan, $bolehKelola, dst.).
--}}
@if ($items->isNotEmpty())
    @include('pages.fasilitasi.hasil-fasilitasi.partials.input-filter', ['bagian' => $jenis, 'denganSaya' => $denganSaya ?? true])
    <div class="table-responsive border rounded js-list" data-bagian="{{ $jenis }}">
        <table class="table table-hover masukan-tabel mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%">No</th>
                    <th>Catatan</th>
                    <th style="width: 18%">Oleh</th>
                    @if ($denganAksi ?? true)
                        <th style="width: 5%"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr class="js-item" data-jenis="{{ $jenis }}" data-id="{{ $item->id }}"
                        data-url="{{ route('hasil-fasilitasi.'.$jenis.'.update', [$permohonan, $item->id]) }}"
                        data-user-id="{{ $item->user_id }}" data-opd="{{ $item->user?->opd_id ? 1 : 0 }}"
                        data-urutan="{{ $item->created_at->timestamp }}" data-created-at="{{ $item->created_at->timestamp }}">
                        <td class="text-center js-no">{{ $loop->iteration }}</td>
                        <td class="catatan-cell">{!! $item->catatan !!}</td>
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
