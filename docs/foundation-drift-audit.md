# Foundation Drift Audit — `nova-helpdesk` vs `nova-starter-kit`

> **Status:** rencana / ledger keputusan — *plan-only*, belum ada kode diubah.
> **Dibuat:** 2026-07-07 · **Metode:** `diff -rq` otoritatif langsung dari kedua repo + verifikasi file:line. Analisis tempelan sebelumnya dipakai hanya sebagai petunjuk awal, bukan sumber kebenaran.

## Keputusan kerangka (hasil grilling)

| Hal | Keputusan |
|---|---|
| Model hubungan | **(a)** `nova-starter-kit` = upstream hidup; arah sinkronisasi **starter → helpdesk**. |
| Relasi git | **Tidak ada shared history** (repo di-`init` terpisah). Sinkronisasi ke depan **manual**, tak bisa `git merge`. Nilai alignment = model mental sama + fix bisa di-copy antar-sibling, *bukan* merge mulus. |
| Scope | Ketiga bucket masuk, termasuk audit divergensi disengaja. |
| Arah perbedaan | Full sweep tiga arah: modified-in-both + starter-only + helpdesk-only. |
| Tie-breaker Bucket 3 | **(c) case-by-case** — tiap item disajikan rekomendasi + alasan; **kamu** ketuk palu di kolom Keputusan. |
| Deliverable sesi ini | **Plan-only.** Eksekusi = putaran terpisah setelah ledger disetujui. |

## Cara pakai

Isi kolom **Keputusan** tiap baris dengan salah satu: `ADOPT` (ambil versi starter) · `KEEP` (pertahankan helpdesk) · `SKIP` (abaikan, divergensi wajar) · `DEFER` (tunda). Setelah terisi, eksekusi dipecah per bucket dengan TDD.

Legenda **Risiko** eksekusi: 🟢 murah/lokal · 🟡 sedang · 🔴 menyentuh schema/auth/RBAC.

---

## Bucket 1 — Regresi / kehilangan tak sengaja

Item di mana helpdesk kehilangan sesuatu dari starter. Semua terverifikasi.

| # | Item | Bukti | Temuan | Rekomendasi | Risiko | Keputusan |
|---|---|---|---|---|---|---|
| 1.1 | `shouldRenderJsonWhen(api/*)` hilang | `bootstrap/app.php:42` (helpdesk `//` kosong) | Helpdesk melepas handler JSON-untuk-`api/*`. **Verifikasi:** helpdesk tak punya route `api/*` sama sekali → efek nol **saat ini**, tapi regresi laten bila nanti ada API. | **ADOPT** (restore) — 3 baris, mencegah jebakan masa depan. Alternatif KEEP kalau yakin helpdesk selamanya tanpa API. | 🟢 | |
| 1.2 | Session cookie fallback = `laravel` | `config/session.php:132` (helpdesk `'laravel'`, starter `'nova-starter-kit'`) | Helpdesk memakai default framework lama, tak pernah ikut rename starter. Hanya berlaku bila `APP_NAME` env kosong. | **ADOPT-varian** → set ke `nova-helpdesk` (bukan menyalin `nova-starter-kit`). | 🟢 | |

---

## Bucket 2 — Metadata / tooling murah

