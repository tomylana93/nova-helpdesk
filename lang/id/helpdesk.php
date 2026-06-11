<?php

return [
    'ticket' => [
        'index' => [
            'title' => 'Tiket',
            'heading' => 'Manajemen Tiket',
            'description' => 'Lihat dan kelola semua tiket helpdesk.',
        ],
        'create' => [
            'title' => 'Buat Tiket',
            'heading' => 'Ajukan Tiket Baru',
            'description' => 'Laporkan insiden atau ajukan permintaan layanan.',
        ],
        'show' => [
            'title' => 'Detail Tiket',
            'heading' => 'Detail Tiket',
            'description' => 'Lihat informasi dan riwayat tiket.',
        ],
        'edit' => [
            'title' => 'Edit Tiket',
            'heading' => 'Edit Tiket',
            'description' => 'Perbarui detail, status, dan penugasan tiket.',
        ],
        'label' => [
            'ticket_number' => 'No. Tiket',
            'type' => 'Tipe',
            'subject' => 'Subjek',
            'description' => 'Deskripsi',
            'status' => 'Status',
            'priority' => 'Prioritas',
            'branch' => 'Cabang',
            'department' => 'Departemen',
            'category' => 'Kategori',
            'requester' => 'Pemohon',
            'assignee' => 'Ditugaskan Ke',
            'submitted_at' => 'Diajukan Pada',
            'resolved_at' => 'Diselesaikan Pada',
            'all_types' => 'Semua Tipe',
            'all_priorities' => 'Semua Prioritas',
            'view' => 'Tampilan',
            'actions' => 'Aksi',
        ],
        'view' => [
            'all' => 'Semua tiket',
            'mine' => 'Ditugaskan ke saya',
            'unassigned' => 'Belum ditugaskan',
            'overdue' => 'Terlambat',
        ],
        'sla' => [
            'first_response' => 'Respons pertama',
            'resolution' => 'Penyelesaian',
            'no_sla' => 'Tidak ada SLA',
            'completed' => 'Selesai',
            'remaining' => 'Sisa :duration',
            'overdue' => 'Lewat :duration',
            'minutes' => ':count menit',
            'hours' => ':count jam',
            'hours_minutes' => ':hours :minutes',
        ],
        'transition' => [
            'in_progress' => 'Mulai Kerjakan',
            'waiting_for_requester' => 'Tunggu Requester',
            'resolved' => 'Selesaikan',
            'closed' => 'Tutup',
            'reopened' => 'Buka Kembali',
        ],
        'placeholder' => [
            'type' => 'Pilih tipe tiket',
            'subject' => 'Masukkan ringkasan singkat masalah',
            'description' => 'Jelaskan masalah secara detail...',
            'status' => 'Pilih status',
            'priority' => 'Pilih prioritas',
            'branch' => 'Pilih cabang (opsional)',
            'department' => 'Pilih departemen (opsional)',
            'category' => 'Pilih kategori',
            'assignee' => 'Pilih petugas (opsional)',
        ],
        'action' => [
            'create' => 'Kirim Tiket',
            'update' => 'Perbarui Tiket',
            'edit' => 'Edit',
            'back' => 'Kembali ke Tiket',
            'reset' => 'Reset',
            'reply' => 'Balas',
            'reopen' => 'Buka Kembali',
            'confirm_resolved' => 'Konfirmasi Selesai',
        ],
        'message' => [
            'created' => [
                'success' => 'Tiket berhasil diajukan.',
            ],
            'updated' => [
                'success' => 'Tiket berhasil diperbarui.',
            ],
            'reopened' => [
                'success' => 'Tiket dibuka kembali.',
            ],
            'confirmed' => [
                'success' => 'Tiket dikonfirmasi selesai dan ditutup.',
            ],
        ],
    ],
    'approval' => [
        'message' => [
            'approved' => 'Tiket disetujui dan dipindahkan ke In Progress.',
            'rejected' => 'Tiket ditolak dan ditutup.',
        ],
        'label' => [
            'approve' => 'Setujui',
            'reject' => 'Tolak',
            'decision_note' => 'Catatan Keputusan (opsional)',
            'pending' => 'Menunggu Persetujuan',
        ],
    ],
    'comment' => [
        'label' => [
            'add_comment' => 'Tambah Komentar',
            'internal_note' => 'Catatan Internal',
            'public' => 'Publik',
            'internal' => 'Internal',
            'visibility' => 'Visibilitas',
            'placeholder' => 'Tulis komentar Anda di sini...',
            'no_comments' => 'Belum ada komentar.',
            'awaiting_reply' => 'Tiket ini menunggu balasan Anda.',
        ],
        'action' => [
            'submit' => 'Kirim Komentar',
        ],
        'message' => [
            'created' => ['success' => 'Komentar berhasil ditambahkan.'],
        ],
    ],
];
