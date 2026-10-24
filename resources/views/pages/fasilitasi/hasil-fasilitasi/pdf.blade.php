<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Hasil Fasilitasi {{ $kabkota }} Tahun {{ $tahun }}</title>
    <style>
        @page {
            margin: 21mm 25mm 23mm 19mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ── Header kiri atas ── */
        .header-left p {
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        /* ── Judul ── */
        .judul {
            text-align: center;
            margin: 16px 0 16px;
        }
        .judul p {
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.5;
        }

        /* ── Seksi ── */
        .section-title {
            margin-top: 14px;
            margin-bottom: 4px;
        }
        .section-body {
            margin: 0 0 4px 0;
            text-align: justify;
        }

        /* ── List item (a., b., 1., 2.) ── */
        .list-item {
            margin: 0 0 4px 0;
            padding-left: 1.5em;
            text-indent: -1.5em;
            text-align: justify;
        }

        /* ── Tabel ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            text-align: center;
            padding: 5px 6px;
            vertical-align: middle;
        }
        td {
            padding: 5px 6px;
            vertical-align: top;
        }
        .no-col    { width: 5%;  text-align: center; }
        .bab-col   { width: 25%; }
        .cat-col   { width: 70%; }
        .ket-col   { width: 20%; }
        .mas-col   { width: 75%; }
        .bab-header  { }
        .urus-header { }
        .empty-state { text-align: center; font-style: italic; color: #666; }

        /* Cegah pemisahan baris tabel */
        thead { display: table-header-group; }
        tr    { page-break-inside: avoid; }

        /* Konten HTML TinyMCE */
        td p            { margin: 0; padding: 0; line-height: 1.3; }
        td ul, td ol    { margin: 0; padding-left: 16px; }
        td li           { margin: 0; padding: 0; line-height: 1.3; }
        td strong, td b { font-weight: bold; }
        td em, td i     { font-style: italic; }
        td u            { text-decoration: underline; }
        td s, td strike { text-decoration: line-through; }

        /* Caption tabel */
        .tabel-caption {
            text-align: center;
            margin-bottom: 4px;
        }

        /* Page break */
        .page-break { page-break-before: always; }

        /* Tanda tangan (kanan bawah) */
        .ttd-wrapper {
            width: 100%;
            margin-top: 24px;
        }
        .ttd-wrapper table {
            border: none;
            width: 100%;
        }
        .ttd-wrapper td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .ttd-cell {
            text-align: center;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .ttd-cell p {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>

@php
    // Variabel bantu
    $bulanId = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    // Grouping sistematika
    $groupedSist  = [];
    foreach ($sistematika as $item) {
        $babId  = $item->master_bab_id;
        $subBab = $item->sub_bab ?: ($item->masterBab->nama_bab ?? '-');
        $key    = $babId . '|' . $subBab;
        if (!isset($groupedSist[$key])) {
            $groupedSist[$key] = [
                'bab_id'   => $babId,
                'bab_nama' => $item->masterBab->nama_bab ?? '-',
                'sub_bab'  => $subBab,
                'catatan'  => [],
            ];
        }
        $groupedSist[$key]['catatan'][] = $item->catatan_penyempurnaan;
    }

    // Grouping urusan
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

{{-- ── Header kiri atas ── --}}
<div class="header-left">
    <p>Lampiran</p>
    <p>Nomor&nbsp;&nbsp;&nbsp;:</p>
    <p>Tanggal : {{ $tanggalGenerate }}</p>
</div>

{{-- ── Judul ── --}}
<div class="judul">
    <p>HASIL FASILITASI</p>
    <p>RANCANGAN AKHIR {{ $jenisDokumen }} {{ strtoupper($jenisWilayah) }} {{ strtoupper($kabkota) }}</p>
    <p>TAHUN {{ $tahun }}</p>
</div>

{{-- ── I. PENDAHULUAN ── --}}
<div class="section-title">I.&nbsp;&nbsp;&nbsp;PENDAHULUAN</div>
<p class="list-item">a.&nbsp;&nbsp;&nbsp;Bahwa Sesuai amanat pasal 102 dalam Peraturan Menteri Dalam Negeri Nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian Dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang Rencana Pembangunan Jangka Panjang Daerah Dan Rencana Pembangunan Jangka Menengah Daerah, Serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah, Dan Rencana Kerja Pemerintah Daerah disebutkan bahwa Gubernur dan Bupati/Walikota menyampaikan Rancangan Perkada RKPD Provinsi dan Kabupaten/Kota untuk difasilitasi;</p>
<p class="list-item">b.&nbsp;&nbsp;&nbsp;Fasilitasi sebagaimana dimaksud merupakan tindakan pembinaan berupa pemberian pedoman dan petunjuk teknis, arahan, bimbingan teknis, supervisi, asistensi dan kerja sama serta monitoring dan evaluasi yang dilakukan oleh Gubernur kepada kabupaten/kota terhadap materi muatan rancangan produk hukum daerah berbentuk peraturan sebelum ditetapkan.</p>

{{-- ── II. DASAR HUKUM ── --}}
<div class="section-title">II.&nbsp;&nbsp;&nbsp;DASAR HUKUM</div>
<p class="list-item">a.&nbsp;&nbsp;&nbsp;Undang-Undang Nomor 25 Tahun 2004 tentang Sistem Perencanaan Pembangunan Nasional (Lembaran Negara Republik Indonesia Tahun 2004 Nomor 104, Tambahan Lembaran Negara Republik Indonesia Nomor 4421);</p>
<p class="list-item">b.&nbsp;&nbsp;&nbsp;Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintah Daerah (Lembaran Negara Republik Indonesia Tahun 2014 Nomor 244 Tambahan Lembaran Negara Republik Indonesia Nomor 5587) sebagaimana telah beberapa kali diubah, terakhir dengan Undang-Undang Nomor 9 Tahun 2015 tentang Perubahan Kedua atas Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah (Lembaran Negara Republik Indonesia tahun 2015 Nomor 58, Tambahan Lembar Negara Republik Indonesia Nomor 5679);</p>
<p class="list-item">c.&nbsp;&nbsp;&nbsp;Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah (Lembaran Negara Republik Indonesia Tahun 2019 Nomor 42, Tambahan Lembaran Negara Republik Indonesia Nomor 6322);</p>
<p class="list-item">d.&nbsp;&nbsp;&nbsp;Peraturan Menteri Dalam Negeri Nomor 80 Tahun 2015 tentang Produk Hukum Daerah (Berita Negara Republik Indonesia Tahun 2015 Nomor 2036) sebagaimana telah diubah dengan Peraturan Menteri Dalam Negeri Nomor 120 Tahun 2018 tentang Perubahan atas Peraturan Menteri Dalam Negeri Nomor 80 Tahun 2015 tentang Pembentukan Produk Hukum Daerah (Berita Negara Republik Indonesia Tahun 2021 Nomor 157);</p>
<p class="list-item">e.&nbsp;&nbsp;&nbsp;Peraturan Menteri Dalam Negeri Nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian Dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang Rencana Pembangunan Jangka Panjang Daerah Dan Rencana Pembangunan Jangka Menengah Daerah, Serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah, Dan Rencana Kerja Pemerintah Daerah (Berita Negara Republik Indonesia Tahun 2017 Nomor 1312);</p>
<p class="list-item">f.&nbsp;&nbsp;&nbsp;Peraturan Menteri Dalam Negeri Nomor 70 Tahun 2019 tentang Sistem Informasi Pemerintahan Daerah (Berita Negara Republik Indonesia Tahun 2019 Nomor 1114);</p>
<p class="list-item">g.&nbsp;&nbsp;&nbsp;Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 10 Tahun 2025 tentang Pedoman Penyusunan Rencana Kerja Pemerintah Daerah Tahun 2026;</p>
<p class="list-item">h.&nbsp;&nbsp;&nbsp;Instruksi Menteri Dalam Negeri Nomor 2 Tahun 2025 tentang Pedoman Penyusunan Rencana Pembangunan Jangka Menengah Daerah dan Rencana Strategis Perangkat Daerah Tahun 2025-2029;</p>
<p class="list-item">i.&nbsp;&nbsp;&nbsp;Keputusan Menteri Dalam Negeri Nomor 900.1.15.5-3406 Tahun 2024 tentang Perubahan Atas Keputusan Menteri Dalam Negeri Nomor 050-5889 Tahun 2021 tentang Hasil Verifikasi, Validasi dan Inventarisasi Pemutakhiran Klasifikasi, Kodefikasi dan Nomenklatur Perencanaan Pembangunan dan Keuangan Daerah.</p>

{{-- ── III. MAKSUD DAN TUJUAN ── --}}
<div class="section-title">III.&nbsp;&nbsp;&nbsp;MAKSUD DAN TUJUAN</div>
<p class="section-body">Maksud dan tujuan dari fasilitasi Rancangan Perkada tentang {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }} Tahun {{ $tahun }} adalah memberikan masukan dan saran penyempurnaan terhadap Rancangan Akhir {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }} Tahun {{ $tahun }} sehingga kebijakan perencanaan pembangunan tahunan {{ $jenisWilayah }} {{ $kabkota }} lebih berkualitas yang mengarah kepada semakin baiknya kinerja pembangunan daerah.</p>

{{-- ── IV. KELENGKAPAN PERSYARATAN FASILITASI ── --}}
<div class="section-title">IV.&nbsp;&nbsp;&nbsp;KELENGKAPAN PERSYARATAN FASILITASI</div>
<p class="section-body">Fasilitasi terhadap Rancangan Perkada {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }} Tahun {{ $tahun }} dapat dilaksanakan berdasarkan persyaratan yang diterima. Selanjutnya telah dilakukan pemeriksaan terhadap persyaratan dimaksud dengan beberapa catatan antara lain:</p>
@if ($kelengkapan->count() > 0)
    @foreach ($kelengkapan as $idx => $dok)
        <p class="list-item">{{ $idx + 1 }}.&nbsp;&nbsp;&nbsp;{{ $dok->masterKelengkapan->nama_dokumen ?? $dok->file_name ?? '-' }}</p>
    @endforeach
@else
    <p class="list-item">1.&nbsp;&nbsp;&nbsp;-</p>
@endif

{{-- ── V. HASIL FASILITASI ── --}}
<div class="section-title">V.&nbsp;&nbsp;&nbsp;HASIL FASILITASI</div>
<p class="section-body">Berdasarkan hasil pencermatan serta diskusi Tim Fasilitasi dengan Pemerintah {{ $jenisWilayah }} {{ $kabkota }} terhadap Rancangan Akhir {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }} Tahun {{ $tahun }}, terdapat beberapa hal yang perlu dilakukan penyesuaian dan penyempurnaan baik sistematika dan teknik penulisan maupun substansi dalam rancangan akhir {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }}. Beberapa hal yang perlu disempurnakan dalam {{ strtoupper($ranperkada) }} tentang {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }} Tahun {{ $tahun }}, antara lain:</p>

