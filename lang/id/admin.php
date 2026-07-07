<?php

return [
    'settings' => [
        'title' => 'Pengaturan',
        'heading' => 'Pengaturan',
        'description' => 'Kelola pengaturan Anda di sini.',
        'action' => [
            'open' => 'Buka pengaturan',
        ],
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
                'phone' => 'Nomor Telepon',
                'status' => 'Status',
                'role' => 'Peran',
                'branch' => 'Cabang',
                'department' => 'Departemen',
                'last_login_at' => 'Login Terakhir',
            ],
            'placeholder' => [
                'name' => 'Masukkan nama lengkap',
                'email' => 'Masukkan alamat email',
                'phone' => 'Masukkan nomor telepon',
                'status' => 'Pilih status',
                'role' => 'Pilih peran',
                'branch' => 'Pilih cabang',
                'department' => 'Pilih departemen',
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
                'deleted' => [
                    'success' => 'User berhasil dihapus.',
                ],
                'restored' => [
                    'success' => 'User berhasil dipulihkan.',
                ],
            ],
        ],
        'branch' => [
            'index' => [
                'title' => 'Cabang',
                'heading' => 'Manajemen Cabang',
                'description' => 'Kelola cabang perusahaan.',
            ],
            'create' => [
                'title' => 'Buat Cabang',
                'heading' => 'Buat Cabang Baru',
                'description' => 'Tambahkan cabang baru ke sistem.',
            ],
            'show' => [
                'title' => 'Detail Cabang',
                'heading' => 'Detail Cabang',
                'description' => 'Lihat informasi cabang.',
            ],
            'edit' => [
                'title' => 'Ubah Cabang',
                'heading' => 'Ubah Cabang',
                'description' => 'Perbarui informasi cabang.',
            ],
            'label' => [
                'code' => 'Kode',
                'name' => 'Nama',
                'status' => 'Status',
            ],
            'placeholder' => [
                'code' => 'Masukkan kode cabang (contoh: BR-HO)',
                'name' => 'Masukkan nama cabang',
                'status' => 'Pilih status',
            ],
            'action' => [
                'create' => 'Buat Cabang',
                'update' => 'Perbarui Cabang',
                'back' => 'Kembali ke Cabang',
            ],
            'message' => [
                'created' => [
                    'success' => 'Cabang berhasil dibuat.',
                ],
                'updated' => [
                    'success' => 'Cabang berhasil diperbarui.',
                ],
            ],
        ],
        'department' => [
            'index' => [
                'title' => 'Departemen',
                'heading' => 'Manajemen Departemen',
                'description' => 'Kelola departemen cabang.',
            ],
            'create' => [
                'title' => 'Buat Departemen',
                'heading' => 'Buat Departemen Baru',
                'description' => 'Tambahkan departemen baru ke sistem.',
            ],
            'show' => [
                'title' => 'Detail Departemen',
                'heading' => 'Detail Departemen',
                'description' => 'Lihat informasi departemen.',
            ],
            'edit' => [
                'title' => 'Ubah Departemen',
                'heading' => 'Ubah Departemen',
                'description' => 'Perbarui informasi departemen.',
            ],
            'label' => [
                'branch' => 'Cabang',
                'code' => 'Kode',
                'name' => 'Nama',
                'status' => 'Status',
            ],
            'placeholder' => [
                'branch' => 'Pilih cabang',
                'code' => 'Masukkan kode departemen (contoh: DEPT-IT)',
                'name' => 'Masukkan nama departemen',
                'status' => 'Pilih status',
            ],
            'action' => [
                'create' => 'Buat Departemen',
                'update' => 'Perbarui Departemen',
                'back' => 'Kembali ke Departemen',
            ],
            'message' => [
                'created' => [
                    'success' => 'Departemen berhasil dibuat.',
                ],
                'updated' => [
                    'success' => 'Departemen berhasil diperbarui.',
                ],
            ],
        ],
        'sla_policy' => [
            'index' => [
                'title' => 'Kebijakan SLA',
                'heading' => 'Manajemen Kebijakan SLA',
                'description' => 'Kelola kebijakan SLA untuk target respons dan penyelesaian.',
            ],
            'create' => [
                'title' => 'Buat Kebijakan SLA',
                'heading' => 'Buat Kebijakan SLA Baru',
                'description' => 'Tentukan kebijakan SLA untuk tipe dan prioritas tiket.',
            ],
            'show' => [
                'title' => 'Detail Kebijakan SLA',
                'heading' => 'Detail Kebijakan SLA',
                'description' => 'Lihat detail kebijakan SLA.',
            ],
            'edit' => [
                'title' => 'Edit Kebijakan SLA',
                'heading' => 'Edit Kebijakan SLA',
                'description' => 'Perbarui target kebijakan SLA.',
            ],
            'label' => [
                'name' => 'Nama',
                'ticket_type' => 'Tipe Tiket',
                'all_types' => 'Semua Tipe',
                'priority' => 'Prioritas',
                'first_response' => 'Target Respons Pertama (menit)',
                'resolution' => 'Target Penyelesaian (menit)',
                'is_active' => 'Aktif',
            ],
            'placeholder' => [
                'name' => 'Masukkan nama kebijakan',
                'ticket_type' => 'Semua tipe tiket',
                'priority' => 'Pilih prioritas',
            ],
            'action' => [
                'create' => 'Buat Kebijakan',
                'update' => 'Perbarui Kebijakan',
                'back' => 'Kembali ke Kebijakan SLA',
            ],
            'message' => [
                'created' => ['success' => 'Kebijakan SLA berhasil dibuat.'],
                'updated' => ['success' => 'Kebijakan SLA berhasil diperbarui.'],
            ],
        ],
        'ticket_category' => [
            'index' => [
                'title' => 'Kategori Tiket',
                'heading' => 'Manajemen Kategori Tiket',
                'description' => 'Kelola kategori dan subkategori tiket.',
            ],
            'create' => [
                'title' => 'Buat Kategori',
                'heading' => 'Buat Kategori Baru',
                'description' => 'Tambahkan kategori atau subkategori tiket baru.',
            ],
            'show' => [
                'title' => 'Detail Kategori',
                'heading' => 'Detail Kategori',
                'description' => 'Lihat detail kategori.',
            ],
            'edit' => [
                'title' => 'Ubah Kategori',
                'heading' => 'Ubah Kategori',
                'description' => 'Perbarui detail kategori.',
            ],
            'label' => [
                'parent' => 'Kategori Induk',
                'name' => 'Nama',
                'description' => 'Deskripsi',
                'status' => 'Status',
            ],
            'placeholder' => [
                'parent' => 'Pilih kategori induk (opsional)',
                'name' => 'Masukkan nama kategori',
                'description' => 'Masukkan deskripsi kategori',
                'status' => 'Pilih status',
            ],
            'action' => [
                'create' => 'Buat Kategori',
                'update' => 'Perbarui Kategori',
                'back' => 'Kembali ke Kategori',
            ],
            'message' => [
                'created' => [
                    'success' => 'Kategori berhasil dibuat.',
                ],
                'updated' => [
                    'success' => 'Kategori berhasil diperbarui.',
                ],
            ],
        ],
        'asset' => [
            'index' => [
                'title' => 'Aset',
                'heading' => 'Manajemen Aset',
                'description' => 'Kelola aset perangkat keras dan lunak perusahaan.',
            ],
            'create' => [
                'title' => 'Buat Aset',
                'heading' => 'Buat Aset Baru',
                'description' => 'Tambahkan aset baru ke inventaris.',
            ],
            'show' => [
                'title' => 'Detail Aset',
                'heading' => 'Detail Aset',
                'description' => 'Lihat informasi aset.',
            ],
            'edit' => [
                'title' => 'Ubah Aset',
                'heading' => 'Ubah Aset',
                'description' => 'Perbarui informasi aset.',
            ],
            'label' => [
                'asset_tag' => 'Label Aset',
                'name' => 'Nama Aset',
                'category' => 'Kategori',
                'status' => 'Status',
                'branch' => 'Lokasi Cabang',
                'user' => 'Penerima Tugas',
            ],
            'placeholder' => [
                'asset_tag' => 'Masukkan label aset (mis. AST-10001)',
                'name' => 'Masukkan nama aset (mis. Laptop Lenovo)',
                'category' => 'Pilih kategori',
                'status' => 'Pilih status',
                'branch' => 'Pilih cabang',
                'branch_unassigned' => 'Tanpa cabang / Penyimpanan',
                'user' => 'Pilih penerima tugas',
                'user_unassigned' => 'Belum ditugaskan',
            ],
            'action' => [
                'create' => 'Buat Aset',
                'update' => 'Ubah Aset',
                'back' => 'Kembali ke Aset',
                'delete' => 'Hapus',
                'confirm_delete' => 'Anda yakin ingin menghapus aset ini?',
            ],
            'history' => [
                'title' => 'Riwayat Dukungan',
                'empty' => 'Belum ada tiket yang terhubung dengan aset ini.',
            ],
            'message' => [
                'created' => [
                    'success' => 'Aset berhasil dibuat.',
                ],
                'updated' => [
                    'success' => 'Aset berhasil diperbarui.',
                ],
                'deleted' => [
                    'success' => 'Aset berhasil dihapus.',
                ],
            ],
            'validation' => [
                'user_branch' => 'Penerima tugas yang dipilih harus berada di cabang yang dipilih.',
            ],
        ],
    ],
    'asset' => [
        'category' => [
            'laptop' => 'Laptop',
            'monitor' => 'Monitor',
            'device' => 'Perangkat Mobile',
            'license' => 'Lisensi Perangkat Lunak',
            'other' => 'Lainnya',
        ],
        'status' => [
            'in_use' => 'Sedang Digunakan',
            'in_storage' => 'Di Penyimpanan',
            'under_repair' => 'Sedang Diperbaiki',
            'retired' => 'Sudah Dihapus/Pensiun',
        ],
    ],
];
