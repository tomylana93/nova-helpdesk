<?php

return [
    'failed' => 'Kredensial ini tidak cocok dengan data kami.',
    'password' => 'Kata sandi yang diberikan salah.',
    'throttle' => 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam :seconds detik.',

    'login' => [
        'title' => 'Masuk',
        'card' => [
            'heading' => 'Selamat datang kembali!',
            'description' => 'Silakan masukkan kredensial Anda untuk masuk.',
        ],
        'label' => [
            'email' => 'Email',
            'password' => 'Kata sandi',
            'remember' => 'Ingat saya',
        ],
        'action' => [
            'submit' => 'Masuk',
        ],
        'link' => [
            'forgot' => 'Lupa kata sandi',
        ],
        'message' => [
            'active' => 'Akun Anda aktif.',
            'disable' => 'Akun Anda telah dinonaktifkan. Silakan hubungi dukungan.',
            'suspend' => 'Akun Anda telah ditangguhkan. Silakan hubungi dukungan.',
            'default_password_warning' => 'Anda masih menggunakan kata sandi bawaan. Silakan ganti dari pengaturan Keamanan.',
        ],
    ],

    'forgot_password' => [
        'title' => 'Lupa kata sandi',
        'card' => [
            'heading' => 'Atur ulang kata sandi Anda',
            'description' => 'Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.',
        ],
        'label' => [
            'email' => 'Email',
        ],
        'action' => [
            'submit' => 'Kirim tautan reset kata sandi',
        ],
        'link' => [
            'login' => 'Kembali ke masuk',
        ],
    ],

    'force_password' => [
        'title' => 'Ganti kata sandi',
        'label' => [
            'password' => 'Kata sandi baru',
            'password_confirmation' => 'Konfirmasi kata sandi baru',
        ],
        'action' => [
            'submit' => 'Ganti kata sandi',
        ],
        'message' => [
            'success' => 'Kata sandi Anda telah diganti.',
        ],
    ],
];