{{-- V.1 Sistematika --}}
<div class="section-title" style="margin-left:1em;">1.&nbsp;&nbsp;&nbsp;Sistematika dan Substansi Rancangan Akhir {{ $jenisDokumen }}</div>
<p class="list-item" style="margin-left:1em;">a.&nbsp;&nbsp;&nbsp;Sistematika Rancangan Akhir {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }} Tahun {{ $tahun }} Telah Sesuai dengan ayat (1) Pasal 79 Peraturan Menteri Dalam Negeri Nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang Rencana Pembangunan Jangka Panjang Daerah dan Rencana Pembangunan Jangka Menengah Daerah, Serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah, dan Rencana Kerja Pemerintah Daerah.</p>
<p class="list-item" style="margin-left:1em;">b.&nbsp;&nbsp;&nbsp;Catatan penyempurnaan terhadap sistematika dan substansi Rancangan Peraturan {{ $jabatanTtd }} tentang {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }} Tahun {{ $tahun }} sebagaimana pada tabel berikut:</p>

<div class="tabel-caption">
    <p>Tabel 1.1</p>
    <p>Catatan Penyempurnaan</p>
    <p>{{ ucfirst(strtolower($ranperkada)) }} tentang {{ $jenisDokumen }} Tahun {{ $tahun }}</p>
