<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
                @foreach ($sistematika as $index => $item)
                    <tr>
                        <td class="no-col">{{ $index + 1 }}</td>
                        <td><strong>{{ $item->bab_sub_bab }}</strong></td>
                        <td>{{ $item->catatan_penyempurnaan }}</td>
                    </tr>
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
                <th class="content-col">Catatan Masukan/ Saran</th>
            </tr>
        </thead>
        <tbody>
            @if ($urusan->count() > 0)
                @php
                    $currentUrusan = null;
                    $urusanIndex = 0;
                    $itemIndex = 0;
                @endphp

                @foreach ($urusan as $item)
                    @if ($currentUrusan !== $item->masterUrusan->nama_urusan)
                        @php
                            $currentUrusan = $item->masterUrusan->nama_urusan;
                            $urusanIndex++;
                            $itemIndex = 0;
                        @endphp
                        <tr class="urusan-header">
                            <td class="no-col">{{ $urusanIndex }}</td>
                            <td><strong>Urusan {{ $currentUrusan }}</strong></td>
                        </tr>
                    @endif

                    @php $itemIndex++; @endphp
                    <tr>
                        <td class="no-col">{{ $itemIndex }}.</td>
                        <td>{{ $item->catatan_masukan }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="2" class="empty-state">Tidak ada catatan masukan</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>
