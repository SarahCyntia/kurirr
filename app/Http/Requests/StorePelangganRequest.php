<?php

namespace App\Http\Requests;

use App\Models\Pelanggan;
use Illuminate\Foundation\Http\FormRequest;

class StorePelangganRequest extends FormRequest
{

    // public function store(StorePelangganRequest $request)
    // {
    //     $validated = $request->validated(); // Debugging

    //     $pelanggan = Pelanggan::create($validated);

    //     return response()->json([
    //         'message' => 'Pelanggan berhasil ditambahkan',
    //         'data' => $pelanggan
    //     ], 201);
    // }
    // /**
    //  * Menentukan apakah pengguna berwenang membuat permintaan ini.
    //  */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Mendefinisikan aturan validasi untuk permintaan ini.
     */
    public function rules(): array
    {
        return [
            // 'name' => 'required|string|max:255',
            // 'phone' => 'required|string|max:20|unique:pelanggans,phone',
            // 'email' => 'nullable|email|max:255|unique:pelanggans,email',
            'alamat' => 'nullable|string|max:500',
            'keluhan' => 'nullable|string',
            // 'orderan' => 'nullable|string',
            // 'rating' => 'nullable|integer|min:1|max:5',
        ];
    }

    /**
     * Kustomisasi pesan error untuk validasi.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.unique' => 'Nomor telepon sudah digunakan.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            // 'rating.integer' => 'Rating harus berupa angka.',
            // 'rating.min' => 'Rating minimal 1.',
            // 'rating.max' => 'Rating maksimal 5.',
        ];
    }
    protected $table = 'pelanggan';
}
