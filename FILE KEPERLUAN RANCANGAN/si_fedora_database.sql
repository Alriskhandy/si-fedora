-- =====================================================
-- SI-FEDORA Database Schema
-- Generated from Laravel Migrations
-- Date: 2026-02-03
-- =====================================================

-- Create Database (optional - uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS si_fedora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE si_fedora;

-- =====================================================
-- 1. CACHE TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. JOB TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. PERMISSION TABLES (Spatie)
-- =====================================================

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `guard_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `guard_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255) NOT NULL,
  `model_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
  INDEX `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` 
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255) NOT NULL,
  `model_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`),
  INDEX `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` 
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` 
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` 
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. MASTER DATA TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `kabupaten_kota` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(10) NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `jenis` ENUM('kabupaten', 'kota') NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kabupaten_kota_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `master_tahapan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_tahapan` VARCHAR(255) NOT NULL,
  `urutan` INT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_tahapan_urutan_unique` (`urutan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `master_jenis_dokumen` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(255) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `master_bab` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_bab` VARCHAR(255) NOT NULL,
  `jenis_dokumen_id` BIGINT UNSIGNED NULL,
  `parent_id` BIGINT UNSIGNED NULL,
  `urutan` INT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `master_bab_jenis_dokumen_id_foreign` 
    FOREIGN KEY (`jenis_dokumen_id`) REFERENCES `master_jenis_dokumen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `master_bab_parent_id_foreign` 
    FOREIGN KEY (`parent_id`) REFERENCES `master_bab` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `master_urusan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_urusan` VARCHAR(255) NOT NULL,
  `kategori` ENUM('wajib_dasar', 'wajib_non_dasar', 'pilihan') NOT NULL,
  `urutan` INT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `master_kelengkapan_verifikasi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_dokumen` VARCHAR(255) NOT NULL,
  `kategori` ENUM('surat_permohonan', 'kelengkapan_verifikasi') NOT NULL DEFAULT 'kelengkapan_verifikasi',
  `tahapan_id` BIGINT UNSIGNED NULL COMMENT 'Kelengkapan untuk tahapan tertentu (null = berlaku untuk semua)',
  `deskripsi` TEXT NULL,
  `wajib` TINYINT(1) NOT NULL DEFAULT 1,
  `urutan` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `master_kelengkapan_verifikasi_kategori_tahapan_id_index` (`kategori`, `tahapan_id`),
  INDEX `master_kelengkapan_verifikasi_wajib_urutan_index` (`wajib`, `urutan`),
  CONSTRAINT `master_kelengkapan_verifikasi_tahapan_id_foreign` 
    FOREIGN KEY (`tahapan_id`) REFERENCES `master_tahapan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. USER TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `kabupaten_kota_id` BIGINT UNSIGNED NULL,
  `no_hp` VARCHAR(20) NULL,
  `foto_profile` VARCHAR(255) NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  CONSTRAINT `users_kabupaten_kota_id_foreign` 
    FOREIGN KEY (`kabupaten_kota_id`) REFERENCES `kabupaten_kota` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `sessions_user_id_index` (`user_id`),
  INDEX `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `temporary_role_assignments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `delegated_by` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `temporary_role_assignments_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `temporary_role_assignments_role_id_foreign` 
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `temporary_role_assignments_delegated_by_foreign` 
    FOREIGN KEY (`delegated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_kabkota_assignments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `kabupaten_kota_id` BIGINT UNSIGNED NOT NULL,
  `jenis_dokumen_id` BIGINT UNSIGNED NULL,
  `role_type` ENUM('verifikator', 'fasilitator', 'koordinator') NOT NULL,
  `is_pic` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'PIC/Ketua Tim untuk kabupaten/kota ini',
  `tahun` YEAR NOT NULL COMMENT 'Tahun penugasan',
  `nomor_surat` VARCHAR(255) NULL COMMENT 'Nomor surat penugasan',
  `file_sk` VARCHAR(255) NULL COMMENT 'File SK Tim',
  `assigned_from` DATE NULL COMMENT 'Mulai penugasan',
  `assigned_until` DATE NULL COMMENT 'Akhir penugasan',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Status aktif assignment',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_user_kabkota_active` (`user_id`, `kabupaten_kota_id`, `is_active`),
  INDEX `idx_kabkota_role_active` (`kabupaten_kota_id`, `role_type`, `is_active`),
  INDEX `idx_user_role_active` (`user_id`, `role_type`, `is_active`),
  INDEX `idx_jenis_tahun` (`jenis_dokumen_id`, `tahun`),
  INDEX `user_kabkota_assignments_is_pic_index` (`is_pic`),
  CONSTRAINT `user_kabkota_assignments_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_kabkota_assignments_kabupaten_kota_id_foreign` 
    FOREIGN KEY (`kabupaten_kota_id`) REFERENCES `kabupaten_kota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_kabkota_assignments_jenis_dokumen_id_foreign` 
    FOREIGN KEY (`jenis_dokumen_id`) REFERENCES `master_jenis_dokumen` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. JADWAL & PERMOHONAN TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `jadwal_fasilitasi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tahun_anggaran` INT NOT NULL,
  `jenis_dokumen` BIGINT UNSIGNED NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `batas_permohonan` DATE NULL,
  `undangan_file` TEXT NULL,
  `status` ENUM('draft', 'published', 'closed') NOT NULL DEFAULT 'draft',
  `dibuat_oleh` BIGINT UNSIGNED NOT NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `jadwal_fasilitasi_tahun_anggaran_jenis_dokumen_index` (`tahun_anggaran`, `jenis_dokumen`),
  INDEX `jadwal_fasilitasi_status_index` (`status`),
  INDEX `jadwal_fasilitasi_tanggal_mulai_tanggal_selesai_index` (`tanggal_mulai`, `tanggal_selesai`),
  INDEX `jadwal_fasilitasi_batas_permohonan_index` (`batas_permohonan`),
  CONSTRAINT `jadwal_fasilitasi_jenis_dokumen_foreign` 
    FOREIGN KEY (`jenis_dokumen`) REFERENCES `master_jenis_dokumen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_fasilitasi_dibuat_oleh_foreign` 
    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_fasilitasi_updated_by_foreign` 
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permohonan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `kab_kota_id` BIGINT UNSIGNED NOT NULL,
  `jadwal_fasilitasi_id` BIGINT UNSIGNED NOT NULL,
  `jenis_dokumen_id` BIGINT UNSIGNED NOT NULL,
  `tahun` INT NOT NULL,
  `status_akhir` ENUM('belum', 'proses', 'revisi', 'selesai') NOT NULL DEFAULT 'belum',
  `submitted_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `permohonan_user_id_tahun_jenis_dokumen_id_index` (`user_id`, `tahun`, `jenis_dokumen_id`),
  INDEX `permohonan_kab_kota_id_tahun_index` (`kab_kota_id`, `tahun`),
  INDEX `permohonan_status_akhir_index` (`status_akhir`),
  CONSTRAINT `permohonan_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permohonan_kab_kota_id_foreign` 
    FOREIGN KEY (`kab_kota_id`) REFERENCES `kabupaten_kota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permohonan_jadwal_fasilitasi_id_foreign` 
    FOREIGN KEY (`jadwal_fasilitasi_id`) REFERENCES `jadwal_fasilitasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permohonan_jenis_dokumen_id_foreign` 
    FOREIGN KEY (`jenis_dokumen_id`) REFERENCES `master_jenis_dokumen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `perpanjangan_waktu` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `alasan` TEXT NOT NULL,
  `surat_permohonan` VARCHAR(255) NULL,
  `catatan_admin` TEXT NULL,
  `diproses_oleh` BIGINT UNSIGNED NULL,
  `diproses_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `perpanjangan_waktu_permohonan_id_index` (`permohonan_id`),
  INDEX `perpanjangan_waktu_user_id_index` (`user_id`),
  CONSTRAINT `perpanjangan_waktu_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `perpanjangan_waktu_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `perpanjangan_waktu_diproses_oleh_foreign` 
    FOREIGN KEY (`diproses_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. TAHAPAN & ASSIGNMENT TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `permohonan_tahapan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `tahapan_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('belum', 'proses', 'revisi', 'selesai') NOT NULL DEFAULT 'belum',
  `catatan` TEXT NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `permohonan_tahapan_permohonan_id_tahapan_id_index` (`permohonan_id`, `tahapan_id`),
  INDEX `permohonan_tahapan_status_index` (`status`),
  UNIQUE KEY `permohonan_tahapan_permohonan_id_tahapan_id_unique` (`permohonan_id`, `tahapan_id`),
  CONSTRAINT `permohonan_tahapan_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permohonan_tahapan_tahapan_id_foreign` 
    FOREIGN KEY (`tahapan_id`) REFERENCES `master_tahapan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permohonan_tahapan_updated_by_foreign` 
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permohonan_tahapan_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_tahapan_id` BIGINT UNSIGNED NOT NULL,
  `status_lama` ENUM('belum', 'proses', 'revisi', 'selesai') NULL,
  `status_baru` ENUM('belum', 'proses', 'revisi', 'selesai') NOT NULL,
  `keterangan` TEXT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `permohonan_tahapan_log_permohonan_tahapan_id_created_at_index` (`permohonan_tahapan_id`, `created_at`),
  CONSTRAINT `permohonan_tahapan_log_permohonan_tahapan_id_foreign` 
    FOREIGN KEY (`permohonan_tahapan_id`) REFERENCES `permohonan_tahapan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permohonan_tahapan_log_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `koordinator_assignment` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `koordinator_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `koordinator_assignment_permohonan_id_index` (`permohonan_id`),
  INDEX `koordinator_assignment_koordinator_id_index` (`koordinator_id`),
  UNIQUE KEY `koordinator_assignment_permohonan_id_unique` (`permohonan_id`),
  CONSTRAINT `koordinator_assignment_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koordinator_assignment_koordinator_id_foreign` 
    FOREIGN KEY (`koordinator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tim_fasilitasi_assignment` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `tim_fasilitasi_assignment_permohonan_id_index` (`permohonan_id`),
  INDEX `tim_fasilitasi_assignment_user_id_index` (`user_id`),
  UNIQUE KEY `tim_fasilitasi_assignment_permohonan_id_user_id_unique` (`permohonan_id`, `user_id`),
  CONSTRAINT `tim_fasilitasi_assignment_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tim_fasilitasi_assignment_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tim_verifikasi_assignment` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `tim_verifikasi_assignment_permohonan_id_index` (`permohonan_id`),
  INDEX `tim_verifikasi_assignment_user_id_index` (`user_id`),
  UNIQUE KEY `tim_verifikasi_assignment_permohonan_id_user_id_unique` (`permohonan_id`, `user_id`),
  CONSTRAINT `tim_verifikasi_assignment_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tim_verifikasi_assignment_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. DOKUMEN TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `persyaratan_dokumen` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(20) NOT NULL,
  `nama` VARCHAR(200) NOT NULL,
  `deskripsi` TEXT NULL,
  `is_wajib` TINYINT(1) NOT NULL DEFAULT 1,
  `urutan` INT NOT NULL DEFAULT 0,
  `template_file` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `persyaratan_dokumen_is_wajib_index` (`is_wajib`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permohonan_dokumen` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `master_kelengkapan_id` BIGINT UNSIGNED NOT NULL,
  `is_ada` TINYINT(1) NOT NULL DEFAULT 0,
  `file_path` VARCHAR(255) NULL,
  `file_name` VARCHAR(255) NULL,
  `file_size` VARCHAR(255) NULL,
  `file_type` VARCHAR(255) NULL,
  `status_verifikasi` ENUM('pending', 'verified', 'rejected', 'revision') NOT NULL DEFAULT 'pending',
  `catatan_verifikasi` TEXT NULL,
  `verified_by` BIGINT UNSIGNED NULL,
  `verified_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permohonan_kelengkapan_unique` (`permohonan_id`, `master_kelengkapan_id`),
  CONSTRAINT `permohonan_dokumen_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permohonan_dokumen_master_kelengkapan_id_foreign` 
    FOREIGN KEY (`master_kelengkapan_id`) REFERENCES `master_kelengkapan_verifikasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permohonan_dokumen_verified_by_foreign` 
    FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dokumen_tahapan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `tahapan_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `nama_dokumen` TEXT NOT NULL,
  `file_path` TEXT NOT NULL,
  `file_name` VARCHAR(255) NULL,
  `file_size` BIGINT NULL,
  `file_type` VARCHAR(255) NULL,
  `status` ENUM('menunggu', 'diterima', 'ditolak') NOT NULL DEFAULT 'menunggu',
  `catatan_verifikator` TEXT NULL,
  `verified_by` BIGINT UNSIGNED NULL,
  `verified_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `dokumen_tahapan_permohonan_id_tahapan_id_index` (`permohonan_id`, `tahapan_id`),
  INDEX `dokumen_tahapan_status_index` (`status`),
  INDEX `dokumen_tahapan_created_at_index` (`created_at`),
  CONSTRAINT `dokumen_tahapan_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dokumen_tahapan_tahapan_id_foreign` 
    FOREIGN KEY (`tahapan_id`) REFERENCES `master_tahapan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dokumen_tahapan_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dokumen_tahapan_verified_by_foreign` 
    FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dokumen_verifikasi_detail` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dokumen_tahapan_id` BIGINT UNSIGNED NOT NULL,
  `master_kelengkapan_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('lengkap', 'tidak_lengkap', 'revisi') NOT NULL DEFAULT 'tidak_lengkap',
  `catatan` TEXT NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `dokumen_verifikasi_detail_dokumen_tahapan_id_index` (`dokumen_tahapan_id`),
  INDEX `dokumen_verifikasi_detail_master_kelengkapan_id_index` (`master_kelengkapan_id`),
  UNIQUE KEY `dokumen_kelengkapan_unique` (`dokumen_tahapan_id`, `master_kelengkapan_id`),
  CONSTRAINT `dokumen_verifikasi_detail_dokumen_tahapan_id_foreign` 
    FOREIGN KEY (`dokumen_tahapan_id`) REFERENCES `dokumen_tahapan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dokumen_verifikasi_detail_master_kelengkapan_id_foreign` 
    FOREIGN KEY (`master_kelengkapan_id`) REFERENCES `master_kelengkapan_verifikasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dokumen_verifikasi_detail_updated_by_foreign` 
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dokumen_revisi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dokumen_tahapan_id` BIGINT UNSIGNED NOT NULL,
  `file_path` TEXT NOT NULL,
  `file_name` VARCHAR(255) NULL,
  `file_size` BIGINT NULL,
  `file_type` VARCHAR(255) NULL,
  `alasan_revisi` TEXT NULL,
  `created_by` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `dokumen_revisi_dokumen_tahapan_id_created_at_index` (`dokumen_tahapan_id`, `created_at`),
  CONSTRAINT `dokumen_revisi_dokumen_tahapan_id_foreign` 
    FOREIGN KEY (`dokumen_tahapan_id`) REFERENCES `dokumen_tahapan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dokumen_revisi_created_by_foreign` 
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. SURAT & NOTIFIKASI TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `surat_pemberitahuan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jadwal_fasilitasi_id` BIGINT UNSIGNED NOT NULL,
  `kabupaten_kota_id` BIGINT UNSIGNED NOT NULL,
  `nomor_surat` VARCHAR(50) NOT NULL,
  `tanggal_surat` DATE NOT NULL,
  `perihal` VARCHAR(200) NOT NULL,
  `isi_surat` TEXT NULL,
  `file_path` VARCHAR(255) NULL,
  `status` ENUM('draft', 'sent', 'received') NOT NULL DEFAULT 'draft',
  `sent_at` TIMESTAMP NULL,
  `received_at` TIMESTAMP NULL,
  `created_by` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `surat_pemberitahuan_jadwal_fasilitasi_id_kabupaten_kota_id_index` (`jadwal_fasilitasi_id`, `kabupaten_kota_id`),
  INDEX `surat_pemberitahuan_status_index` (`status`),
  INDEX `surat_pemberitahuan_sent_at_index` (`sent_at`),
  UNIQUE KEY `surat_pemberitahuan_nomor_surat_unique` (`nomor_surat`),
  CONSTRAINT `surat_pemberitahuan_jadwal_fasilitasi_id_foreign` 
    FOREIGN KEY (`jadwal_fasilitasi_id`) REFERENCES `jadwal_fasilitasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_pemberitahuan_kabupaten_kota_id_foreign` 
    FOREIGN KEY (`kabupaten_kota_id`) REFERENCES `kabupaten_kota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_pemberitahuan_created_by_foreign` 
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `surat_rekomendasi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `nomor_surat` VARCHAR(50) NOT NULL,
  `tanggal_surat` DATE NOT NULL,
  `perihal` VARCHAR(200) NOT NULL,
  `isi_surat` TEXT NULL,
  `file_surat` VARCHAR(255) NULL,
  `file_ttd` VARCHAR(255) NULL,
  `file_lampiran` VARCHAR(255) NULL,
  `status` ENUM('draft', 'approved', 'signed', 'sent') NOT NULL DEFAULT 'draft',
  `signed_by` BIGINT UNSIGNED NULL,
  `signed_at` TIMESTAMP NULL,
  `sent_at` TIMESTAMP NULL,
  `created_by` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_rekomendasi_nomor_surat_unique` (`nomor_surat`),
  CONSTRAINT `surat_rekomendasi_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_rekomendasi_signed_by_foreign` 
    FOREIGN KEY (`signed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_rekomendasi_created_by_foreign` 
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifikasi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'info',
  `model_type` VARCHAR(255) NULL,
  `model_id` BIGINT UNSIGNED NULL,
  `action_url` VARCHAR(255) NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `notifikasi_user_id_is_read_index` (`user_id`, `is_read`),
  INDEX `notifikasi_created_at_index` (`created_at`),
  INDEX `notifikasi_model_type_model_id_index` (`model_type`, `model_id`),
  CONSTRAINT `notifikasi_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `whatsapp_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `phone_number` VARCHAR(20) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
  `response` TEXT NULL,
  `sent_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `whatsapp_log_phone_number_index` (`phone_number`),
  INDEX `whatsapp_log_status_index` (`status`),
  INDEX `whatsapp_log_sent_at_index` (`sent_at`),
  CONSTRAINT `whatsapp_log_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. PELAKSANAAN & HASIL FASILITASI TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `laporan_verifikasi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `ringkasan_verifikasi` TEXT NULL,
  `catatan_admin` TEXT NULL,
  `status_kelengkapan` ENUM('lengkap', 'tidak_lengkap') NOT NULL DEFAULT 'lengkap',
  `jumlah_dokumen_verified` INT NOT NULL DEFAULT 0,
  `jumlah_dokumen_revision` INT NOT NULL DEFAULT 0,
  `total_dokumen` INT NOT NULL DEFAULT 0,
  `dibuat_oleh` BIGINT UNSIGNED NOT NULL,
  `tanggal_laporan` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `laporan_verifikasi_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laporan_verifikasi_dibuat_oleh_foreign` 
    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `penetapan_jadwal_fasilitasi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `jadwal_fasilitasi_id` BIGINT UNSIGNED NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `lokasi` VARCHAR(255) NULL,
  `latitude` DECIMAL(10, 8) NULL,
  `longitude` DECIMAL(11, 8) NULL,
  `catatan` TEXT NULL,
  `ditetapkan_oleh` BIGINT UNSIGNED NOT NULL,
  `tanggal_penetapan` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `penetapan_jadwal_fasilitasi_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penetapan_jadwal_fasilitasi_jadwal_fasilitasi_id_foreign` 
    FOREIGN KEY (`jadwal_fasilitasi_id`) REFERENCES `jadwal_fasilitasi` (`id`) ON DELETE SET NULL,
  CONSTRAINT `penetapan_jadwal_fasilitasi_ditetapkan_oleh_foreign` 
    FOREIGN KEY (`ditetapkan_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `undangan_pelaksanaan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `penetapan_jadwal_id` BIGINT UNSIGNED NOT NULL,
  `nomor_undangan` VARCHAR(255) NOT NULL,
  `perihal` TEXT NOT NULL,
  `isi_undangan` TEXT NULL,
  `file_undangan` VARCHAR(255) NULL,
  `status` ENUM('draft', 'terkirim') NOT NULL DEFAULT 'draft',
  `dibuat_oleh` BIGINT UNSIGNED NOT NULL,
  `tanggal_dibuat` TIMESTAMP NULL,
  `tanggal_dikirim` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `undangan_pelaksanaan_nomor_undangan_unique` (`nomor_undangan`),
  CONSTRAINT `undangan_pelaksanaan_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `undangan_pelaksanaan_penetapan_jadwal_id_foreign` 
    FOREIGN KEY (`penetapan_jadwal_id`) REFERENCES `penetapan_jadwal_fasilitasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `undangan_pelaksanaan_dibuat_oleh_foreign` 
    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `undangan_penerima` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `undangan_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `jenis_penerima` ENUM('verifikator', 'fasilitator', 'pemohon') NOT NULL DEFAULT 'pemohon',
  `dibaca` TINYINT(1) NOT NULL DEFAULT 0,
  `tanggal_dibaca` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `undangan_penerima_undangan_id_user_id_unique` (`undangan_id`, `user_id`),
  CONSTRAINT `undangan_penerima_undangan_id_foreign` 
    FOREIGN KEY (`undangan_id`) REFERENCES `undangan_pelaksanaan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `undangan_penerima_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pelaksanaan_catatan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `berita_acara_file` TEXT NULL,
  `notulensi_file` TEXT NULL,
  `dokumentasi_file` TEXT NULL,
  `absensi_file` TEXT NULL,
  `keterangan` TEXT NULL,
  `dibuat_oleh` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `pelaksanaan_catatan_permohonan_id_index` (`permohonan_id`),
  CONSTRAINT `pelaksanaan_catatan_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pelaksanaan_catatan_dibuat_oleh_foreign` 
    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fasilitasi_bab` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `bab_id` BIGINT UNSIGNED NOT NULL,
  `catatan` TEXT NULL,
  `dibuat_oleh` BIGINT UNSIGNED NOT NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fasilitasi_bab_permohonan_id_bab_id_index` (`permohonan_id`, `bab_id`),
  UNIQUE KEY `fasilitasi_bab_permohonan_id_bab_id_unique` (`permohonan_id`, `bab_id`),
  CONSTRAINT `fasilitasi_bab_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fasilitasi_bab_bab_id_foreign` 
    FOREIGN KEY (`bab_id`) REFERENCES `master_bab` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fasilitasi_bab_dibuat_oleh_foreign` 
    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fasilitasi_bab_updated_by_foreign` 
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fasilitasi_urusan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `urusan_id` BIGINT UNSIGNED NOT NULL,
  `kondisi_umum` TEXT NULL,
  `permasalahan` TEXT NULL,
  `analisis_kinerja` TEXT NULL,
  `kesesuaian_dokumen` TEXT NULL,
  `rekomendasi` TEXT NULL,
  `dibuat_oleh` BIGINT UNSIGNED NOT NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fasilitasi_urusan_permohonan_id_urusan_id_index` (`permohonan_id`, `urusan_id`),
  UNIQUE KEY `fasilitasi_urusan_permohonan_id_urusan_id_unique` (`permohonan_id`, `urusan_id`),
  CONSTRAINT `fasilitasi_urusan_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fasilitasi_urusan_urusan_id_foreign` 
    FOREIGN KEY (`urusan_id`) REFERENCES `master_urusan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fasilitasi_urusan_dibuat_oleh_foreign` 
    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fasilitasi_urusan_updated_by_foreign` 
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hasil_fasilitasi` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `draft_file` TEXT NULL,
  `final_file` TEXT NULL,
  `surat_penyampaian` VARCHAR(255) NULL,
  `surat_dibuat_oleh` BIGINT UNSIGNED NULL,
  `surat_tanggal` TIMESTAMP NULL,
  `catatan` TEXT NULL,
  `dibuat_oleh` BIGINT UNSIGNED NOT NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `hasil_fasilitasi_permohonan_id_index` (`permohonan_id`),
  UNIQUE KEY `hasil_fasilitasi_permohonan_id_unique` (`permohonan_id`),
  CONSTRAINT `hasil_fasilitasi_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hasil_fasilitasi_surat_dibuat_oleh_foreign` 
    FOREIGN KEY (`surat_dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hasil_fasilitasi_dibuat_oleh_foreign` 
    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hasil_fasilitasi_updated_by_foreign` 
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hasil_fasilitasi_sistematika` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `hasil_fasilitasi_id` BIGINT UNSIGNED NOT NULL,
  `master_bab_id` BIGINT UNSIGNED NULL,
  `sub_bab` VARCHAR(255) NULL,
  `bab_sub_bab` VARCHAR(255) NULL,
  `catatan_penyempurnaan` TEXT NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `hasil_fasilitasi_sistematika_hasil_fasilitasi_id_foreign` 
    FOREIGN KEY (`hasil_fasilitasi_id`) REFERENCES `hasil_fasilitasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hasil_fasilitasi_sistematika_master_bab_id_foreign` 
    FOREIGN KEY (`master_bab_id`) REFERENCES `master_bab` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hasil_fasilitasi_sistematika_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hasil_fasilitasi_urusan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `hasil_fasilitasi_id` BIGINT UNSIGNED NOT NULL,
  `master_urusan_id` BIGINT UNSIGNED NOT NULL,
  `catatan_masukan` TEXT NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `hasil_fasilitasi_urusan_hasil_fasilitasi_id_foreign` 
    FOREIGN KEY (`hasil_fasilitasi_id`) REFERENCES `hasil_fasilitasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hasil_fasilitasi_urusan_master_urusan_id_foreign` 
    FOREIGN KEY (`master_urusan_id`) REFERENCES `master_urusan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hasil_fasilitasi_urusan_user_id_foreign` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `penetapan_perda` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `nomor_perda` VARCHAR(100) NOT NULL,
  `tanggal_penetapan` DATE NOT NULL,
  `file_perda` TEXT NOT NULL,
  `keterangan` TEXT NULL,
  `dibuat_oleh` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `penetapan_perda_permohonan_id_index` (`permohonan_id`),
  INDEX `penetapan_perda_tanggal_penetapan_index` (`tanggal_penetapan`),
  UNIQUE KEY `penetapan_perda_permohonan_id_unique` (`permohonan_id`),
  CONSTRAINT `penetapan_perda_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penetapan_perda_dibuat_oleh_foreign` 
    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tindak_lanjut` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `keterangan` TEXT NOT NULL,
  `file_laporan` VARCHAR(255) NOT NULL,
  `tanggal_upload` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diupload_oleh` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `tindak_lanjut_permohonan_id_foreign` 
    FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tindak_lanjut_diupload_oleh_foreign` 
    FOREIGN KEY (`diupload_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. ACTIVITY LOG & RICH TEXT TABLES
-- =====================================================

CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_name` VARCHAR(255) NULL,
  `description` TEXT NOT NULL,
  `subject_type` VARCHAR(255) NULL,
  `subject_id` BIGINT UNSIGNED NULL,
  `causer_type` VARCHAR(255) NULL,
  `causer_id` BIGINT UNSIGNED NULL,
  `properties` JSON NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `subject` (`subject_type`, `subject_id`),
  INDEX `causer` (`causer_type`, `causer_id`),
  INDEX `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rich_texts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_type` VARCHAR(255) NOT NULL,
  `record_id` BIGINT UNSIGNED NOT NULL,
  `field` VARCHAR(255) NOT NULL,
  `body` LONGTEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rich_texts_field_record_type_record_id_unique` (`field`, `record_type`, `record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- END OF SCHEMA
-- =====================================================
