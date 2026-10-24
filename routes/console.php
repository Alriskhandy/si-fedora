<?php

/*
|--------------------------------------------------------------------------
| Route Console
|--------------------------------------------------------------------------
|
| File ini dimuat Laravel untuk command artisan berbasis closure. Aplikasi ini
| TIDAK memakai closure command:
|
| - Command aplikasi berupa kelas di app/Console/Commands (terdaftar otomatis).
|   Daftar dan kegunaannya: docs/pengembang/REFERENSI-KOMPONEN.md.
| - Jadwal (scheduler) didefinisikan di bootstrap/app.php → withSchedule().
|
| Biarkan file ini tetap ada karena dirujuk di bootstrap/app.php (withRouting: commands).
|
*/
