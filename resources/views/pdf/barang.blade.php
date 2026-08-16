<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventaris Barang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.4;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #1e40af;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header p {
            font-size: 11px;
            color: #6b7280;
        }

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        thead th {
            background-color: #1e40af;
            color: #ffffff;
            font-weight: 600;
            text-align: left;
            padding: 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tbody tr:hover {
            background-color: #eff6ff;
        }

        /* Badge Jenis */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-wahana {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-peralatan {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-komponen {
            background-color: #d1fae5;
            color: #065f46;
        }

        /* Badge Kondisi */
        .kondisi-baik {
            background-color: #d1fae5;
            color: #065f46;
        }

        .kondisi-rusak {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .kondisi-dipinjam {
            background-color: #fef3c7;
            color: #92400e;
        }

        /* Nomor urut */
        .col-no {
            width: 30px;
            text-align: center;
        }

        .col-kode {
            width: 85px;
        }

        .col-jenis {
            width: 80px;
        }

        .col-jumlah {
            width: 70px;
            text-align: center;
        }

        .col-kondisi {
            width: 80px;
        }

        .text-center {
            text-align: center;
        }

        /* Footer */
        .footer {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            font-size: 9px;
            color: #9ca3af;
            display: flex;
            justify-content: space-between;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }

        /* Ringkasan */
        .summary {
            margin-bottom: 16px;
        }

        .summary-item {
            display: inline-block;
            padding: 6px 14px;
            margin-right: 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
        }

        .summary-total {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .summary-baik {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .summary-rusak {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .summary-dipinjam {
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Laporan Inventaris Barang</h1>
        <p>UKM Robotika &mdash; Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

    {{-- Ringkasan --}}
    <div class="summary">
        <span class="summary-item summary-total">
            Total: {{ $barang->count() }} item
        </span>
        <span class="summary-item summary-baik">
            Baik: {{ $barang->where('kondisi', 'Baik')->count() }}
        </span>
        <span class="summary-item summary-rusak">
            Rusak: {{ $barang->where('kondisi', 'Rusak')->count() }}
        </span>
        <span class="summary-item summary-dipinjam">
            Dipinjam: {{ $barang->where('kondisi', 'Dipinjam')->count() }}
        </span>
    </div>

    {{-- Tabel --}}
    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-kode">Kode</th>
                <th>Nama Barang</th>
                <th class="col-jenis">Jenis</th>
                <th class="col-jumlah">Jumlah</th>
                <th class="col-kondisi">Kondisi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($barang as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $item->id }}</strong></td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>
                        @php
                            $jenisClass = match($item->jenis_barang) {
                                'Wahana'    => 'badge-wahana',
                                'Peralatan' => 'badge-peralatan',
                                'Komponen'  => 'badge-komponen',
                                default     => '',
                            };
                        @endphp
                        <span class="badge {{ $jenisClass }}">{{ $item->jenis_barang }}</span>
                    </td>
                    <td class="text-center">{{ $item->jumlah }} {{ $item->satuan }}</td>
                    <td>
                        @php
                            $kondisiClass = match($item->kondisi) {
                                'Baik'     => 'kondisi-baik',
                                'Rusak'    => 'kondisi-rusak',
                                'Dipinjam' => 'kondisi-dipinjam',
                                default    => '',
                            };
                        @endphp
                        <span class="badge {{ $kondisiClass }}">{{ $item->kondisi }}</span>
                    </td>
                    <td>{{ $item->keterangan ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #9ca3af;">
                        Belum ada data barang.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <span class="footer-left">Robotics Inventory System</span>
        <span class="footer-right">Halaman 1</span>
    </div>
</body>
</html>
