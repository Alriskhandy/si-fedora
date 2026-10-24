<?php

namespace Tests\Feature;

use App\Services\FonteService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FonteServiceTest extends TestCase
{
    public function test_sends_message_with_token_from_config(): void
    {
        Http::preventStrayRequests();
        Http::fake(['https://api.fonnte.com/send' => Http::response(['status' => true])]);
        config(['services.fonnte.token' => 'token-dari-config']);

        $result = app(FonteService::class)->sendMessage('6281234567890', 'Halo');

        $this->assertTrue($result['success']);
        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'token-dari-config')
            && $request['message'] === 'Halo');
    }
}
