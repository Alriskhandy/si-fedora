{{--
    Skrip halaman input hasil fasilitasi.
    - Editor TinyMCE dimuat saat form/modal dibuka (bukan saat halaman dimuat).
    - Isian form tambah disimpan sebagai draf di sessionStorage dan dipulihkan setelah muat ulang.
    - Setelah tambah/ubah/hapus, halaman dimuat ulang pada tab yang sama (hash #tab-...) dan item disorot.
    Tab, saringan, konfirmasi form, dan preview PDF ditangani partial daftar-script (di-include lebih dulu).
    Variabel: $permohonan.
--}}
<script>
    (function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const KUNCI_PESAN = 'hasil-fasilitasi:pesan';
        const kunciDraf = (bagian) => `hasil-fasilitasi:{{ $permohonan->id }}:draf:${bagian}`;
        let sedangMuatUlang = false;

        const penyimpanan = {
            ambil(kunci) {
                try { return JSON.parse(sessionStorage.getItem(kunci)); } catch (e) { return null; }
            },
            simpan(kunci, nilai) {
                try { sessionStorage.setItem(kunci, JSON.stringify(nilai)); } catch (e) {}
            },
            hapus(kunci) {
                try { sessionStorage.removeItem(kunci); } catch (e) {}
            },
        };

        // ============================================================
        // NOTIFIKASI & KONFIRMASI
        // ============================================================
        function notifikasi(ikon, pesan) {
            if (!window.Swal) {
                alert(pesan);
                return;
            }
            Swal.fire({
                toast: true, position: 'top-end', icon: ikon, title: pesan,
                showConfirmButton: false, timer: 3000, timerProgressBar: true,
            });
        }

        async function konfirmasi(pesan, teksTombol = 'Ya, lanjutkan', bahaya = false) {
            if (!window.Swal) {
                return confirm(pesan);
            }
            const hasil = await Swal.fire({
                icon: bahaya ? 'warning' : 'question', text: pesan, showCancelButton: true, reverseButtons: true,
                confirmButtonText: teksTombol, cancelButtonText: 'Batal', confirmButtonColor: bahaya ? '#ff3e1d' : '#696cff',
            });
            return hasil.isConfirmed;
        }

        async function kirim(url, metode, isi = null) {
            const respons = await fetch(url, {
                method: metode,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: isi ? JSON.stringify(isi) : undefined,
            });
            const data = await respons.json().catch(() => ({}));
            if (!respons.ok || !data.success) {
                const galatValidasi = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(data.error || galatValidasi || data.message || 'Terjadi kesalahan, coba lagi.');
            }
            return data;
        }

        function muatUlang(pesan, sorot = null) {
            penyimpanan.simpan(KUNCI_PESAN, { pesan, sorot });
            sedangMuatUlang = true;
            location.reload();
        }

        // ============================================================
        // EDITOR (TinyMCE, dimuat saat dibutuhkan)
        // ============================================================
        const opsiEditor = {
            height: 220,
            menubar: false,
            plugins: ['advlist', 'autolink', 'lists', 'searchreplace', 'wordcount'],
            toolbar: 'undo redo | bold italic underline strikethrough | bullist numlist outdent indent | removeformat',
            // Font dan ukuran disesuaikan dengan dokumen Word/PDF yang dihasilkan
            content_style: 'body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.5; margin: 8px; }' +
                'p { margin: 0 0 4px 0; } ul, ol { margin: 2px 0; padding-left: 20px; }',
            branding: false,
            promotion: false,
            statusbar: false,
            // Hanya izinkan elemen yang dirender dengan baik oleh PhpWord dan DomPDF
            valid_elements: 'p,br,strong/b,em/i,u,s/strike/del,ul,ol,li,span[style],sub,sup',
            valid_styles: { '*': 'font-weight,font-style,text-decoration' },
            paste_as_text: false,
            paste_webkit_styles: 'font-weight font-style text-decoration',
            paste_retain_style_properties: 'font-weight font-style text-decoration',
        };

        function siapkanEditor(id) {
            const editor = tinymce.get(id);
            if (editor) {
                return Promise.resolve(editor);
            }
            return tinymce.init({ ...opsiEditor, selector: '#' + id }).then((editors) => editors[0]);
        }

        const isiEditor = (id) => (tinymce.get(id)?.getContent() ?? document.getElementById(id)?.value ?? '').trim();

        const pesanTertunda = penyimpanan.ambil(KUNCI_PESAN);
        if (pesanTertunda) {
            penyimpanan.hapus(KUNCI_PESAN);
            notifikasi('success', pesanTertunda.pesan);
            const sorot = pesanTertunda.sorot;
            const baris = sorot && document.querySelector(`.js-item[data-jenis="${sorot.jenis}"][data-id="${sorot.id}"]`);
            if (baris) {
                baris.classList.add('js-sorot');
                baris.scrollIntoView({ block: 'center' });
                setTimeout(() => baris.classList.remove('js-sorot'), 2500);
            }
        }

        // ============================================================
        // FORM TAMBAH MASUKAN (dengan draf otomatis)
        // ============================================================
        document.querySelectorAll('.js-tambah').forEach((collapse) => {
            const form = collapse.querySelector('.js-form-tambah');
            const bagian = form.dataset.bagian;
            const textarea = form.querySelector('textarea');
            const statusDraf = form.querySelector('.js-status-draf');

            const simpanDraf = () => {
                const draf = { kolom: {}, catatan: isiEditor(textarea.id) };
                form.querySelectorAll('[name]').forEach((el) => draf.kolom[el.name] = el.value);
                const kosong = !draf.catatan && Object.values(draf.kolom).every((nilai) => !nilai);
                kosong ? penyimpanan.hapus(kunciDraf(bagian)) : penyimpanan.simpan(kunciDraf(bagian), draf);
                statusDraf.textContent = kosong ? '' : 'Draf tersimpan otomatis';
            };

            const draf = penyimpanan.ambil(kunciDraf(bagian));
            if (draf) {
                Object.entries(draf.kolom || {}).forEach(([nama, nilai]) => {
                    const el = form.querySelector(`[name="${nama}"]`);
                    if (el) {
                        el.value = nilai;
                    }
                });
                textarea.value = draf.catatan || '';
                statusDraf.textContent = 'Draf sebelumnya dipulihkan';
            }

            collapse.addEventListener('shown.bs.collapse', (e) => {
                if (e.target !== collapse) {
                    return;
                }
                siapkanEditor(textarea.id).then((editor) => {
                    if (!textarea.dataset.drafTerpasang) {
                        textarea.dataset.drafTerpasang = '1';
                        editor.on('input change undo redo', simpanDraf);
                    }
                    editor.focus();
                });
            });

            if (draf) {
                bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false }).show();
            }

            form.querySelectorAll('[name]').forEach((el) => el.addEventListener('input', simpanDraf));
            form.querySelector('.js-batal-tambah').addEventListener('click', () => {
                bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false }).hide();
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const isi = {};
                for (const el of form.querySelectorAll('[name]')) {
                    if (el.required && !el.value) {
                        const label = form.querySelector(`label[for="${el.id}"]`)?.textContent.replace('*', '').trim();
                        notifikasi('error', `${label} wajib dipilih.`);
                        el.focus();
                        return;
                    }
                    isi[el.name] = el.value;
                }
                const catatan = tinymce.get(textarea.id)?.getContent() ?? '';
                if (!catatan.trim()) {
                    notifikasi('error', 'Catatan wajib diisi.');
                    tinymce.get(textarea.id)?.focus();
                    return;
                }
                isi[textarea.dataset.kolom] = catatan;

                const tombol = form.querySelector('[type="submit"]');
                tombol.disabled = true;
                try {
                    const data = await kirim(form.dataset.url, 'POST', isi);
                    penyimpanan.hapus(kunciDraf(bagian));
                    muatUlang(data.message, { jenis: bagian, id: data.data?.id });
                } catch (err) {
                    notifikasi('error', err.message);
                    tombol.disabled = false;
                }
            });
        });

        // Cari opsi pada dropdown panjang (urusan)
        document.querySelectorAll('.js-cari-opsi').forEach((input) => {
            const select = document.getElementById(input.dataset.target);
            const semuaOpsi = [...select.options].map((opsi) => ({ value: opsi.value, text: opsi.text }));
            input.addEventListener('input', () => {
                const kata = input.value.trim().toLowerCase();
                const terpilih = select.value;
                select.innerHTML = '';
                semuaOpsi.filter((opsi) => !opsi.value || opsi.text.toLowerCase().includes(kata))
                    .forEach((opsi) => select.add(new Option(opsi.text, opsi.value, false, opsi.value === terpilih)));
                if (kata && !select.value && select.options.length === 2) {
                    select.selectedIndex = 1;
                }
            });
        });

        // Daftar urusan bidang OPD yang belum diisi: klik untuk langsung menambah masukan
        document.querySelectorAll('.js-tambah-urusan').forEach((tombol) => {
            tombol.addEventListener('click', () => {
                const pencarian = document.querySelector('.js-cari-opsi[data-target="master_urusan_id"]');
                if (pencarian?.value) {
                    pencarian.value = '';
                    pencarian.dispatchEvent(new Event('input'));
                }
                const select = document.getElementById('master_urusan_id');
                select.value = tombol.dataset.urusanId;
                select.dispatchEvent(new Event('input'));
                const collapse = document.getElementById('tambah-urusan');
                bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false }).show();
                collapse.scrollIntoView({ behavior: 'smooth', block: 'start' });
                siapkanEditor('catatan_masukan').then((editor) => editor.focus());
            });
        });

        // ============================================================
        // UBAH & HAPUS MASUKAN
        // ============================================================
        const modalUbah = document.getElementById('modalUbah');
        const judulUbah = {
            sistematika: 'Ubah Masukan Sistematika',
            form: 'Ubah Masukan Konsistensi & Keselarasan',
            urusan: 'Ubah Masukan Urusan Pemerintahan',
            rekomendasi: 'Ubah Rekomendasi',
        };
        const kolomCatatan = {
            sistematika: 'catatan_penyempurnaan', form: 'catatan', urusan: 'catatan_masukan', rekomendasi: 'catatan',
        };
        let barisDiubah = null;
        let isiAwal = '';
        let tutupTanpaTanya = false;

        function pilihOpsi(select, nilai, label) {
            if (![...select.options].some((opsi) => opsi.value === nilai)) {
                select.add(new Option(label, nilai));
            }
            select.value = nilai;
        }

        document.addEventListener('click', async (e) => {
            const tombolUbah = e.target.closest('.js-ubah');
            const tombolHapus = e.target.closest('.js-hapus');
            if (!tombolUbah && !tombolHapus) {
                return;
            }
            const baris = e.target.closest('.js-item');

            if (tombolHapus) {
                if (!(await konfirmasi('Hapus masukan ini? Tindakan ini tidak dapat dibatalkan.', 'Ya, hapus', true))) {
                    return;
                }
                try {
                    const data = await kirim(baris.dataset.url, 'DELETE');
                    muatUlang(data.message);
                } catch (err) {
                    notifikasi('error', err.message);
                }
                return;
            }

            barisDiubah = baris;
            const jenis = baris.dataset.jenis;
            document.getElementById('modalUbahJudul').lastChild.textContent = judulUbah[jenis];
            modalUbah.querySelectorAll('.js-field-ubah').forEach((field) => field.classList.toggle('d-none', field.dataset.untuk !== jenis));
            if (jenis === 'sistematika') {
                pilihOpsi(document.getElementById('ubah_master_bab_id'), baris.dataset.bagianId, baris.dataset.bagianLabel);
                document.getElementById('ubah_sub_bab').value = baris.dataset.subBab || '';
            }
            if (jenis === 'urusan') {
                pilihOpsi(document.getElementById('ubah_master_urusan_id'), baris.dataset.bagianId, baris.dataset.bagianLabel);
            }

            const isiCatatan = baris.querySelector('.catatan-cell').innerHTML;
            const editor = tinymce.get('ubah_catatan');
            editor ? editor.setContent(isiCatatan) : document.getElementById('ubah_catatan').value = isiCatatan;
            document.getElementById('btnSimpanUbah').disabled = false;
            bootstrap.Modal.getOrCreateInstance(modalUbah).show();
            siapkanEditor('ubah_catatan').then((siap) => isiAwal = siap.getContent());
        });

        modalUbah.addEventListener('hide.bs.modal', async (e) => {
            if (tutupTanpaTanya || sedangMuatUlang) {
                tutupTanpaTanya = false;
                return;
            }
            const editor = tinymce.get('ubah_catatan');
            if (editor && editor.getContent() !== isiAwal) {
                e.preventDefault();
                if (await konfirmasi('Perubahan belum disimpan. Tutup tanpa menyimpan?', 'Ya, tutup', true)) {
                    tutupTanpaTanya = true;
                    bootstrap.Modal.getInstance(modalUbah).hide();
                }
            }
        });

        document.getElementById('btnSimpanUbah').addEventListener('click', async function() {
            const jenis = barisDiubah.dataset.jenis;
            const catatan = tinymce.get('ubah_catatan')?.getContent() ?? '';
            if (!catatan.trim()) {
                notifikasi('error', 'Catatan wajib diisi.');
                return;
            }
            const isi = { [kolomCatatan[jenis]]: catatan };
            if (jenis === 'sistematika') {
                isi.master_bab_id = document.getElementById('ubah_master_bab_id').value;
                isi.sub_bab = document.getElementById('ubah_sub_bab').value;
            }
            if (jenis === 'urusan') {
                isi.master_urusan_id = document.getElementById('ubah_master_urusan_id').value;
            }

            this.disabled = true;
            try {
                const data = await kirim(barisDiubah.dataset.url, 'PUT', isi);
                muatUlang(data.message, { jenis, id: barisDiubah.dataset.id });
            } catch (err) {
                notifikasi('error', err.message);
                this.disabled = false;
            }
        });

        // ============================================================
        // LAIN-LAIN
        // ============================================================
        // Peringatkan bila meninggalkan halaman dengan isian form tambah yang belum disimpan
        window.addEventListener('beforeunload', (e) => {
            if (sedangMuatUlang) {
                return;
            }
            const adaIsian = [...document.querySelectorAll('.js-form-tambah textarea')].some((t) => isiEditor(t.id));
            if (adaIsian) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    })();
</script>
