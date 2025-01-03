<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Nota Belanja</title>
    <style>
        @page {
            size: 3.15in;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0.2in;
            width: auto;
            line-height: 1.2;
            font-size: 7pt;
            font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
        }

        table {
            width: 100%;
            table-layout: fixed;
        }

        th, td {
            padding: 2px 0;
            text-align: left
        }

        .title {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: -5px;
        }

        .subtitle {
            text-align: center;
            color: #71717a;
        }
    </style>
</head>
<body>
<div>
    <h1 class="title">{{ config('app.name') }}</h1>
    <p class="subtitle">{{ now()->format('d F Y H:i') }}</p>
    <hr>
    <table>
        <thead>
        <tr>
            <th style="width: 6%;">#</th>
            <th style="width: 25%;">NP</th>
            <th style="width: 35%">Nama</th>
            <th style="width: 30%; text-align:center;">Qty</th>
            <th style="text-align: right">Harga</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($order->orderDetails as $detail)
            <tr>
                <td style="width: 6%; font-size: 7px; font-family: monospace">{{ $loop->iteration }}.</td>
                <td style="width: 25%; font-size: 7px;font-family: monospace;">{{ $detail->order->order_number }}</td>
                <td style="width: 35%; font-family: monospace">{{ $detail->product->name }}</td>
                <td style="width: 30%; letter-spacing: -1.5px; font-family: 'monospace';font-size: 7px; text-align:center;">{{ $detail->quantity }} x {{ number_format($detail->price, 0, '.', '.') }}</td>
                <td style="text-align: right;font-family: 'monospace';font-size: 7px;">{{ number_format($detail->price * $detail->quantity, 0, '.', '.') }}</td>
            </tr>
        @endforeach

        <tr><td/><td/><td/><td/></tr><tr><td/><td/><td/><td/></tr>
        <tr><td/><td/><td/><td/></tr><tr><td/><td/><td/><td/></tr>

        <tr>
            <td colspan="3" style="text-align: right;">Total:</td>
            <td style="text-align: right; font-weight: bold; font-family: 'monospace'">Rp {{ number_format($order->total, 0, '.', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: right;">Diskon:</td>
            <td style="text-align: right; font-weight: bold; font-family: 'monospace'">Rp {{ number_format($order->discount ?? '0', 0, '.', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: right;">Total setelah Diskon:</td>
            <td style="text-align: right; width: 40%; font-weight: bold; font-family: 'monospace', sans-serif">
                Rp {{ number_format($order->total - $order->discount, 0, '.', '.') }}
            </td>
        </tr>
        </tbody>
    </table>
    <p style="font-size: 7px; text-align: left;margin-top: 15px;"><b>#catatan:</b>
        <br>
        - harap tidak dibuang karena nota ini sekaligus berupa garansi yang berlaku selama 1 bulan 
        <br>
        - garansi hangus jika tidak ada nota</p>
</div>
</body>
</html>