<?php

return [
    'settings' => [
        'general' => [
            'title' => 'Pengaturan Umum',
            'heading' => 'Pengaturan Umum',
            'description' => 'Atur nama situs, deskripsi, dan bahasa.',
            'action' => [
                'submit' => 'Simpan perubahan',
            ],
            'status' => [
                'saved' => 'Tersimpan.',
            ],
            'label' => [
                'site_name' => 'Nama Situs',
                'site_description' => 'Deskripsi Situs',
                'locale' => 'Bahasa',
            ],
            'placeholder' => [
                'site_name' => 'Masukkan nama situs',
                'site_description' => 'Masukkan deskripsi situs',
                'locale' => 'Pilih bahasa situs',
            ],
        ],
        'style' => [
            'title' => 'Pengaturan Tampilan',
            'heading' => 'Pengaturan Tampilan',
            'description' => 'Sesuaikan tampilan panel admin.',
            'branding' => [
                'heading' => 'Aset Branding',
                'description' => 'Unggah aset ikon, logo, dan favicon kustom untuk aplikasi.',
            ],
            'label' => [
                'icon' => 'Ikon',
                'icon_alt' => 'Ikon Alt',
                'logo' => 'Logo',
                'logo_alt' => 'Logo Alt',
                'favicon' => 'Favicon',
                'logo_style' => 'Gaya Logo',
                'auth_layout' => 'Tata Letak Autentikasi',
                'layout' => 'Tata Letak Panel Admin',
                'theme' => 'Tema Warna',
                'font' => 'Jenis Huruf',
            ],
            'action' => [
                'submit' => 'Simpan perubahan',
            ],
            'status' => [
                'saved' => 'Tersimpan.',
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
            'placeholder' => [
                'theme' => 'Pilih tema warna',
                'font' => 'Pilih jenis huruf',
            ],
            'option' => [
                'logo_style' => [
                    'icon' => 'Tampilkan ikon bersama nama situs yang dikonfigurasi.',
                    'logo' => 'Tampilkan aset logo penuh.',
                ],
                'auth_layout' => [
                    'simple' => 'Form autentikasi minimal tanpa panel pendukung.',
                    'split' => 'Form dengan panel pendukung bermerek.',
                    'card' => 'Layout kartu terpusat untuk layar autentikasi yang ringkas.',
                ],
                'layout' => [
                    'sidebar' => 'Navigasi sidebar tetap untuk halaman admin.',
                    'header' => 'Layout navigasi atas dengan ruang horizontal lebih lega.',
                ],
            ],
        ],
        'password' => [
            'title' => 'Pengaturan Kata Sandi',
            'heading' => 'Pengaturan Kata Sandi',
            'description' => 'Kelola kata sandi bawaan untuk user baru.',
            'action' => [
                'submit' => 'Simpan perubahan',
            ],
            'status' => [
                'saved' => 'Tersimpan.',
            ],
            'label' => [
                'default_user_password' => 'Kata Sandi Bawaan User',
                'default_user_password_confirmation' => 'Konfirmasi Kata Sandi Bawaan User',
            ],
            'placeholder' => [
                'default_user_password' => 'Masukkan kata sandi bawaan user',
                'default_user_password_confirmation' => 'Masukkan ulang kata sandi bawaan user',
            ],
        ],
        'locale' => [
            'english' => 'Bahasa Inggris',
            'indonesian' => 'Bahasa Indonesia',
        ],
        'logo_style' => [
            'icon' => 'Ikon',
            'logo' => 'Logo',
        ],
        'auth_layout' => [
            'simple' => 'Sederhana',
            'split' => 'Split',
            'card' => 'Kartu',
        ],
        'layout' => [
            'sidebar' => 'Sidebar',
            'header' => 'Header',
        ],
        'theme' => [
            'zinc' => 'Zinc',
            'slate' => 'Slate',
            'rose' => 'Rose',
            'emerald' => 'Emerald',
            'indigo' => 'Indigo',
            'violet' => 'Violet',
            'cyan' => 'Cyan',
            'orange' => 'Orange',
            'teal' => 'Teal',
            'fuchsia' => 'Fuchsia',
        ],
        'font' => [
            'inter' => 'Inter — Bersih & Serbaguna',
            'sora_inter' => 'Sora + Inter — Modern Teknologi',
            'plus_jakarta_dm_sans' => 'Plus Jakarta Sans + DM Sans — Profesional',
            'space_grotesk_inter' => 'Space Grotesk + Inter — Tegas & Geometris',
            'nunito_plus_jakarta' => 'Nunito + Plus Jakarta Sans — Ramah',
        ],
    ],
    'master_data' => [
        'title' => 'Master Data',
        'heading' => 'Master Data',
        'description' => 'Kelola master data di sini.',
        'action' => [
            'open' => 'Buka data',
        ],
        'user' => [
            'index' => [
                'title' => 'Users',
                'heading' => 'Manajemen User',
                'description' => 'Kelola user aplikasi, role, dan permission.',
            ],
            'create' => [
                'title' => 'Create User',
                'heading' => 'Buat User Baru',
                'description' => 'Tambahkan user baru ke sistem.',
            ],
            'show' => [
                'title' => 'Detail User',
                'heading' => 'Detail User',
                'description' => 'Lihat informasi user.',
            ],
            'edit' => [
                'title' => 'Edit User',
                'heading' => 'Ubah User',
                'description' => 'Perbarui informasi user.',
            ],
            'label' => [
                'name' => 'Nama',
                'email' => 'Email',
                'status' => 'Status',
                'role' => 'Peran',
            ],
            'placeholder' => [
                'name' => 'Masukkan nama lengkap',
                'email' => 'Masukkan alamat email',
                'status' => 'Pilih status',
                'role' => 'Pilih peran',
            ],
            'action' => [
                'create' => 'Buat User',
                'update' => 'Perbarui User',
                'reset' => 'Reset',
                'back' => 'Kembali ke Users',
                'view' => 'Lihat',
            ],
            'message' => [
                'created' => [
                    'success' => 'User berhasil dibuat.',
                    'error' => 'Gagal membuat user. Silakan coba lagi.',
                ],
                'updated' => [
                    'success' => 'User berhasil diperbarui.',
                    'error' => 'Gagal memperbarui user. Silakan coba lagi.',
                ],
            ],
        ],
    ],
];
