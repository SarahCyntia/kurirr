<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'nama' => 'required|string|max:255',
            // 'phone' => 'required|string|max:15',
            // 'email' => 'required|email|unique:kurirs,email,' . $this->kurir->id,
            'alamat' => 'required|string|max:500',
            'keluhan' => 'required|string|max:500',
            // 'rating' => 'required|integer|min:1|max:5',
            // 'id_card_number' => 'required|string|max:20|unique:kurirs,id_card_number,' . $this->kurir->id,
            
            
        ];
    }

    public function messages()
    {
        return [
            'nama.required' => 'Nama harus diisi.',
            'phone.required' => 'Nomor telepon harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.unique' => 'Email sudah digunakan oleh kurir lain.',
            'alamat.required' => 'Alamat harus diisi.',
            // 'id_card_number.required' => 'Nomor KTP harus diisi.',
            // 'id_card_number.unique' => 'Nomor KTP sudah digunakan.',
        ];
    }
    protected $table = 'pelanggan';
}