</div>

{{-- Tabel sistematika --}}
<table>
    <thead>
        <tr>
            <th class="no-col">No.</th>
            <th class="bab-col">Bab/Sub Bab</th>
            <th class="cat-col">Catatan Penyempurnaan</th>
        </tr>
    </thead>
    <tbody>
        @if (count($groupedSist) > 0)
            @php $counter = 1; $currentBab = null; @endphp
            @foreach ($groupedSist as $item)
                @if ($currentBab !== $item['bab_id'])
                    <tr>
                        <td colspan="3" class="bab-header">{{ ucwords(strtolower($item['bab_nama'])) }}</td>
                    </tr>
                    @php $currentBab = $item['bab_id']; @endphp
                @endif
                <tr>
                    <td class="no-col">{{ $counter }}</td>
                    <td class="bab-col">{{ ucwords(strtolower($item['sub_bab'])) }}</td>
                    <td class="cat-col">
                        @foreach ($item['catatan'] as $idx => $catatan)
                            @if (!$loop->first)<hr style="border:none;margin:3px 0;">@endif
                            @if (count($item['catatan']) > 1){{ $idx + 1 }}.&nbsp;@endif{{ strip_tags($catatan) }}
                        @endforeach
                    </td>
                </tr>
                @php $counter++; @endphp
            @endforeach
        @else
            <tr><td colspan="3" class="empty-state">Tidak ada catatan penyempurnaan</td></tr>
        @endif
    </tbody>
