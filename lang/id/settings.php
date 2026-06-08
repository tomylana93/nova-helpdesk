<?php

return [
    'profile' => [
        'title' => 'Pengaturan profil',
        'heading' => 'Informasi profil',
        'description' => 'Perbarui nama, alamat email, dan avatar Anda',
        'label' => [
            'name' => 'Nama',
            'email' => 'Alamat email',
            'avatar' => 'Avatar',
        ],
        'placeholder' => [
            'name' => 'Nama lengkap',
            'email' => 'Alamat email',
        ],
        'helper' => [
            'uploader_idle' => 'Letakkan gambar di sini atau telusuri',
        ],
        'message' => [
            'upload_invalid_type' => 'Tipe file yang dipilih tidak diizinkan.',
            'upload_too_large' => 'Ukuran file yang dipilih terlalu besar.',
            'upload_failed' => 'File tidak dapat diunggah.',
            'upload_remove_failed' => 'File tidak dapat dihapus.',
        ],
        'action' => [
            'submit' => 'Simpan',
        ],
    ],
    'security' => [
        'title' => 'Pengaturan keamanan',
        'heading' => 'Perbarui kata sandi',
        'description' => 'Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman',
        'label' => [
            'current_password' => 'Kata sandi saat ini',
            'password' => 'Kata sandi baru',
            'password_confirmation' => 'Konfirmasi kata sandi',
        ],
        'placeholder' => [
            'current_password' => 'Kata sandi saat ini',
            'password' => 'Kata sandi baru',
            'password_confirmation' => 'Konfirmasi kata sandi',
        ],
        'action' => [
            'submit' => 'Simpan kata sandi',
        ],
    ],
];
