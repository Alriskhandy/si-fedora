<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(HtmlSanitizer::class, function ($app) {
            $htmlSanitizerConfig = (new HtmlSanitizerConfig())
                // Allow safe elements
                ->allowSafeElements()
                
                // Block elements
                ->allowElement('div', ['class', 'style', 'data-*'])
                ->allowElement('p', ['class', 'style'])
                ->allowElement('br')
                ->allowElement('hr')
                
                // Text formatting
                ->allowElement('strong')
                ->allowElement('b')
                ->allowElement('em')
                ->allowElement('i')
                ->allowElement('u')
                ->allowElement('s')
                ->allowElement('strike')
                ->allowElement('del')
                ->allowElement('mark')
                ->allowElement('small')
                
                // Headings
                ->allowElement('h1', ['class', 'style'])
                ->allowElement('h2', ['class', 'style'])
                ->allowElement('h3', ['class', 'style'])
                ->allowElement('h4', ['class', 'style'])
                ->allowElement('h5', ['class', 'style'])
                ->allowElement('h6', ['class', 'style'])
                
                // Lists
                ->allowElement('ul', ['class', 'style'])
                ->allowElement('ol', ['class', 'style'])
                ->allowElement('li', ['class', 'style'])
                
                // Links
                ->allowElement('a', ['href', 'title', 'rel', 'target', 'class'])
                
                // Quotes and code
                ->allowElement('blockquote', ['class', 'style'])
                ->allowElement('pre', ['class'])
                ->allowElement('code', ['class'])
                
                // Tables (TinyMCE support)
                ->allowElement('table', ['class', 'style', 'border', 'cellpadding', 'cellspacing'])
                ->allowElement('thead', ['class', 'style'])
                ->allowElement('tbody', ['class', 'style'])
                ->allowElement('tfoot', ['class', 'style'])
                ->allowElement('tr', ['class', 'style'])
                ->allowElement('th', ['class', 'style', 'colspan', 'rowspan', 'scope'])
                ->allowElement('td', ['class', 'style', 'colspan', 'rowspan'])
                
                // Inline elements
                ->allowElement('span', ['class', 'style'])
                ->allowElement('sub')
                ->allowElement('sup')
                
                // Images (if needed)
                ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'class', 'style'])
                
                // Allow link and media hosts
                ->allowLinkHosts(['*'])
                ->allowLinkSchemes(['http', 'https', 'mailto'])
                ->allowMediaHosts(['*'])
                ->allowMediaSchemes(['http', 'https', 'data'])
                
                // Don't force HTTPS (allow HTTP links)
                ->forceHttpsUrls(false)
                
                // Allow specific attributes globally
                ->allowAttribute('class', '*')
                ->allowAttribute('style', '*');

            return new HtmlSanitizer($htmlSanitizerConfig);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
