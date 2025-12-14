<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Hasil Fasilitasi RKPD {{ $kabkota }} Tahun {{ $tahun }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        .section-desc {
            margin-bottom: 15px;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            vertical-align: middle;
        }

        td {
            padding: 8px;
            vertical-align: top;
        }

        .no-col {
            width: 5%;
            text-align: center;
        }

        .title-col {
            width: 25%;
        }

        .content-col {
            width: 70%;
        }

        .urusan-header {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .empty-state {
            text-align: center;
            font-style: italic;
            color: #666;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>Hasil Fasilitasi</h1>
        <h1>Rancangan Akhir RKPD {{ strtoupper($kabkota) }}</h1>
        <h1>Tahun {{ $tahun }}</h1>
    </div>

    <!-- Bagian I: Sistematika -->
    <div class="section-title">I. Sistematika dan Substansi Rancangan Akhir RKPD</div>
    <div class="section-desc">
        Catatan penyempurnaan terhadap sistematika dan rancangan akhir RKPD {{ $kabkota }}, sebagai berikut:
    </div>

    <table>
        <thead>
            <tr>
                <th class="no-col">No.</th>
                <th class="title-col">Bab/Sub Bab</th>
                <th class="content-col">Catatan Penyempurnaan</th>
            </tr>
        </thead>
        <tbody>
            @if ($sistematika->count() > 0)
                @php
                    // Helper function to clean HTML
                    function cleanHtml($content) {
                        if (is_object($content) && method_exists($content, 'render')) {
                            $content = $content->render();
                        }
                        $content = (string) $content;
                        $content = str_replace(['<br>', '<br/>', '<br />'], "\n", $content);
                        $content = preg_replace('/<\/p>\s*<p>/', "\n\n", $content);
                        $content = preg_replace('/<li>/', '• ', $content);
                        $content = preg_replace('/<\/li>/', "\n", $content);
                        $content = strip_tags($content);
                        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        return trim($content);
                    }

                    $counter = 1;
                    $currentBabId = null;
                    // Group items by bab and sub_bab
                    $groupedItems = [];
                    foreach ($sistematika as $item) {
                        $babId = $item->master_bab_id;
                        $subBab = $item->sub_bab ?: $item->masterBab->nama_bab ?? '-';
                        $key = $babId . '|' . $subBab;

                        if (!isset($groupedItems[$key])) {
                            $groupedItems[$key] = [
                                'bab_id' => $babId,
                                'bab_nama' => $item->masterBab->nama_bab ?? '-',
                                'sub_bab' => $subBab,
                                'catatan' => [],
                            ];
                        }
                        $groupedItems[$key]['catatan'][] = $item->catatan_penyempurnaan;
                    }
                @endphp
                @foreach ($groupedItems as $groupedItem)
                    {{-- Tampilkan header bab jika bab berubah --}}
                    @if ($currentBabId !== $groupedItem['bab_id'])
                        <tr>
                            <td class="no-col" style="background-color: #e8f4f8;"></td>
                            <td colspan="2" style="background-color: #e8f4f8;">
                                <strong>{{ $groupedItem['bab_nama'] }}</strong>
                            </td>
                        </tr>
                        @php $currentBabId = $groupedItem['bab_id']; @endphp
                    @endif

                    {{-- Tampilkan item dengan catatan digabung --}}
                    <tr>
                        <td class="no-col">{{ $counter }}</td>
                        <td>{{ $groupedItem['sub_bab'] }}</td>
                        <td>
                            @foreach ($groupedItem['catatan'] as $index => $catatan)
                                {{ $index + 1 }}. {{ cleanHtml($catatan) }}
                                @if (!$loop->last)
                                    <br><br>
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
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 85%;">Masukan/Saran</th>
                <th style="width: 10%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if ($urusan->count() > 0)
                @php
                    // Group urusan by master_urusan
                    $groupedUrusan = [];
                    foreach ($urusan as $item) {
                        $urusanId = $item->master_urusan_id;
                        $namaUrusan = $item->masterUrusan->nama_urusan ?? $item->masterUrusan->nama;
                        if (!isset($groupedUrusan[$urusanId])) {
                            $groupedUrusan[$urusanId] = [
                                'nama' => $namaUrusan,
                                'items' => [],
                            ];
                        }
                        $groupedUrusan[$urusanId]['items'][] = $item->catatan_masukan;
                    }
                @endphp

                @foreach ($groupedUrusan as $urusan)
                    {{-- Header urusan --}}
                    <tr>
                        <td colspan="3" style="background-color: #f0f0f0; font-weight: bold;">Urusan
                            {{ $urusan['nama'] }}</td>
                    </tr>

                    {{-- Items with numbering --}}
                    @foreach ($urusan['items'] as $index => $catatan)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}.</td>
                            <td>{{ cleanHtml($catatan) }}</td>
                            <td></td>
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
