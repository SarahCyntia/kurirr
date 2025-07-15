@extends('layouts.app')

@section('title', 'SMS & WhatsApp - BinderByte Laravel')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">
            <i class="fas fa-sms"></i> SMS & WhatsApp
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-mobile-alt"></i> Kirim SMS
                </h5>
            </div>
            <div class="card-body">
                <form id="sms-form">
                    <div class="mb-3">
                        <label for="sms_phone" class="form-label">Nomor Telepon</label>
                        <input type="tel" id="sms_phone" name="phone" class="form-control" placeholder="08123456789" required>
                        <div class="form-text">Format: 08xxxxxxxxxx</div>
                    </div>
                    <div class="mb-3">
                        <label for="sms_message" class="form-label">Pesan</label>
                        <textarea id="sms_message" name="message" class="form-control" rows="4" placeholder="Tulis pesan Anda..." maxlength="160" required></textarea>
                        <div class="form-text">
                            <span id="sms_counter">0</span>/160 karakter
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Kirim SMS
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fab fa-whatsapp"></i> Kirim WhatsApp
                </h5>
            </div>
            <div class="card-body">
                <form id="whatsapp-form">
                    <div class="mb-3">
                        <label for="wa_phone" class="form-label">Nomor WhatsApp</label>
                        <input type="tel" id="wa_phone" name="phone" class="form-control" placeholder="08123456789" required>
                        <div class="form-text">Format: 08xxxxxxxxxx</div>
                    </div>
                    <div class="mb-3">
                        <label for="wa_message" class="form-label">Pesan</label>
                        <textarea id="wa_message" name="message" class="form-control" rows="4" placeholder="Tulis pesan WhatsApp Anda..." required></textarea>
                        <div class="form-text">
                            <span id="wa_counter">0</span> karakter
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fab fa-whatsapp"></i> Kirim WhatsApp
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Message History -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history"></i> Riwayat Pesan
                </h5>
            </div>
            <div class="card-body">
                <div id="message-history">
                    <div class="alert alert-info">
                        Riwayat pesan akan ditampilkan di sini
                    </div>
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
    <p class="mt-2">Mengirim pesan...</p>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Character counters
    $('#sms_message').on('input', function() {
        const length = $(this).val().length;
        $('#sms_counter').text(length);
        
        if (length > 160) {
            $('#sms_counter').addClass('text-danger');
        } else {
            $('#sms_counter').removeClass('text-danger');
        }
    });
    
    $('#wa_message').on('input', function() {
        const length = $(this).val().length;
        $('#wa_counter').text(length);
    });
    
    // SMS form
    $('#sms-form').submit(function(e) {
        e.preventDefault();
        
        const phone = $('#sms_phone').val();
        const message = $('#sms_message').val();
        
        if (!phone || !message) {
            alert('Mohon lengkapi semua field');
            return;
        }
        
        if (message.length > 160) {
            alert('Pesan SMS tidak boleh lebih dari 160 karakter');
            return;
        }
        
        sendSMS(phone, message);
    });
    
    // WhatsApp form
    $('#whatsapp-form').submit(function(e) {
        e.preventDefault();
        
        const phone = $('#wa_phone').val();
        const message = $('#wa_message').val();
        
        if (!phone || !message) {
            alert('Mohon lengkapi semua field');
            return;
        }
        
        sendWhatsApp(phone, message);
    });
});

function sendSMS(phone, message) {
    showLoading();
    
    $.ajax({
        url: '/api/message/sms',
        method: 'POST',
        data: {
            phone: phone,
            message: message
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                showAlert('SMS berhasil dikirim!', 'success');
                addToHistory('SMS', phone, message, 'success');
                $('#sms-form')[0].reset();
                $('#sms_counter').text('0');
            } else {
                showAlert('Gagal mengirim SMS: ' + response.message, 'danger');
                addToHistory('SMS', phone, message, 'failed');
            }
        },
        error: function() {
            hideLoading();
            showAlert('Terjadi kesalahan saat mengirim SMS', 'danger');
            addToHistory('SMS', phone, message, 'error');
        }
    });
}

function sendWhatsApp(phone, message) {
    showLoading();
    
    $.ajax({
        url: '/api/message/whatsapp',
        method: 'POST',
        data: {
            phone: phone,
            message: message
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                showAlert('WhatsApp berhasil dikirim!', 'success');
                addToHistory('WhatsApp', phone, message, 'success');
                $('#whatsapp-form')[0].reset();
                $('#wa_counter').text('0');
            } else {
                showAlert('Gagal mengirim WhatsApp: ' + response.message, 'danger');
                addToHistory('WhatsApp', phone, message, 'failed');
            }
        },
        error: function() {
            hideLoading();
            showAlert('Terjadi kesalahan saat mengirim WhatsApp', 'danger');
            addToHistory('WhatsApp', phone, message, 'error');
        }
    });
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('#message-history').prepend(alertHtml);
    
    // Auto remove alert after 5 seconds
    setTimeout(function() {
        $('.alert').not(':first').remove();
    }, 5000);
}

function addToHistory(type, phone, message, status) {
    const now = new Date().toLocaleString();
    const statusClass = status === 'success' ? 'text-success' : 'text-danger';
    const statusText = status === 'success' ? 'Berhasil' : 'Gagal';
    
    const historyHtml = `
        <div class="border-bottom pb-3 mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1">${type} ke ${phone}</h6>
                    <p class="mb-1">${message}</p>
                    <small class="text-muted">${now}</small>
                </div>
                <span class="badge ${statusClass}">${statusText}</span>
            </div>
        </div>
    `;
    
    // Remove initial message if exists
    $('#message-history .alert-info').remove();
    
    $('#message-history').prepend(historyHtml);
    
    // Keep only last 10 messages
    $('#message-history > div:nth-child(n+11)').remove();
}

function showLoading() {
    $('#loading').show();
}

function hideLoading() {
    $('#loading').hide();
}
</script>
@endpush
🔧 Langkah 8: Buat Middleware dan Request Validation
8.1 Buat Request Validation
bashphp artisan make:request TrackingRequest
php artisan make:request SmsRequest
php artisan make:request WhatsAppRequest
Edit file app/Http/Requests/TrackingRequest.php:
php<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'courier' => 'required|string|in:jne,pos,jnt,sicepat,tiki,anteraja,wahana,ninja,lion,pcp',
            'resi' => 'required|string|min:5|max:50'
        ];
    }

    public function messages()
    {
        return [
            'courier.required' => 'Kurir harus dipilih',
            'courier.in' => 'Kurir tidak valid',
            'resi.required' => 'Nomor resi harus diisi',
            'resi.min' => 'Nomor resi minimal 5 karakter',
            'resi.max' => 'Nomor resi maksimal 50 karakter'
        ];
    }
}