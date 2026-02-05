# Alur Teknis Sistem Si-Fedora

## A. Alur Umum Sistem
1. User login
2. Sistem memverifikasi role
3. Sistem menampilkan dashboard sesuai role
4. User melakukan input / update data
5. Data disimpan ke database
6. Data ditampilkan ke user lain sesuai hak akses

## B. Alur per Modul

### 1. Modul Registrasi / Login
**Role terlibat:** Semua role

| Langkah | Aktor | Proses | Data |
|-------|------|-------|------|
| 1 | User | Input kredensial | email, password |
| 2 | Sistem | Validasi | users |
| 3 | Sistem | Set session | roles |

---

### 2. Modul Pengajuan Data
**Role:** Operator → Admin → Pimpinan

| Step | Role | Aksi | Status Data |
|----|----|----|----|
| 1 | Operator | Input data | Draft |
| 2 | Admin | Verifikasi | Disetujui / Ditolak |
| 3 | Pimpinan | Monitoring | Read-only |

