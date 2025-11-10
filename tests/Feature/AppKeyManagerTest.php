<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AppKeyManagerTest extends TestCase
{
    public function test_app_key_is_ensured_and_valid(): void
    {
        // Bootstrap already calls ensure(); assert presence & validity
        $key = env('APP_KEY');
        $this->assertNotEmpty($key, 'APP_KEY should be set');

        $cipher = strtolower(env('APP_CIPHER', 'AES-256-CBC'));
        $raw = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
        $this->assertIsString($raw);
        $this->assertContains(strlen($raw), $cipher === 'aes-128-cbc' || $cipher === 'aes-128-gcm' ? [16] : [32]);
        $this->assertSame($key, Config::get('app.key'));
    }
}
