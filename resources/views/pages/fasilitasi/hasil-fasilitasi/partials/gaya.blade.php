{{-- Gaya bersama halaman input dan detail hasil fasilitasi. --}}
<style>
    @media (min-width: 992px) {
        .border-lg-start {
            border-left: 1px solid #d9dee3;
        }
    }

    .masukan-tabel td.catatan-cell p:last-child {
        margin-bottom: 0;
    }

    .masukan-tabel tr.js-sorot td {
        background-color: rgba(113, 221, 55, .16);
        transition: background-color 1.5s;
    }

    .grup-urusan-header {
        background-color: #f5f5f9;
    }

    /* Dialog SweetAlert tampil di atas modal Bootstrap */
    .swal2-container {
        z-index: 2000 !important;
    }

    .nav-tabs .penanda-saya {
        display: inline-block;
        width: .45rem;
        height: .45rem;
        border-radius: 50%;
        vertical-align: middle;
    }
</style>
