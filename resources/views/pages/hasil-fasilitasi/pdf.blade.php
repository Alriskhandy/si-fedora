<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Hasil Fasilitasi {{ $kabkota }} Tahun {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 13pt;
            font-weight: bold;
            margin: 4px 0;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        .section-desc {
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 6px 8px;
            vertical-align: middle;
        }

        td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .no-col    { width: 5%;  text-align: center; }
        .bab-col   { width: 25%; }
        .cat-col   { width: 70%; }
        .ket-col   { width: 20%; }
        .mas-col   { width: 75%; }

        .bab-header  { background-color: #e8f4f8; }
        .urus-header { background-color: #f0f0f0; font-weight: bold; }
        .empty-state { text-align: center; font-style: italic; color: #666; }

        /* Cegah judul seksi terpisah dari tabel di halaman berbeda */
        .section-title { page-break-after: avoid; }
        .section-desc  { page-break-after: avoid; }

        /* Cegah header tabel terpisah dari baris isi */
        thead { display: table-header-group; page-break-after: avoid; }
        tr    { page-break-inside: avoid; }

        /* Styling untuk konten HTML dari TinyMCE — spacing diperketat */
        td p            { margin: 0; padding: 0; line-height: 1.3; }
        td ul, td ol    { margin: 0; padding-left: 16px; }
        td li           { margin: 0; padding: 0; line-height: 1.3; }
        td strong, td b { font-weight: bold; }
        td em, td i     { font-style: italic; }
        td u            { text-decoration: underline; }
        td s, td strike { text-decoration: line-through; }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>Hasil Fasilitasi</h1>
        <h1>Rancangan Akhir {{ strtoupper($kabkota) }}</h1>
        <h1>Tahun {{ $tahun }}</h1>
    </div>

    <!-- Bagian I: Sistematika -->
    <div class="section-title">I. Sistematika dan Substansi Rancangan Akhir</div>
    <div class="section-desc">
        Catatan penyempurnaan terhadap sistematika dan substansi sebagai berikut:
    </div>

    <table>
        <thead>
            <tr>
                <th class="no-col">No.</th>
                <th class="bab-col">Bab/Sub Bab</th>
                <th class="cat-col">Catatan Penyempurnaan</th>
            </tr>
        </thead>
        <tbody>
            @if ($sistematika->count() > 0)
                @php
                    $counter    = 1;
                    $currentBab = null;
                    $grouped    = [];

                    foreach ($sistematika as $item) {
                        $babId  = $item->master_bab_id;
                        $subBab = $item->sub_bab ?: ($item->masterBab->nama_bab ?? '-');
                        $key    = $babId . '|' . $subBab;

                        if (!isset($grouped[$key])) {
                            $grouped[$key] = [
                                'bab_id'   => $babId,
                                'bab_nama' => $item->masterBab->nama_bab ?? '-',
                                'sub_bab'  => $subBab,
                                'catatan'  => [],
                            ];
                        }
                        $grouped[$key]['catatan'][] = $item->catatan_penyempurnaan;
                    }
                @endphp

                @foreach ($grouped as $item)
                    @if ($currentBab !== $item['bab_id'])
                        <tr>
                            <td class="no-col bab-header"></td>
                            <td colspan="2" class="bab-header">
                                <strong>{{ $item['bab_nama'] }}</strong>
                            </td>
                        </tr>
                        @php $currentBab = $item['bab_id']; @endphp
                    @endif

                    <tr>
                        <td class="no-col">{{ $counter }}</td>
                        <td class="bab-col">{{ $item['sub_bab'] }}</td>
                        <td class="cat-col">
                            @foreach ($item['catatan'] as $idx => $catatan)
                                @if (count($item['catatan']) > 1)
                                    <strong>{{ $idx + 1 }}.</strong>
                                @endif
                                {!! $catatan !!}
                                @if (!$loop->last)
                                    <hr style="border:none; margin: 4px 0;">
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    @php $counter++; @endphp
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="empty-state">Tidak ada catatan penyempurnaan</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Bagian II: Urusan Pemerintahan -->
    <div class="section-title">II. Masukan terkait penyelenggaraan urusan Pemerintah Daerah</div>
    <div class="section-desc">
        Masukan terkait penyelenggaraan urusan Pemerintah Daerah sebagai berikut:
    </div>

    <table>
        <thead>
            <tr>
                <th class="no-col">No.</th>
                <th class="mas-col">Masukan/Saran</th>
                <th class="ket-col">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if ($urusan->count() > 0)
                @php
                    $groupedUrusan = [];
                    foreach ($urusan as $item) {
                        $id = $item->master_urusan_id;
                        if (!isset($groupedUrusan[$id])) {
                            $groupedUrusan[$id] = [
                                'nama'  => $item->masterUrusan->nama_urusan ?? $item->masterUrusan->nama ?? '-',
                                'items' => [],
                            ];
                        }
                        $groupedUrusan[$id]['items'][] = $item->catatan_masukan;
                    }
                @endphp

                @foreach ($groupedUrusan as $group)
                    <tr>
                        <td colspan="3" class="urus-header">Urusan {{ $group['nama'] }}</td>
                    </tr>
                    @foreach ($group['items'] as $idx => $catatan)
                        <tr>
                            <td class="no-col">{{ $idx + 1 }}.</td>
                            <td class="mas-col">{!! $catatan !!}</td>
                            <td class="ket-col"></td>
                        </tr>
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="empty-state">Tidak ada catatan masukan</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>
