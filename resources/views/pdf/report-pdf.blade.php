<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $company['company_name'] ?? 'Company' }} — Laporan Operasional — {{ $year }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Margin kertas */
        @page {
            margin: 2.5cm 3cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            font-size: 12pt;
            line-height: 1.5;
            padding: 0 20px;
        }

        /* === WATERMARK (selalu di semua halaman) === */
        .wm {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 65%;
            /* atur besar watermark */
            max-width: 500px;
            /* batas maksimum agar tidak kebesaran */
            opacity: 0.06;
            /* transparansi ringan */
            z-index: 0;
            /* di belakang konten */
            pointer-events: none;
            /* tidak mengganggu seleksi/klik */
        }

        .wm img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
            filter: grayscale(100%);
            /* opsional: bikin samar */
        }

        /* Pastikan konten di atas watermark */
        .page-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        .report-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .report-meta {
            font-size: 11pt;
            line-height: 1.6;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-header {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 12px;
            margin-top: 20px;
        }

        .subsection-header {
            font-size: 12pt;
            font-weight: bold;
            margin: 12px 0 8px;
        }

        p {
            text-align: justify;
            margin-bottom: 10px;
        }

        .summary-paragraph {
            text-indent: 40px;
            margin-bottom: 12px;
        }

        .inline-stat {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 11pt;
        }

        th {
            background: #000;
            color: #fff;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #000;
        }

        td {
            border: 1px solid #000;
            padding: 8px;
        }

        tbody tr:nth-child(even) {
            background: #f5f5f5;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .table-label {
            font-weight: bold;
        }

        .table-caption {
            font-size: 10pt;
            font-style: italic;
            text-align: center;
            margin-top: 5px;
            margin-bottom: 20px;
        }

        .chart-figure {
            margin: 20px 0;
            text-align: center;
        }

        .chart-img {
            max-width: 100%;
            height: auto;
            border: 1px solid #000;
            margin: 10px auto;
            display: block;
        }

        .figure-caption {
            font-size: 10pt;
            font-style: italic;
            margin-top: 8px;
        }

        .note-box {
            border: 1px solid #000;
            padding: 10px;
            margin: 15px 0;
            background: #f9f9f9;
            font-size: 10pt;
        }

        .page-break {
            page-break-before: always;
        }

        .document-footer {
            margin-top: 40px;
            padding-top: 10px;
            border-top: 1px solid #000;
            font-size: 9pt;
            text-align: center;
        }

        ul {
            margin-left: 40px;
            margin-bottom: 12px;
        }

        li {
            margin-bottom: 5px;
        }

        /* Supaya header tetap rapih tanpa logo di atas */
        .logo-container,
        .logo {
            display: none;
        }
    </style>
</head>

<body>

    {{-- WATERMARK: pilih prioritas dataURI, fallback ke URL --}}
    @php
        $wm_src = $company_logo;
    @endphp
    @if(!empty($wm_src))
        <div class="wm">
            <img src="{{ $wm_src }}" alt="Watermark Logo">
        </div>
    @endif

    <div class="page-content">

        {{-- Header (tanpa logo, karena logo jadi watermark tengah) --}}
        <div class="report-header">
            <div class="company-name">{{ $company['company_name'] ?? 'Company' }}</div>
            <div class="report-title">Laporan Operasional</div>
            <div class="report-meta">
                Periode: {{ $year }}<br>
                Disusun oleh: {{ $generated_by }}<br>
                Tanggal: {{ $generated_at }}
            </div>
        </div>

        {{-- Executive Summary --}}
        <div class="section">
            <h1 class="section-header">1. RINGKASAN EKSEKUTIF</h1>

            <p class="summary-paragraph">
                Dokumen ini merangkum aktivitas operasional
                <strong>{{ $company['company_name'] ?? 'Perusahaan' }}</strong> selama tahun
                <strong>{{ $year }}</strong> pada lima kategori: Peminjaman Ruangan, Peminjaman Kendaraan,
                Tiket Support, Buku Tamu, dan Pengantaran (Delivery).
            </p>

            <p class="summary-paragraph">
                Total aktivitas tercatat sebanyak
                <span class="inline-stat">{{ number_format($analysis['kpi']['overall_total'] ?? 0) }}</span>
                dengan rata-rata
                <span class="inline-stat">{{ number_format($analysis['kpi']['avg_per_month'] ?? 0, 2) }}</span>
                per bulan.
                @php $gy = $analysis['kpi']['growth_yoy']['overall'] ?? null; @endphp
                @if(!is_null($gy))
                    Dibanding tahun sebelumnya ({{ $year - 1 }}), terjadi perubahan sebesar
                    <span class="inline-stat">{{ $gy }}%</span>.
                @else
                    Data perbandingan terhadap {{ $year - 1 }} tidak tersedia.
                @endif
            </p>

            <h2 class="subsection-header">1.1 Ringkasan Tiap Kategori</h2>
            <ul>
                <li><strong>Ruangan:</strong> {{ number_format($analysis['kpi']['total_room'] ?? 0) }} transaksi
                    @if(!is_null($analysis['kpi']['growth_yoy']['room'] ?? null))
                        ({{ $analysis['kpi']['growth_yoy']['room'] }}% vs tahun lalu)
                    @endif
                </li>
                <li><strong>Kendaraan:</strong> {{ number_format($analysis['kpi']['total_vehicle'] ?? 0) }} transaksi
                    @if(!is_null($analysis['kpi']['growth_yoy']['vehicle'] ?? null))
                        ({{ $analysis['kpi']['growth_yoy']['vehicle'] }}% vs tahun lalu)
                    @endif
                </li>
                <li><strong>Tiket Support:</strong> {{ number_format($analysis['kpi']['total_ticket'] ?? 0) }} tiket
                    @if(!is_null($analysis['kpi']['growth_yoy']['ticket'] ?? null))
                        ({{ $analysis['kpi']['growth_yoy']['ticket'] }}% vs tahun lalu)
                    @endif
                </li>
                <li><strong>Buku Tamu:</strong> {{ number_format($analysis['kpi']['total_guestbook'] ?? 0) }} entri
                    @if(!is_null($analysis['kpi']['growth_yoy']['guestbook'] ?? null))
                        ({{ $analysis['kpi']['growth_yoy']['guestbook'] }}% vs tahun lalu)
                    @endif
                </li>
                <li><strong>Delivery:</strong> {{ number_format($analysis['kpi']['total_delivery'] ?? 0) }} pengantaran
                    @if(!is_null($analysis['kpi']['growth_yoy']['delivery'] ?? null))
                        ({{ $analysis['kpi']['growth_yoy']['delivery'] }}% vs tahun lalu)
                    @endif
                </li>
            </ul>

            <table>
                <caption class="table-caption">Tabel 1. Ringkasan Aktivitas per Kategori</caption>
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">YoY (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="table-label">Ruangan</td>
                        <td class="text-right">{{ number_format($analysis['kpi']['total_room'] ?? 0) }}</td>
                        <td class="text-right">
                            {{ is_null($analysis['kpi']['growth_yoy']['room'] ?? null) ? 'N/A' : $analysis['kpi']['growth_yoy']['room'] . '%' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="table-label">Kendaraan</td>
                        <td class="text-right">{{ number_format($analysis['kpi']['total_vehicle'] ?? 0) }}</td>
                        <td class="text-right">
                            {{ is_null($analysis['kpi']['growth_yoy']['vehicle'] ?? null) ? 'N/A' : $analysis['kpi']['growth_yoy']['vehicle'] . '%' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="table-label">Tiket Support</td>
                        <td class="text-right">{{ number_format($analysis['kpi']['total_ticket'] ?? 0) }}</td>
                        <td class="text-right">
                            {{ is_null($analysis['kpi']['growth_yoy']['ticket'] ?? null) ? 'N/A' : $analysis['kpi']['growth_yoy']['ticket'] . '%' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="table-label">Buku Tamu</td>
                        <td class="text-right">{{ number_format($analysis['kpi']['total_guestbook'] ?? 0) }}</td>
                        <td class="text-right">
                            {{ is_null($analysis['kpi']['growth_yoy']['guestbook'] ?? null) ? 'N/A' : $analysis['kpi']['growth_yoy']['guestbook'] . '%' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="table-label">Delivery</td>
                        <td class="text-right">{{ number_format($analysis['kpi']['total_delivery'] ?? 0) }}</td>
                        <td class="text-right">
                            {{ is_null($analysis['kpi']['growth_yoy']['delivery'] ?? null) ? 'N/A' : $analysis['kpi']['growth_yoy']['delivery'] . '%' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Trend Analysis --}}
        <div class="section">
            <h1 class="section-header">2. ANALISIS TREN</h1>
            <p class="summary-paragraph">
                Bagian ini menampilkan pola aktivitas sepanjang tahun {{ $year }} (Gambar 1) dan perbandingan beberapa
                tahun terakhir (Gambar 2).
            </p>

            @if(!empty($img['monthly']))
                <div class="chart-figure">
                    <img src="{{ $img['monthly'] }}" class="chart-img">
                    <p class="figure-caption">Gambar 1. Distribusi Aktivitas Bulanan ({{ $year }})</p>
                </div>
            @endif

            @if(!empty($img['yearly']))
                <div class="chart-figure">
                    <img src="{{ $img['yearly'] }}" class="chart-img">
                    <p class="figure-caption">Gambar 2. Perbandingan Tahunan ({{ count($yearly['labels'] ?? []) }} Tahun)
                    </p>
                </div>
            @endif
        </div>

        {{-- MoM --}}
        <div class="section">
            <h1 class="section-header">3. PERUBAHAN BULANAN (MONTH-OVER-MONTH)</h1>
            <p class="summary-paragraph">
                Persentase perubahan dibanding bulan sebelumnya untuk melihat momentum jangka pendek.
            </p>

            @php
                $mom = $analysis['mom'] ?? [];
                $labels = $monthly['labels'] ?? [];
                $pad = fn($arr) => array_merge(['—'], $arr);
            @endphp

            <table>
                <caption class="table-caption">Tabel 2. MoM per Kategori</caption>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Overall</th>
                        <th class="text-right">Ruangan</th>
                        <th class="text-right">Kendaraan</th>
                        <th class="text-right">Tiket</th>
                        <th class="text-right">Buku Tamu</th>
                        <th class="text-right">Delivery</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labels as $i => $m)
                        <tr>
                            <td class="table-label">{{ $m }}</td>
                            <td class="text-right">@php $v = $pad($mom['overall'] ?? [])[$i] ?? '—'; @endphp
                                {{ is_null($v) ? 'N/A' : $v . '%' }}</td>
                            <td class="text-right">@php $v = $pad($mom['room'] ?? [])[$i] ?? '—'; @endphp
                                {{ is_null($v) ? 'N/A' : $v . '%' }}</td>
                            <td class="text-right">@php $v = $pad($mom['vehicle'] ?? [])[$i] ?? '—'; @endphp
                                {{ is_null($v) ? 'N/A' : $v . '%' }}</td>
                            <td class="text-right">@php $v = $pad($mom['ticket'] ?? [])[$i] ?? '—'; @endphp
                                {{ is_null($v) ? 'N/A' : $v . '%' }}</td>
                            <td class="text-right">@php $v = $pad($mom['guestbook'] ?? [])[$i] ?? '—'; @endphp
                                {{ is_null($v) ? 'N/A' : $v . '%' }}</td>
                            <td class="text-right">@php $v = $pad($mom['delivery'] ?? [])[$i] ?? '—'; @endphp
                                {{ is_null($v) ? 'N/A' : $v . '%' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Moving Average --}}
        <div class="section">
            <h1 class="section-header">4. RATA-RATA BERGERAK (3 BULAN)</h1>
            <p class="summary-paragraph">
                Rata-rata 3 bulan membantu melihat tren arah umum dengan mengurangi fluktuasi cepat bulanan.
            </p>

            <table>
                <caption class="table-caption">Tabel 3. Moving Average 3 Bulan (Overall)</caption>
                <thead>
                    <tr>
                        @foreach(($monthly['labels'] ?? []) as $m) <th class="text-center">{{ $m }}</th> @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach(($analysis['moving_avg_3'] ?? []) as $v)
                            <td class="text-center">{{ is_null($v) ? '—' : number_format($v, 2) }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Appendix A: Monthly numbers --}}
        <div class="section page-break">
            <h1 class="section-header">LAMPIRAN A: DETAIL BULANAN</h1>
            <table>
                <caption class="table-caption">Tabel A1. Volume Transaksi Bulanan</caption>
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Ruangan</th>
                        <th class="text-right">Kendaraan</th>
                        <th class="text-right">Tiket</th>
                        <th class="text-right">Buku Tamu</th>
                        <th class="text-right">Delivery</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthly['labels'] as $i => $m)
                        <tr>
                            <td class="table-label">{{ $m }}</td>
                            <td class="text-right">{{ number_format($monthly['room'][$i]) }}</td>
                            <td class="text-right">{{ number_format($monthly['vehicle'][$i]) }}</td>
                            <td class="text-right">{{ number_format($monthly['ticket'][$i]) }}</td>
                            <td class="text-right">{{ number_format($monthly['guestbook'][$i]) }}</td>
                            <td class="text-right">{{ number_format($monthly['delivery'][$i]) }}</td>
                            <td class="text-right table-label">
                                {{ number_format($monthly['room'][$i] + $monthly['vehicle'][$i] + $monthly['ticket'][$i] + $monthly['guestbook'][$i] + $monthly['delivery'][$i]) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Appendix B: Yearly totals --}}
        <div class="section">
            <h1 class="section-header">LAMPIRAN B: REKAP TAHUNAN</h1>
            <table>
                <caption class="table-caption">Tabel B1. Volume Tahunan per Kategori</caption>
                <thead>
                    <tr>
                        <th>Tahun</th>
                        <th class="text-right">Ruangan</th>
                        <th class="text-right">Kendaraan</th>
                        <th class="text-right">Tiket</th>
                        <th class="text-right">Buku Tamu</th>
                        <th class="text-right">Delivery</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($yearly['labels'] as $i => $y)
                        <tr>
                            <td class="table-label">{{ $y }}</td>
                            <td class="text-right">{{ number_format($yearly['room'][$i]) }}</td>
                            <td class="text-right">{{ number_format($yearly['vehicle'][$i]) }}</td>
                            <td class="text-right">{{ number_format($yearly['ticket'][$i]) }}</td>
                            <td class="text-right">{{ number_format($yearly['guestbook'][$i]) }}</td>
                            <td class="text-right">{{ number_format($yearly['delivery'][$i]) }}</td>
                            <td class="text-right table-label">
                                {{ number_format($yearly['room'][$i] + $yearly['vehicle'][$i] + $yearly['ticket'][$i] + $yearly['guestbook'][$i] + $yearly['delivery'][$i]) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Appendix C: SLA Ticket Performance --}}
        <div class="section page-break">
            <h1 class="section-header">LAMPIRAN C: PERFORMA PENANGANAN TIKET (SLA)</h1>

            <p class="summary-paragraph">
                Bagian ini menjelaskan seberapa cepat tim menyelesaikan tiket pada tahun <strong>{{ $year }}</strong>.
                Durasi dihitung dari tiket dibuat (<em>created_at</em>) hingga tiket berstatus
                <strong>RESOLVED/CLOSED</strong> (menggunakan <em>updated_at</em>).
                Angka waktu dalam <strong>jam</strong> — semakin kecil, semakin cepat.
            </p>

            <div class="note-box">
                <strong>Panduan Membaca:</strong>
                <ul>
                    <li><strong>Avg (Rata-rata):</strong> gambaran umum kecepatan penyelesaian.</li>
                    <li><strong>Median:</strong> nilai tengah (lebih tahan outlier).</li>
                    <li><strong>P90:</strong> 90% tiket selesai ≤ angka ini (melihat “hampir semua” kasus).</li>
                    <li><strong>SLA:</strong> target waktu penyelesaian (High 24 jam, Medium 48 jam, Low 72 jam).</li>
                    <li><strong>Tepat SLA:</strong> persentase tiket yang selesai sesuai target SLA.</li>
                    <li><strong>Penilaian:</strong> <em>Cepat</em> (≥90%), <em>Sedang</em> (70–89%), <em>Perlu
                            Perbaikan</em> (&lt;70%).</li>
                </ul>
            </div>

            {{-- C.1 Per Prioritas --}}
            <h2 class="subsection-header">C.1 Ringkasan Berdasarkan Prioritas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Prioritas</th>
                        <th class="text-right">Jumlah Tiket</th>
                        <th class="text-right">Avg (jam)</th>
                        <th class="text-right">Median (jam)</th>
                        <th class="text-right">P90 (jam)</th>
                        <th class="text-right">SLA (jam)</th>
                        <th class="text-right">Tepat SLA</th>
                        <th>Penilaian</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $prioOrder = ['high', 'medium', 'low', 'unspecified'];
                        $labelMap = ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low', 'unspecified' => 'Tidak Ditentukan'];
                    @endphp
                    @foreach($prioOrder as $p)
                        @php $st = $ticket_perf['by_priority'][$p] ?? null; @endphp
                        @if($st)
                            <tr>
                                <td class="table-label">{{ $labelMap[$p] }}</td>
                                <td class="text-right">{{ $st['count'] }}</td>
                                <td class="text-right">
                                    {{ is_null($st['avg_hours']) ? '—' : number_format($st['avg_hours'], 2) }}</td>
                                <td class="text-right">
                                    {{ is_null($st['median_hours']) ? '—' : number_format($st['median_hours'], 2) }}</td>
                                <td class="text-right">
                                    {{ is_null($st['p90_hours']) ? '—' : number_format($st['p90_hours'], 2) }}</td>
                                <td class="text-right">
                                    {{ is_null($st['sla_hours']) ? 'n/a' : number_format($st['sla_hours'], 0) }}</td>
                                <td class="text-right">
                                    {{ is_null($st['sla_hit_rate']) ? 'n/a' : (number_format($st['sla_hit_rate'], 0) . '%') }}
                                </td>
                                <td>{{ $st['grade'] ?? '—' }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            @if(!empty($ticket_perf['verdicts']))
                <p class="summary-paragraph">
                    <strong>Kesimpulan Singkat:</strong>
                    @foreach($ticket_perf['verdicts'] as $i => $v)
                        {{ $i ? ' • ' : '' }}{{ $v }}
                    @endforeach
                </p>
            @endif

            {{-- C.2 Per Admin --}}
            <h2 class="subsection-header">C.2 Performa per Admin (Berdasarkan Penugasan Terakhir)</h2>
            <p class="summary-paragraph">
                Tabel ini menampilkan <em>rata-rata</em>, <em>median</em>, dan <em>P90</em> waktu penyelesaian untuk
                setiap admin,
                serta persentase <em>tepat SLA</em> per prioritas untuk memetakan kekuatan dan area perbaikan tiap
                admin.
            </p>
            <table>
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th class="text-right">Jumlah Tiket</th>
                        <th class="text-right">Avg (jam)</th>
                        <th class="text-right">Median (jam)</th>
                        <th class="text-right">P90 (jam)</th>
                        <th>Per-Prioritas: Tepat SLA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($ticket_perf['by_admin'] ?? []) as $row)
                        <tr>
                            <td class="table-label">{{ $row['admin_name'] }}</td>
                            <td class="text-right">{{ $row['overall']['count'] }}</td>
                            <td class="text-right">
                                {{ is_null($row['overall']['avg_hours']) ? '—' : number_format($row['overall']['avg_hours'], 2) }}
                            </td>
                            <td class="text-right">
                                {{ is_null($row['overall']['median_hours']) ? '—' : number_format($row['overall']['median_hours'], 2) }}
                            </td>
                            <td class="text-right">
                                {{ is_null($row['overall']['p90_hours']) ? '—' : number_format($row['overall']['p90_hours'], 2) }}
                            </td>
                            <td>
                                @php
                                    $pkeys = ['high', 'medium', 'low', 'unspecified'];
                                    $label = ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low', 'unspecified' => '—'];
                                @endphp
                                @foreach($pkeys as $pk)
                                    @php $st = $row['by_priority'][$pk] ?? null; @endphp
                                    @if($st && !is_null($st['sla_hit_rate']))
                                        <span
                                            style="display:inline-block;padding:2px 6px;margin:2px;border:1px solid #000;font-size:10pt;">
                                            {{ $label[$pk] }}: {{ number_format($st['sla_hit_rate'], 0) }}%
                                        </span>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data penugasan/admin pada tahun ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="document-footer">
            <p><strong>{{ $company['company_name'] ?? 'Company' }}</strong> — Laporan Operasional {{ $year }}</p>
            <p>Dokumen ini bersifat rahasia dan hanya untuk keperluan internal.</p>
        </div>

        {{-- Nomor halaman Dompdf --}}
        <script type="text/php">
    if (isset($pdf)) {
      $pdf->page_text(520, 812, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", null, 10, array(0,0,0));
    }
    </script>

    </div>
</body>

</html>