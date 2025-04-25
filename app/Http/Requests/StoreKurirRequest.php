<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKurirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pastikan ini 'true' agar request bisa diproses
    }

    public function rules(): array
    {
        return [
            // 'nama' => 'required|string|max:255',
            // 'phone' => 'required|string|max:15',
'             jenis_kendaraan' => 'required|in:motor,mobil',            // 'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // 'email' => 'required|email|unique:kurir,email',
            'alamat' => 'required|string|max:500',
            'penilaian' => 'required|string|max:500',
            // 'id_card_number' => 'required|string|max:20|unique:kurirs,id_card_number',
            // 'status' => 'required|in:aktif,nonaktif',
            'status' => 'required|string',
            // 'jenis_kelamin' => 'required|in:laki-laki,perempuan'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.required' => 'Nomor telepon harus diisi.',
            'address.required' => 'Alamat harus diisi.',
            'id_card_number.required' => 'Nomor KTP harus diisi.',
            'id_card_number.unique' => 'Nomor KTP sudah terdaftar.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'photo.max' => 'Ukuran gambar maksimal 2MB.',
            'status.required' => 'Status harus diisi.',
            'status.in' => 'Status harus "aktif" atau "nonaktif".'
        ];
    }
}