<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerAction;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Mendaftarkan HtmlSanitizer (symfony/html-sanitizer) sebagai singleton.
 *
 * Dipakai oleh cast App\Casts\SanitizedHtml dan command richtext:sanitize.
 */
class HtmlSanitizerServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan sanitizer dengan whitelist elemen.
     *
     * Whitelist mengikuti `valid_elements` TinyMCE di form hasil fasilitasi
     * (resources/views/pages/hasil-fasilitasi/create.blade.php), agar konten yang
     * disimpan sama dengan yang dapat dihasilkan editor dan aman diekspor ke Word/PDF.
     */
    public function register(): void
    {
        $this->app->singleton(HtmlSanitizer::class, function ($app) {
            $config = (new HtmlSanitizerConfig)
                // Elemen lain dihapus, tetapi teks di dalamnya dipertahankan
                ->defaultAction(HtmlSanitizerAction::Block)
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('b')
                ->allowElement('em')
                ->allowElement('i')
                ->allowElement('u')
                ->allowElement('s')
                ->allowElement('strike')
                ->allowElement('del')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('span', ['style'])
                ->allowElement('sub')
                ->allowElement('sup')
                // Elemen berbahaya dibuang beserta isinya
                ->dropElement('script')
                ->dropElement('style')
                ->dropElement('iframe')
                ->dropElement('object')
                ->dropElement('embed')
                ->dropElement('noscript')
                ->dropElement('template')
                ->dropElement('svg')
                ->dropElement('math');

            return new HtmlSanitizer($config);
        });
    }
}
