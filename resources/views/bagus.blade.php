<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resi Pengiriman</title>
    <style>
        body {
    font-family: 'Arial', sans-serif;
    font-size: 14px;
    margin: 0;
    padding: 20px;
    background-color: #f5f5f5;
}

h3 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 24px;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-bottom: 2px solid #4CAF50;
    padding-bottom: 10px;
}

.section {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    padding: 12px 15px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border-left: 4px solid #4CAF50;
    transition: all 0.3s ease;
}

.section:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

.section strong {
    min-width: 160px;
    color: #2E7D32;
    font-weight: 600;
    flex-shrink: 0;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 1px;
}

/* Styling khusus untuk No Resi */
.section:first-of-type {
    background: linear-gradient(45deg, #FF6B6B, #FF8E53);
    color: white;
    border-left-color: #FF6B6B;
}

.section:first-of-type strong {
    color: white;
}

/* Styling untuk Status */
.section:last-of-type {
    border-left-color: #2196F3;
    background: linear-gradient(90deg, rgba(33, 150, 243, 0.1) 0%, white 100%);
}

.section:last-of-type strong {
    color: #1976D2;
}

/* Responsive */
@media (max-width: 600px) {
    .section {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .section strong {
        min-width: auto;
    }
}

/* CSS untuk PDF/Print */
@media print {
    body {
        background-color: white !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    
    .section {
        box-shadow: none;
        break-inside: avoid;
    }
}
        /* body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            /* padding: 10px;
        } */
        /* h3 {
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 8px;
        } */
             @page {
            size: A4;
            margin: 15mm;
        }
    </style>
</head>
<body>
    <h3>Struk Resi Pengiriman</h3>
    
    <div class="section"><strong>No Resi:</strong> {{ $data->no_resi }}</div>
    <div class="section"><strong>Nama pengirim:</strong> {{ $data->nama_pengirim }}</div>
    <div class="section"><strong>Nama Penerima:</strong> {{ $data->nama_penerima }}</div>
    <div class="section"><strong>Alamat Penerima:</strong> {{ $data->alamat_penerima }}</div>
    <div class="section"><strong>No HP Penerima:</strong> {{ $data->no_hp_penerima }}</div>
    <div class="section"><strong>Ekspedisi:</strong> {{ $data->ekspedisi }}</div>
    <div class="section"><strong>Status:</strong> {{ $data->status }}</div>
</body>
</html>
