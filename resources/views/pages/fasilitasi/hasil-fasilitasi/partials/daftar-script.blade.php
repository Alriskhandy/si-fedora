{{--
    Skrip daftar masukan hasil fasilitasi (halaman input dan detail):
    tab aktif diingat lewat hash #tab-..., saring & urutkan masukan, konfirmasi form .js-konfirmasi, dan preview PDF.
--}}
<script>
    (function() {
        const currentUserId = {{ Auth::id() }};

        async function konfirmasi(pesan) {
            if (!window.Swal) {
                return confirm(pesan);
            }
            const hasil = await Swal.fire({
                icon: 'question', text: pesan, showCancelButton: true, reverseButtons: true,
                confirmButtonText: 'Ya, lanjutkan', cancelButtonText: 'Batal', confirmButtonColor: '#696cff',
            });
            return hasil.isConfirmed;
        }

        // ============================================================
        // TAB AKTIF DIINGAT LEWAT HASH
        // ============================================================
        document.querySelectorAll('[data-hash]').forEach((tombol) => {
            tombol.addEventListener('shown.bs.tab', () => history.replaceState(null, '', '#' + tombol.dataset.hash));
        });
        const tombolTabAwal = document.querySelector(`[data-hash="${location.hash.replace('#', '')}"]`);
        if (tombolTabAwal) {
            bootstrap.Tab.getOrCreateInstance(tombolTabAwal).show();
        }

        // ============================================================
        // SARING & URUTKAN
        // ============================================================
        function terapkanSaringan(bagian) {
            const bilah = document.querySelector(`.js-filter[data-bagian="${bagian}"]`);
            const daftar = document.querySelector(`.js-list[data-bagian="${bagian}"]`);
            if (!bilah || !daftar) {
                return;
            }
            const saring = bilah.querySelector('[data-saring].active')?.dataset.saring || 'semua';
            const idBagian = bilah.querySelector('.js-saring-bagian')?.value || '';
            const urutan = bilah.querySelector('.js-urutkan')?.value || 'urutan';
            let jumlahTampil = 0;

            daftar.querySelectorAll('tbody').forEach((tbody) => {
                const baris = [...tbody.querySelectorAll('tr.js-item')];
                baris.sort((a, b) => urutan === 'waktu'
                    ? a.dataset.createdAt - b.dataset.createdAt
                    : (a.dataset.urutan - b.dataset.urutan) || (a.dataset.createdAt - b.dataset.createdAt));

                let nomor = 1;
                baris.forEach((row) => {
                    tbody.appendChild(row);
                    const cocokPengirim = saring === 'semua'
                        || (saring === 'saya' && Number(row.dataset.userId) === currentUserId)
                        || (saring === 'opd' && row.dataset.opd === '1');
                    const cocok = cocokPengirim && (!idBagian || row.dataset.bagianId === idBagian);
                    row.classList.toggle('d-none', !cocok);
                    if (cocok) {
                        row.querySelector('.js-no').textContent = nomor++;
                    }
                });

                const grup = tbody.closest('.js-grup');
                if (grup) {
                    grup.classList.toggle('d-none', nomor === 1);
                    grup.querySelector('.js-grup-jumlah').textContent = nomor - 1;
                }
                jumlahTampil += nomor - 1;
            });

            daftar.classList.toggle('d-none', jumlahTampil === 0);
            document.querySelector(`.js-filter-kosong[data-bagian="${bagian}"]`).classList.toggle('d-none', jumlahTampil > 0);
        }

        document.querySelectorAll('.js-filter').forEach((bilah) => {
            const bagian = bilah.dataset.bagian;
            bilah.querySelectorAll('[data-saring]').forEach((tombol) => {
                tombol.addEventListener('click', () => {
                    bilah.querySelectorAll('[data-saring]').forEach((t) => t.classList.toggle('active', t === tombol));
                    terapkanSaringan(bagian);
                });
            });
            bilah.querySelectorAll('select').forEach((select) => select.addEventListener('change', () => terapkanSaringan(bagian)));
        });

        // ============================================================
        // KONFIRMASI FORM & PREVIEW PDF
        // ============================================================
        document.querySelectorAll('form.js-konfirmasi').forEach((form) => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (await konfirmasi(form.dataset.konfirmasi)) {
                    form.submit();
                }
            });
        });

        // Preview PDF dimuat saat modal dibuka, dengan versi baru agar tidak memakai cache
        document.getElementById('previewPdfModal')?.addEventListener('show.bs.modal', () => {
            const iframe = document.getElementById('pdfPreviewFrame');
            if (iframe) {
                iframe.src = iframe.dataset.src + '?v=' + Date.now();
            }
        });
    })();
</script>
