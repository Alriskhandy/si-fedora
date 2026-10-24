<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Database yang boleh dikosongkan oleh test (RefreshDatabase menjalankan migrate:fresh).
     */
    private const TESTING_DATABASE = 'sifedora_testing';

    /**
     * Buat aplikasi dan hentikan test sebelum trait database berjalan
     * bila koneksi tidak menunjuk ke database testing.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($database !== self::TESTING_DATABASE) {
            throw new RuntimeException(sprintf(
                'Test dihentikan: koneksi "%s" menunjuk ke database "%s", bukan "%s". Periksa phpunit.xml atau .env.testing.',
                $connection,
                $database,
                self::TESTING_DATABASE,
            ));
        }

        return $app;
    }
}