| # | Item | Bukti | Temuan | Rekomendasi | Risiko | Keputusan |
|---|---|---|---|---|---|---|
| 2.1 | ~~Composer package name masih warisan starter~~ | versi **ter-commit** `composer.json` = `tomylana93/nova-helpdesk`, `php ^8.5` | ⚠️ **Temuan palsu** — dibaca dari working-tree yang sedang dimodifikasi. Kode ter-commit sudah benar; tak ada aksi. | **N/A** (sudah beres) | 🟢 | ✓ |
| 2.2 | CI tak trigger branch `staging` | `.github/workflows/lint.yml`, `tests.yml` (helpdesk tak punya `- staging`) | Alur rilis 3-branch `dev→staging→main` (lih. `mem:branching_release_flow`) tapi CI helpdesk hanya jalan di `dev`+`main` → PR ke `staging` tak tervalidasi. | **ADOPT** — tambahkan `staging` di `push` & `pull_request` kedua workflow. | 🟢 | |
| 2.3 | Nama workflow lint | helpdesk `name: lint`, starter `name: linter` | Kosmetik; tak memengaruhi required-check (yang dipakai nama *job*, bukan *workflow*). | **SKIP** kecuali kamu mau seragam. | 🟢 | |
| 2.4 | tests.yml setup PHP beda | helpdesk daftar `extensions:` eksplisit; starter `tools: composer:v2` | Dua gaya setup-php; keduanya jalan. Helpdesk lebih eksplisit soal ekstensi. | **KEEP** (helpdesk lebih aman/eksplisit); cross-check saja saat ada kegagalan CI. | 🟢 | |
| 2.5 | `wayfinder:generate --with-form` | helpdesk **sudah** pakai `--with-form --no-interaction`; starter masih bare | ⚠️ Koreksi analisis awal: **helpdesk lebih maju**. Ini bukan gap helpdesk. | **KEEP** (idealnya di-backport ke starter — di luar scope). | 🟢 | |
| 2.6 | `.githooks/pre-push` (starter-only) | starter `/.githooks` | Starter punya git hook pre-push; helpdesk tidak. | **DEFER/ADOPT** — perlu lihat isi hook dulu; bukan blocker. | 🟢 | |

---

## Bucket 3 — Divergensi disengaja (butuh keputusanmu)

> Semua item di sini keputusan produk. Default penyajian netral; palu di tanganmu.

### 3A. Model RBAC — divergensi arsitektural terbesar

**Konteks terverifikasi:** Seam RBAC starter (`config/roles.php` Role→Permission map + `config/superadmin.php` + `app/Enums/Permission.php` 4-case + full-publish `config/permission.php`) dibuat **2026-06-22**, ~2 minggu *setelah* helpdesk fork (**2026-06-08**). Jadi helpdesk memakai pola **lama** (bespoke), bukan membuang seam baru.

| # | Item | Bukti | Temuan | Rekomendasi | Risiko | Keputusan |
|---|---|---|---|---|---|---|
| 3A.1 | `AdminPermission` (14 case) vs starter `Permission` (4 case) | `app/Enums/AdminPermission.php` vs `nova-starter-kit/app/Enums/Permission.php` | Helpdesk punya permission jauh lebih kaya & domain-spesifik. | **KEEP** — enum helpdesk superset & sesuai kebutuhan; adopsi `Permission` = kemunduran. | 🔴 | |
| 3A.2 | `config/permission.php` `array_replace_recursive` vs full-publish | `config/permission.php:1` (helpdesk 10 baris override) | Helpdesk override minimal (termasuk `model_uuid`); starter publish penuh 219 baris. | **KEEP** — override minimal lebih rapi & tahan update paket. | 🟡 | |
| 3A.3 | Adopsi seam `config/roles.php` + `superadmin.php`? | starter-only kedua file | Seam starter memungkinkan swap Role→Permission map tanpa sentuh `SyncRolesCommand`. Helpdesk `SyncRolesCommand` (modified-in-both) kemungkinan inline map-nya. | **DEFER** — evaluasi tersendiri: apakah pindah ke seam config bernilai? Refactor menyentuh `SyncRolesCommand`+seeder. Butuh sub-audit. | 🔴 | |
| 3A.4 | `Gate::before` superadmin + alias middleware `role/permission` | `bootstrap/app.php:28-31`, `AppServiceProvider.php:66` | Helpdesk menambah spatie middleware & superadmin bypass; starter tidak. | **KEEP** — ini kapabilitas helpdesk yang sah. | 🟡 | |

### 3B. User model & auth surface

