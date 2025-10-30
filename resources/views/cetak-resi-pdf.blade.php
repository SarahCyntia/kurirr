<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resi Pengiriman</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            /* margin: 0; */
            padding: 5px;
            /* padding: 10px; */
            background-color: white;
            width: 58mm; /* Lebar struk thermal standar */
            max-width: 58mm;
            /* margin: 0 16px; */
        }

        h3 {
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            margin-top: 0;
        }

        .section {
            display: flex;
            margin-bottom: 8px;
            /* padding: 6px 0; */
            /* border-bottom: 1px dotted #ccc; */
            font-size: 10px;
            line-height: 1.3;
             padding: 12px 0;
  border-bottom: 1px dashed #ccc;
  margin: 0 16px; /* Supaya tidak sampai ke pojok kiri/kanan */
        }

        .section:last-of-type {
            border-bottom: 2px solid #333;
            font-weight: bold;
        }

        /* .section strong {
            min-width: 20mm;
            color: #333;
            font-weight: 600;
            flex-shrink: 0;
            font-size: 9px;
            text-transform: uppercase;
        } */

        .section-value {
            flex: 1;
            word-wrap: break-word;
            font-size: 10px;
        }

        /* Styling khusus untuk No Resi */
        .section:first-of-type {
            background: #f0f0f0;
            padding: 8px 5px;
            border: 1px solid #333;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        .section:first-of-type strong {
            font-weight: bold;
        }

        .section:first-of-type .section-value {
            font-weight: bold;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            margin: 0 16px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #333;
            font-size: 8px;
            color: #666;
        }

        /* CSS untuk Print */
        @media print {
            body {
                background-color: white !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                width: 60mm;
                margin: 0;
                padding: 5mm;
                
            }
            
            .section {
                break-inside: avoid;
                 padding: 12px 0;
  border-bottom: 1px dashed #ccc;
  margin: 0 16px; /* Supaya tidak sampai ke pojok kiri/kanan */
            }
        }

        @page {
    size: 60mm 120mm; /* Lebar 60mm, tinggi 100mm */
    margin: 0;
}
    </style>
</head>
<body>
    <h3>Struk Resi Pengiriman</h3>

    {{-- <div class="section" style="text-align:center; margin-top:8px;">
    @php
        $barcode = new DNS1D();
        $barcode->setStorPath(storage_path('framework/barcodes/'));
    @endphp

    <img
        src="data:image/png;base64,{{ base64_encode($barcode->getBarcodePNG($data->no_resi, 'C128')) }}"
        alt="barcode"
        width="140"
        height="40"
    >
</div> --}}


  {{-- <div class="section" style="text-align:center; margin-top:8px;">
    <img
        src="data:image/png;base64,{{ base64_encode(DNS1D::getBarcodePNG($data->no_resi, 'C128')) }}"
        alt="barcode"
        width="140"
        height="40"
    >
</div> --}}
    
    <div class="section">
        <strong>No Resi:</strong> 
        <div class="section-value">{{ $data->no_resi }}</div>
    </div>
    <div class="section">
        <strong>Pengirim:</strong> 
        <div class="section-value">{{ $data->nama_pengirim }}</div>
    </div>
    <div class="section">
        <strong>Penerima:</strong> 
        <div class="section-value">{{ $data->nama_penerima }}</div>
    </div>
    <div class="section">
        <strong>Alamat:</strong> 
        <div class="section-value">{{ $data->alamat_penerima }}</div>
    </div>
    <div class="section">
        <strong>No HP:</strong> 
        <div class="section-value">{{ $data->no_telp_penerima }}</div>
    </div>
    <div class="section">
        <strong>Ekspedisi:</strong> 
        <div class="section-value">{{ $data->ekspedisi }}</div>
    </div>
    <div class="section">
        <strong>Status:</strong> 
        <div class="section-value">{{ $data->status }}</div>
    </div>

    <div class="footer">
        Terima kasih
    </div>
</body>
</html>