</table>

{{-- V.2 Konsistensi & Keselarasan --}}
<div class="section-title" style="margin-left:1em;">2.&nbsp;&nbsp;&nbsp;Konsistensi dan Keselarasan Perencanaan Pembangunan</div>
@if ($form->count() > 0)
    @foreach ($form as $idx => $item)
        @php
            $letter = chr(96 + $idx + 1);
            $catatanHtml = trim($item->catatan ?? '');
            $catatanHtml = preg_replace('/^(<p>(\s|&nbsp;)*<\/p>\s*)+/i', '', $catatanHtml);
            $catatanHtml = preg_replace('/^<p>/i', '', $catatanHtml);
            $catatanHtml = preg_replace('/<\/p>$/i', '', $catatanHtml);
        @endphp
        <p class="list-item" style="margin-left:1em;">{{ $letter }}.&nbsp;&nbsp;&nbsp;{!! $catatanHtml !!}</p>
    @endforeach
@else
    <p class="list-item" style="margin-left:1em;">a.&nbsp;&nbsp;&nbsp;-</p>
@endif

{{-- V.3 Urusan Pemerintahan (halaman baru) --}}
<div class="page-break"></div>
<div class="section-title" style="margin-left:1em;">3.&nbsp;&nbsp;&nbsp;Masukan Terkait Penyelenggaraan Urusan Pemerintah Daerah</div>

<table>
    <thead>
        <tr>
            <th class="no-col">No.</th>
            <th class="mas-col">Masukan/Saran</th>
            <th class="ket-col">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @if (count($groupedUrusan) > 0)
            @foreach ($groupedUrusan as $group)
                <tr>
                    <td colspan="3" class="urus-header">Urusan {{ $group['nama'] }}</td>
                </tr>
                @foreach ($group['items'] as $idx => $catatan)
                    <tr>
                        <td class="no-col">{{ $idx + 1 }}.</td>
                        <td class="mas-col">{{ strip_tags($catatan) }}</td>
                        <td class="ket-col"></td>
                    </tr>
                @endforeach
            @endforeach
        @else
            <tr><td colspan="3" class="empty-state">Tidak ada catatan masukan</td></tr>
        @endif
    </tbody>
</table>

{{-- ── VI. REKOMENDASI (halaman baru) ── --}}
<div class="page-break"></div>
<div class="section-title">VI.&nbsp;&nbsp;&nbsp;REKOMENDASI</div>
@if ($rekomendasi->count() > 0)
    @foreach ($rekomendasi as $idx => $item)
        @php
            $rekHtml = trim($item->catatan ?? '');
            $rekHtml = preg_replace('/^(<p>(\s|&nbsp;)*<\/p>\s*)+/i', '', $rekHtml);
            $rekHtml = preg_replace('/^<p>/i', '', $rekHtml);
            $rekHtml = preg_replace('/<\/p>$/i', '', $rekHtml);
        @endphp
        <p class="list-item">{{ $idx + 1 }}.&nbsp;&nbsp;&nbsp;{!! $rekHtml !!}</p>
    @endforeach
@else
    <p class="list-item">1.&nbsp;&nbsp;&nbsp;-</p>
@endif

{{-- ── VII. PENUTUP (halaman baru) ── --}}
<div class="page-break"></div>
<div class="section-title">VII.&nbsp;&nbsp;&nbsp;PENUTUP</div>
<p class="section-body">Demikian hasil fasilitasi terhadap Rancangan Perkada tentang {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }} Tahun {{ $tahun }}. Masukan dari hasil fasilitasi dijadikan penyempurnaan Rancangan Perkada tentang {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }} Tahun {{ $tahun }} sebelum ditetapkan menjadi Perkada tentang {{ $jenisDokumen }} {{ $jenisWilayah }} {{ $kabkota }} Tahun {{ $tahun }}.</p>

{{-- ── Tanda tangan kanan bawah ── --}}
<div class="ttd-wrapper" style="margin-top:32px;">
    <table>
        <tr>
            <td style="width:55%; border:none;"></td>
            <td class="ttd-cell" style="width:45%; border:none;">
                Kepala Badan Perencanaan Pembangunan Daerah<br>
                Provinsi Maluku Utara
            </td>
        </tr>
    </table>
</div>

</body>
</html>