| # | Item | Bukti | Temuan | Rekomendasi | Risiko | Keputusan |
|---|---|---|---|---|---|---|
| 3B.1 | Kolom `phone` di users | migration `0001_..._create_users_table.php:19` (starter punya, helpdesk tidak) | Helpdesk drop `phone`. | **KEEP** kecuali helpdesk butuh no. telp user. Keputusan produk. | 🔴 | |
| 3B.2 | `softDeletes` di users + aksi `DeleteUser`/`RestoreUser` | migration (helpdesk tak ada `softDeletes()`); `User.php` tanpa `SoftDeletes`; starter-only `DeleteUser.php`,`RestoreUser.php` | Helpdesk kehilangan soft-delete + restore user sepenuhnya. Ini **kapabilitas nyata** yang starter punya. | **DEFER** — putuskan: helpdesk perlu arsip/restore user? Bila ya, ADOPT (migration+model+2 action+test). Bila tidak, KEEP. | 🔴 | |
| 3B.3 | `last_login_at` (kolom+cast+listener) | migration & `AppServiceProvider.php:45` (listener `Login` dihapus). **Verifikasi:** `last_login_at` tak dipakai di mana pun di helpdesk. | Fitur dilepas **koheren** (kolom, cast, listener semua hilang bersama) — bukan setengah jadi. | **KEEP** (drop) kecuali kamu mau melacak login terakhir agent (berguna utk helpdesk). Keputusan produk. | 🟡 | |
| 3B.4 | `must_change_password` | helpdesk via migration terpisah `2026_07_06_..._add_must_change_password`; starter di create-table awal | Sama-sama ADA, beda lokasi migration saja. | **SKIP** — hasil identik, penempatan migration tak relevan. | 🟢 | |
| 3B.5 | `status` default cast | migration:20 helpdesk `UserStatus::Active->value` vs starter `UserStatus::Active` | Beda gaya penulisan default enum; setara. | **SKIP**. | 🟢 | |
| 3B.6 | `TwoFactorAuthenticationRequest` (starter-only) | `app/Http/Requests/Settings/TwoFactorAuthenticationRequest.php` | Starter menyiapkan 2FA request; helpdesk tidak. Fortify aktif di helpdesk hanya reset-password (`mem:conventions`). | **KEEP** (skip) kecuali 2FA masuk roadmap helpdesk. | 🟡 | |
| 3B.7 | `UpdateProfile` action (starter-only) | `app/Actions/Settings/UpdateProfile.php` | Starter mengekstrak update profil ke Action; helpdesk kemungkinan inline di controller (`ProfileController` modified-in-both). | **DEFER** — cek apakah `ProfileController` helpdesk gemuk; bila ya ADOPT pola Action (sesuai `mem:conventions` "controller tipis"). | 🟡 | |
| 3B.8 | `UsersExport` + `ExportUserRequest` (starter-only) | `app/Exports/UsersExport.php`, `.../ExportUserRequest.php` | Starter punya export user; helpdesk tidak (padahal helpdesk punya `maatwebsite/excel` utk report). | **DEFER** — nilai fitur export user; bila diinginkan ADOPT. | 🟡 | |

### 3C. Upload / media

| # | Item | Bukti | Temuan | Rekomendasi | Risiko | Keputusan |
|---|---|---|---|---|---|---|
| 3C.1 | `TemporaryUploadFactory` (starter-only) + `TemporaryUpload` model | starter-only `database/factories/TemporaryUploadFactory.php`; `TemporaryUpload.php` modified-in-both | Starter memberi factory berdedikasi; helpdesk pakai `Factory<TemporaryUpload>` generik → test upload helpdesk kurang ergonomis. | **ADOPT** — factory murah, memperbaiki testability. 🟡 karena menyentuh model+test. | 🟡 | |
| 3C.2 | `config/media-library.php` (helpdesk-only) | helpdesk-only | Helpdesk publish config media-library; wajar utk kebutuhan attachment tiket. | **KEEP**. | 🟢 | |

### 3C-noise. Config publish tambahan (helpdesk-only)

`config/nova.php`, `config/query-builder.php` — helpdesk publish paket yang dipakainya (query-builder utk filter API tabel). **KEEP semua (SKIP audit)** — konsekuensi domain.

---

## Kategori bervolume-besar — dinilai berkelompok

Item berikut **tidak** di-itemize per file (puluhan–ratusan). Karakterisasi dari sampling; bila kamu mau salah satu grup di-deep-dive, sebut grupnya.

