<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resi Pengiriman</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }
        h3 {
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <h3>Resi Pengiriman</h3>
    
    <div class="section"><strong>No Resi:</strong> {{ $data->no_resi }}</div>
    <div class="section"><strong>Nama pengirim:</strong> {{ $data->nama_pengirim }}</div>
    <div class="section"><strong>Nama Penerima:</strong> {{ $data->nama_penerima }}</div>
    <div class="section"><strong>Alamat Penerima:</strong> {{ $data->alamat_penerima }}</div>
    <div class="section"><strong>No HP Penerima:</strong> {{ $data->no_hp_penerima }}</div>
    <div class="section"><strong>Ekspedisi:</strong> {{ $data->ekspedisi }}</div>
    <div class="section"><strong>Status:</strong> {{ $data->status }}</div>
</body>
</html>
