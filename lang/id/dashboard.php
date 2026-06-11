<?php

return [
    'greeting' => 'Halo, :name',
    'subtitle' => 'Ringkasan helpdesk Anda untuk :period.',
    'period' => [
        'monthly' => 'Bulanan',
        'yearly' => 'Tahunan',
        'month' => 'Bulan',
        'year' => 'Tahun',
        'vs_previous' => 'vs :period',
    ],
    'live' => [
        'heading' => 'Saat ini',
        'active' => 'Tiket Aktif',
        'assigned' => 'Ditugaskan ke Anda',
        'unassigned' => 'Belum Ditugaskan',
        'pending_approval' => 'Menunggu Persetujuan',
        'sla_breached' => 'SLA Terlewati',
    ],
    'metric' => [
        'heading' => 'Pada :period',
        'created' => 'Dibuat',
        'resolved' => 'Selesai',
        'new' => 'Baru',
    ],
    'compliance' => [
        'title' => 'Tingkat Kepatuhan SLA',
        'caption' => 'Selesai sesuai SLA',
        'tooltip' => ':within dari :total tiket selesai tepat waktu (sebelum batas waktu penyelesaian).',
    ],
    'trend' => [
        'title' => 'Dibuat vs Selesai',
        'created' => 'Dibuat',
        'resolved' => 'Selesai',
        'empty' => 'Tidak ada aktivitas pada periode ini.',
    ],
    'breakdown' => [
        'priority_title' => 'Distribusi Prioritas Tiket',
        'status_title' => 'Distribusi Status Aktif',
        'tickets' => 'Tiket',
        'empty' => 'Tidak ada data distribusi.',
    ],
    'priority' => [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'critical' => 'Kritis',
    ],
    'status' => [
        'open' => 'Terbuka',
        'pending_approval' => 'Menunggu Persetujuan',
        'in_progress' => 'Sedang Diproses',
        'waiting_for_requester' => 'Menunggu Pelapor',
        'reopened' => 'Dibuka Kembali',
    ],
];
