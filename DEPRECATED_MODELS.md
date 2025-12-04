# Model yang Deprecated/Tidak Terpakai

Setelah refactoring database (2025_12_04), beberapa model tidak lagi digunakan dalam struktur baru. Berikut adalah daftarnya:

## Model yang Harus Dihapus

### 1. **Evaluasi.php** ❌ DEPRECATED

-   **Alasan**: Tabel `evaluasi` tidak ada dalam struktur baru
-   **Pengganti**: Proses evaluasi sekarang masuk dalam workflow `permohonan_tahapan` dengan tahapan "Hasil Fasilitasi"
-   **Relasi Lama**: permohonan, tahunAnggaran, evaluatedBy
-   **Tindakan**: Hapus file model ini

### 2. **PermohonanDokumen.php** ❌ DEPRECATED

-   **Alasan**: Tabel `permohonan_dokumen` tidak ada dalam struktur baru
-   **Pengganti**: `DokumenTahapan` - dokumen sekarang dikelola per tahapan
-   **Relasi Lama**: permohonan, masterKelengkapan, persyaratanDokumen
-   **Tindakan**: Hapus file model ini

### 3. **PersyaratanDokumen.php** ❌ DEPRECATED

-   **Alasan**: Tabel `persyaratan_dokumen` tidak ada dalam struktur baru
-   **Pengganti**: `MasterKelengkapanVerifikasi` - sudah ada dan diupdate dengan kolom kategori & tahapan_id
-   **Relasi Lama**: jenisDokumen, permohonanDokumen
-   **Tindakan**: Hapus file model ini

### 4. **JenisDokumen.php** ❌ DEPRECATED

-   **Alasan**: Tabel `jenis_dokumen` dihapus, sekarang menggunakan ENUM
-   **Pengganti**: Kolom `jenis_dokumen` di tabel `permohonan` menggunakan ENUM ('perda', 'perkada')
-   **Relasi Lama**: permohonan, jadwalFasilitasi
-   **Tindakan**: Hapus file model ini

### 5. **TahunAnggaran.php** ❌ DEPRECATED

-   **Alasan**: Tabel `tahun_anggaran` dihapus, sekarang menggunakan kolom tahun (integer)
-   **Pengganti**: Kolom `tahun` (integer) di tabel `permohonan`
-   **Relasi Lama**: permohonan, jadwalFasilitasi, evaluasi
-   **Tindakan**: Hapus file model ini

### 6. **SuratPemberitahuan.php** ⚠️ REVIEW NEEDED

-   **Alasan**: Foreign key ke `jadwal_fasilitasi` sudah dihapus di migrasi
-   **Status**: Tabel masih ada tapi struktur berbeda
-   **Catatan**: Perlu review apakah masih digunakan dalam workflow baru
-   **Tindakan**: Review controller/view yang menggunakan model ini

### 7. **SuratRekomendasi.php** ❌ DEPRECATED

-   **Alasan**: Model kosong, tabel tidak ada
-   **Pengganti**: Tidak ada - fitur surat rekomendasi belum diimplementasi
-   **Tindakan**: Hapus file model ini

### 8. **TimPokja.php** ❌ DEPRECATED

-   **Alasan**: Sistem pokja diganti dengan assignment system yang lebih fleksibel
-   **Pengganti**:
    -   `KoordinatorAssignment` - untuk koordinator
    -   `TimFasilitasiAssignment` - untuk tim fasilitasi
    -   `TimVerifikasiAssignment` - untuk tim verifikasi
-   **Relasi Lama**: ketua, anggota, permohonan, evaluasi
-   **Tindakan**: Hapus file model ini

### 9. **PokjaAnggota.php** ❌ DEPRECATED

-   **Alasan**: Bagian dari sistem pokja yang diganti dengan assignment system
-   **Pengganti**: Lihat TimPokja.php
-   **Tindakan**: Hapus file model ini

### 10. **MasterBab.php** ⚠️ REVIEW NEEDED

-   **Alasan**: Fungsinya mungkin tumpang tindih dengan `FasilitasiBab`
-   **Status**: Tabel mungkin masih ada tapi perlu review
-   **Catatan**:
    -   MasterBab = Master data struktur bab dokumen (parent-child)
    -   FasilitasiBab = Tracking pembahasan bab per permohonan
-   **Tindakan**: Review - jika MasterBab tidak digunakan, hapus

## Model yang Masih Digunakan

✅ **Permohonan.php** - Sudah diupdate sesuai struktur baru  
✅ **JadwalFasilitasi.php** - Sudah diupdate (dari global ke per-permohonan)  
✅ **MasterKelengkapanVerifikasi.php** - Sudah diupdate dengan kolom baru (kategori, tahapan_id, urutan)  
✅ **MasterTahapan.php** - Sudah diupdate sesuai struktur baru  
✅ **MasterUrusan.php** - Sudah diupdate sesuai struktur baru  
✅ **KabupatenKota.php** - Tidak berubah, masih digunakan  
✅ **User.php** - Tidak berubah, masih digunakan  
✅ **TemporaryRoleAssignment.php** - Tidak berubah, masih digunakan untuk sistem role sementara

## Model Baru yang Dibuat

✅ **PermohonanTahapan.php** - Tracking status per tahapan  
✅ **PermohonanTahapanLog.php** - Audit trail perubahan tahapan  
✅ **KoordinatorAssignment.php** - Assignment koordinator per permohonan  
✅ **TimFasilitasiAssignment.php** - Assignment tim fasilitasi  
✅ **TimVerifikasiAssignment.php** - Assignment tim verifikasi  
✅ **DokumenTahapan.php** - Dokumen per tahapan  
✅ **DokumenVerifikasiDetail.php** - Detail verifikasi dokumen  
✅ **DokumenRevisi.php** - Tracking revisi dokumen  
✅ **PelaksanaanCatatan.php** - Catatan pelaksanaan fasilitasi  
✅ **HasilFasilitasi.php** - Hasil fasilitasi  
✅ **PenetapanPerda.php** - Data penetapan perda/perkada  
✅ **FasilitasiBab.php** - Tracking pembahasan per bab  
✅ **FasilitasiUrusan.php** - Tracking pembahasan per urusan

## Tindakan Selanjutnya

1. **Hapus model deprecated** (setelah migrasi selesai):

    ```bash
    rm app/Models/Evaluasi.php
    rm app/Models/PermohonanDokumen.php
    rm app/Models/PersyaratanDokumen.php
    rm app/Models/JenisDokumen.php
    rm app/Models/TahunAnggaran.php
    rm app/Models/SuratRekomendasi.php
    rm app/Models/TimPokja.php
    rm app/Models/PokjaAnggota.php
    ```

2. **Review dan update controller** yang masih menggunakan model deprecated

3. **Review SuratPemberitahuan** dan **MasterBab** - tentukan apakah masih digunakan

4. **Update seeder** jika ada yang masih menggunakan model deprecated

5. **Update test** jika ada yang masih menggunakan model deprecated

## Catatan Penting

⚠️ **JANGAN HAPUS MODEL SEBELUM:**

-   Migrasi berhasil dijalankan ✅
-   Controller yang menggunakan model ini sudah diupdate
-   View yang menggunakan model ini sudah diupdate
-   Test yang menggunakan model ini sudah diupdate
