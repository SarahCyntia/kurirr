@extends('layouts.app')

@section('title', 'Tracking Paket - BinderByte Laravel')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">
            <i class="fas fa-search"></i> Tracking Paket
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-barcode"></i> Lacak Paket
                </h5>
            </div>
            <div class="card-body">
                <form id="tracking-form">
                    <div class="mb-3">
                        <label for="courier" class="form-label">Kurir</label>
                        <select id="courier" name="courier" class="form-select" required>
                            <option value="">Pilih Kurir</option>
                            <option value="jne">JNE</option>
                            <option value="pos">POS Indonesia</option>
                            <option value="jnt">J&T Express</option>
                            <option value="sicepat">SiCepat</option>
                            <option value="tiki">TIKI</option>
                            <option value="anteraja">AnterAja</option>
                            <option value="wahana">Wahana</option>
                            <option value="ninja">Ninja Express</option>
                            <option value="lion">Lion Parcel</option>
                            <option value="pcp">PCP Express</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="resi" class="form-label">Nomor Resi</label>
                        <input type="text" id="resi" name="resi" class="form-control" placeholder="Masukkan nomor resi" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Lacak Paket
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calculator"></i> Hitung Ongkir
                </h5>
            </div>
            <div class="card-body">
                <form id="ongkir-form">
                    <div class="mb-3">
                        <label for="origin" class="form-label">Asal</label>
                        <input type="text" id="origin" name="origin" class="form-control" placeholder="Kota asal" required>
                    </div>
                    <div class="mb-3">
                        <label for="destination" class="form-label">Tujuan</label>
                        <input type="text" id="destination" name="destination" class="form-control" placeholder="Kota tujuan" required>
                    </div>
                    <div class="mb-3">
                        <label for="weight" class="form-label">Berat (gram)</label>
                        <input type="number" id="weight" name="weight" class="form-control" placeholder="Berat paket" required>
                    </div>
                    <div class="mb-3">
                        <label for="courier_ongkir" class="form-label">Kurir</label>
                        <select id="courier_ongkir" name="courier" class="form-select" required>
                            <option value="">Pilih Kurir</option>
                            <option value="jne">JNE</option>
                            <option value="pos">POS Indonesia</option>
                            <option value="jnt">J&T Express</option>
                            <option value="sicepat">SiCepat</option>
                            <option value="tiki">TIKI</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-calculator"></i> Hitung Ongkir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Results -->
<div class="row">
    <div class="col-md-12">
        <div id="results" class="card" style="display: none;">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle"></i> Hasil
                </h5>
            </div>
            <div class="card-body">
                <div id="results-content">
                    <!-- Results will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading -->
<div id="loading" class="text-center" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Memproses request...</p>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Track package form
    $('#tracking-form').submit(function(e) {
        e.preventDefault();
        
        const courier = $('#courier').val();
        const resi = $('#resi').val();
        
        if (!courier || !resi) {
            alert('Mohon lengkapi semua field');
            return;
        }
        
        trackPackage(courier, resi);
    });
    
    // Shipping cost form
    $('#ongkir-form').submit(function(e) {
        e.preventDefault();
        
        const origin = $('#origin').val();
        const destination = $('#destination').val();
        const weight = $('#weight').val();
        const courier = $('#courier_ongkir').val();
        
        if (!origin || !destination || !weight || !courier) {
            alert('Mohon lengkapi semua field');
            return;
        }
        
        calculateShippingCost(origin, destination, weight, courier);
    });
});

function trackPackage(courier, resi) {
    showLoading();
    
    $.ajax({
        url: '/api/tracking/track',
        method: 'POST',
        data: {
            courier: courier,
            resi: resi
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                displayTrackingResults(response.data);
            } else {
                displayError('Tracking gagal: ' + response.message);
            }
        },
        error: function() {
            hideLoading();
            displayError('Terjadi kesalahan saat tracking paket');
        }
    });
}

function calculateShippingCost(origin, destination, weight, courier) {
    showLoading();
    
    $.ajax({
        url: '/api/tracking/shipping-cost',
        method: 'POST',
        data: {
            origin: origin,
            destination: destination,
            weight: weight,
            courier: courier
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                displayShippingResults(response.data);
            } else {
                displayError('Hitung ongkir gagal: ' + response.message);
            }
        },
        error: function() {
            hideLoading();
            displayError('Terjadi kesalahan saat menghitung ongkir');
        }
    });
}

function displayTrackingResults(data) {
    let html = '';
    
    if (data.status && data.history) {
        html += `
            <div class="mb-4">
                <h6>Status Paket</h6>
                <div class="alert alert-info">
                    <strong>Status:</strong> ${data.status}<br>
                    <strong>Resi:</strong> ${data.resi}<br>
                    <strong>Kurir:</strong> ${data.courier}
                </div>
            </div>
            
            <h6>Riwayat Perjalanan</h6>
            <div class="timeline">
        `;
        
        data.history.forEach(function(item, index) {
            html += `
                <div class="timeline-item ${index === 0 ? 'active' : ''}">
                    <div class="timeline-date">${item.date}</div>
                    <div class="timeline-content">
                        <strong>${item.status}</strong><br>
                        ${item.location}<br>
                        <small class="text-muted">${item.time}</small>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
    } else {
        html = '<div class="alert alert-warning">Data tracking tidak ditemukan</div>';
    }
    
    $('#results-content').html(html);
    $('#results').show();
}

function displayShippingResults(data) {
    let html = '';
    
    if (data.costs && data.costs.length > 0) {
        html += '<h6>Pilihan Layanan</h6>';
        html += '<div class="row">';
        
        data.costs.forEach(function(cost) {
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">${cost.service}</h6>
                            <p class="card-text">${cost.description}</p>
                            <div class="d-flex justify-content-between">
                                <span><strong>Rp ${cost.cost.toLocaleString()}</strong></span>
                                <span class="text-muted">${cost.etd}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
    } else {
        html = '<div class="alert alert-warning">Data ongkir tidak ditemukan</div>';
    }
    
    $('#results-content').html(html);
    $('#results').show();
}

function displayError(message) {
    $('#results-content').html(`<div class="alert alert-danger">${message}</div>`);
    $('#results').show();
}

function showLoading() {
    $('#loading').show();
    $('#results').hide();
}

function hideLoading() {
    $('#loading').hide();
}
</script>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 30px;
    margin-bottom: 20px;
    border-left: 2px solid #e9ecef;
}

.timeline-item.active {
    border-left-color: #007bff;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -6px;
    top: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #e9ecef;
}

.timeline-item.active:before {
    background: #007bff;
}

.timeline-date {
    font-weight: bold;
    color: #007bff;
    font-size: 0.9em;
}

.timeline-content {
    margin-top: 5px;
}
</style>
@endpush