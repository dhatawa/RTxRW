# RTxRW — catatan untuk agen

Laravel 13 monolith (Blade + Tailwind v4 + Alpine). Lihat README.md untuk arsitektur dan docs/ASSUMPTIONS.md untuk keputusan bisnis.

## Environment lokal (Windows + Laragon)
- PHP tidak ada di PATH: pakai `C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe` (di Git Bash: `export PATH="/c/laragon/bin/php/php-8.3.33-Win32-vs16-x64:$PATH"`).
- Composer: `php C:/ProgramData/ComposerSetup/bin/composer.phar`, set `COMPOSER_NO_AUDIT=1` (audit bisa menggantung).
- MySQL dev: port 3308, root tanpa password, database `rtxrw`. Commit di server ini lambat; `migrate:fresh --seed` makan waktu beberapa menit.
- Test: `php artisan test` (SQLite in-memory).

## Aturan penting
- Perubahan status pengajuan HANYA lewat `App\Services\LetterWorkflow::apply()` (atau `LetterIssuer` untuk penerbitan). Jangan tambahkan `status` ke fillable.
- Setiap akses data RT/warga harus lewat policy (`Gate::authorize`) dan query ter-scope wilayah.
- Komponen ikon proyek adalah `<x-svg-icon name="…">` (Lucide). Jangan pakai `<x-icon>` karena bentrok dengan komponen bawaan blade-icons.
- Literal `{{ }}` di Blade harus dirakit lewat PHP (`str_repeat('{', 2)`), jangan ditulis langsung.
- Gaya UI: token warna di `resources/css/app.css` (@theme). Hindari gradient, shadow tebal, dan kartu bertumpuk. Pakai `panel`, `table-civic`, dan `x-summary-list`.
