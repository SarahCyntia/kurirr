<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WhatsAppRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phone' => 'required|string|regex:/^(\+62|62|0)8[1-9][0-9]{6,9}$/',
            'message' => 'required|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => 'Nomor WhatsApp harus diisi',
            'phone.regex' => 'Format nomor WhatsApp tidak valid',
            'message.required' => 'Pesan harus diisi',
            'message.max' => 'Pesan maksimal 1000 karakter'
        ];
    }
}