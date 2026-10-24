<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Test karakterisasi: merekam role mana yang ditolak (403) oleh middleware route.
 *
 * Route model binding dimatikan dan action setiap route diganti respons kosong, sehingga
 * yang teramati hanya lapisan middleware otorisasi (bukan pengecekan di dalam controller).
 * Yang dicatat hanya "ditolak (403)" atau "tidak ditolak". Snapshot disimpan di
 * tests/Fixtures/route-authorization.json. Perubahan hak akses yang disengaja
 * dilakukan dengan menjalankan ulang test dengan UPDATE_ROUTE_SNAPSHOT=1 dan
 * me-review diff snapshot tersebut.
 */
#[Group('slow')]
class RouteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private const TANPA_ROLE = '(tanpa role)';

    private const SNAPSHOT = __DIR__.'/../Fixtures/route-authorization.json';

    public function test_route_access_per_role_matches_snapshot(): void
    {
        $this->withoutMiddleware(SubstituteBindings::class);

        $this->artisan('permission:sync', ['--reset' => true]);

        $users = [self::TANPA_ROLE => User::factory()->create()];
        foreach (config('permissions.roles') as $role) {
            $users[$role] = User::factory()->withRole($role)->create();
        }

        $actual = [];
        foreach ($this->applicationRoutes() as $key => $route) {
            $allowed = [];

            foreach ($users as $label => $user) {
                $status = $this->actingAs($user)->call($route['method'], $route['uri'])->getStatusCode();

                if ($status !== 403) {
                    $allowed[] = $label;
                }
            }

            $actual[$key] = $allowed;
        }

        if (getenv('UPDATE_ROUTE_SNAPSHOT')) {
            if (! is_dir(dirname(self::SNAPSHOT))) {
                mkdir(dirname(self::SNAPSHOT), 0755, true);
            }
            file_put_contents(self::SNAPSHOT, json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
            $this->markTestSkipped('Snapshot route-authorization.json diperbarui.');
        }

        $this->assertFileExists(self::SNAPSHOT, 'Jalankan dengan UPDATE_ROUTE_SNAPSHOT=1 untuk membuat snapshot.');
        $this->assertSame(json_decode(file_get_contents(self::SNAPSHOT), true), $actual);
    }

    /**
     * @return array<string, array{method: string, uri: string}>
     */
    private function applicationRoutes(): array
    {
        $routes = [];

        /** @var Route $route */
        foreach (RouteFacade::getRoutes() as $route) {
            $action = $route->getActionName();

            if (! str_starts_with($action, 'App\\') && $action !== 'Closure') {
                continue;
            }

            if (! in_array('auth', $route->gatherMiddleware(), true)) {
                continue;
            }

            $route->uses(fn () => response()->noContent());

            $method = collect($route->methods())->reject(fn (string $m) => $m === 'HEAD')->first();
            $uri = '/'.ltrim(preg_replace('/\{[^}]+\}/', '1', $route->uri()), '/');

            $routes["{$method} {$route->uri()}"] = ['method' => $method, 'uri' => $uri];
        }

        ksort($routes);

        return $routes;
    }
}