| Grup | Isi | Penilaian awal | Rekomendasi |
|---|---|---|---|
| **G1 — File generated** | `resources/js/actions/**`, `resources/js/routes/**` (modified-in-both), `.serena/cache/*.pkl` | Output Wayfinder & cache Serena. Jangan tangan-edit (`mem:conventions`). Beda = konsekuensi route berbeda. | **SKIP** — akan regenerasi otomatis saat item lain dieksekusi. |
| **G2 — Komponen `ui/**` (shadcn-vue)** | ~30 file `resources/js/components/ui/**` beda | Kemungkinan drift versi shadcn / format Prettier, bukan logika. | **DEFER** — sub-audit terpisah bila mau seragamkan baseline shadcn; risiko rendah, nilai rendah. |
| **G3 — Komponen app & layout** | `AppSidebar.vue`, `NavMain.vue`, `NavUser.vue`, `Dashboard.vue`, layouts, dll. | Sebagian beda karena nav/branding helpdesk (domain), sebagian mungkin drift murni. `Dashboard.vue` sedang di-refactor (`mem:dashboard/refactor-plan`). | **KEEP mayoritas** (domain); **DEFER** pemilahan halus. |
| **G4 — Lang** | `lang/en/*`, `lang/id/*`, `lang/*.json` beda + helpdesk-only `helpdesk.php`/`reports.php` | Beda = string domain helpdesk + entri baru. | **KEEP** — domain. Cek parity via `lang:export` saat eksekusi. |
| **G5 — Types TS** | `resources/js/types/*` modified-in-both + helpdesk-only | Diperluas utk domain; `auth.ts`/`index.ts` mungkin ada drift pondasi kecil. | **DEFER** — sub-audit ringan `auth.ts`/`index.ts`/`user.ts` saja. |
| **G6 — Tests** | Banyak modified-in-both + starter-only (`EnsureActiveUserTest`, `TranslationParityTest`, `Console/`, `Settings/*`) + helpdesk-only | Starter-only test = coverage yang helpdesk mungkin kehilangan (mis. `EnsureActiveUserTest`, `TranslationParityTest`). | **DEFER→sebagian ADOPT** — test starter-only bernilai (coverage pondasi). Sub-audit tersendiri. |
| **G7 — Docs & memories & agent config** | `README.md`, `CLAUDE.md`, `AGENTS.md`, `boost.json`, `.ai/**`, `.claude/**`, `.codex/**`, `.serena/memories/**`, docs recipe (starter-only) | Konfigurasi agent & memori spesifik-proyek; docs recipe starter = referensi. | **SKIP** config/memories (spesifik-proyek). **DEFER** apakah mengimpor `docs/recipe-*.md` starter sebagai referensi. |
| **G8 — Env & editor baseline** | `.editorconfig`, `.env*`, `deploy.sh` | `.env*` lingkungan-lokal (wajar beda). `.editorconfig`/`deploy.sh` mungkin ada perbaikan starter. | **DEFER** — diff cepat `.editorconfig` & `deploy.sh` saja; `.env*` SKIP. |
| **G9 — Release metadata** | `release-please-config*.json`, `.release-please-manifest*.json`, `config/version.php`, `CHANGELOG.md` | Spesifik per-proyek (nama komponen `nova-helpdesk`, versi berjalan). | **SKIP** — divergensi wajar; kelola via `mem:branching_release_flow`. |

---

## Helpdesk-only — hasil sweep

Disweep penuh untuk menangkap file pondasi generik yang keliru masuk. **Hasil: tak ada.** Seluruh helpdesk-only adalah domain: tickets, assets, SLA, branches, departments, ticket-categories, dashboard, reports, notifications, plus support domain (`IndonesiaCalendar.php`, `SlaCalculator.php`) dan test/factory/migration/halaman terkait. **Semua KEEP (dikecualikan sebagai domain).**

---

## Ringkasan aksi yang disarankan (setelah kamu isi kolom Keputusan)

- **Cepat & aman (kandidat ADOPT langsung):** 1.1, 1.2, 2.1, 2.2, 3C.1.
- **Butuh keputusan produk (DEFER):** 3A.3, 3B.2, 3B.7, 3B.8, 3B.3 (last_login), 3B.1 (phone).
- **Sub-audit lanjutan bila diminta:** G2 (shadcn baseline), G5 (types pondasi), G6 (test coverage starter-only), G8 (editorconfig/deploy).
- **Tak perlu diapa-apakan:** semua SKIP + seluruh helpdesk-only.

> Eksekusi item ADOPT dilakukan TDD test-first, dipecah per bucket, di branch `dev`, hanya setelah kolom Keputusan terisi.
