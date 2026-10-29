<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 11px;
            color: #666;
        }
        .meta-info {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 10px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Laporan Data Buku Perpustakaan / Kasir</p>
    </div>

    <div class="meta-info">
        <strong>Tanggal Cetak:</strong> {{ $tanggal }}<br>
        <strong>Total Buku:</strong> {{ count($books) }} data
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 30%;">Judul Buku</th>
                <th style="width: 45%;">Deskripsi</th>
                <th style="width: 20%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($books as $index => $book)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $book->title }}</td>
                <td>{{ $book->description }}</td>
                <td style="text-align: center;">{{ $book->status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">Tidak ada data buku.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis melalui Sistem pada {{ $tanggal }}
    </div>
</body>
</html>

