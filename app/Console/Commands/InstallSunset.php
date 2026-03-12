<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallSunset extends Command
{
    protected $signature = 'install:sunset';

    protected $description = 'Generate RSA key pair for JWT and update .env';

    public function handle(): int
    {
        if ($this->jwtKeysAlreadySet()) {
            $this->info('JWT keys already exist in .env, skipping installation.');
            return self::SUCCESS;
        }

        $this->info('Generating RSA key pair (JWT signing)...');
        $keyResource = $this->generateRsaKeyResource();

        if ($keyResource === false) {
            $this->error('Failed to generate JWT RSA key pair. Ensure the openssl extension is enabled.');
            return self::FAILURE;
        }

        openssl_pkey_export($keyResource, $privateKey);
        $publicKey = openssl_pkey_get_details($keyResource)['key'];

        $this->writeEnvValue('JWT_PRIVATE_KEY', '"' . str_replace("\n", '\n', trim($privateKey)) . '"');
        $this->writeEnvValue('JWT_PUBLIC_KEY',  '"' . str_replace("\n", '\n', trim($publicKey)) . '"');

        $this->info('--- JWT Public Key (copy to other services as JWT_PUBLIC_KEY) ---');
        $this->line(trim($publicKey));
        $this->info('-----------------------------------------------------------------');

        $this->writeAesKeysToEnv();

        $this->info('.env updated with JWT keys and AES keys.');

        return self::SUCCESS;
    }

    private function jwtKeysAlreadySet(): bool
    {
        $envPath  = base_path('.env');
        $contents = file_get_contents($envPath);

        return str_contains($contents, 'JWT_PRIVATE_KEY=') && str_contains($contents, 'JWT_PUBLIC_KEY=');
    }

    private function generateRsaKeyResource(): mixed
    {
        return openssl_pkey_new([
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
    }

    private function writeAesKeysToEnv(): void
    {
        $aesKey = base64_encode(openssl_random_pseudo_bytes(32));
        $aesIv  = strtoupper(bin2hex(openssl_random_pseudo_bytes(16)));

        $this->writeEnvValue('AES_KEY', $aesKey);
        $this->writeEnvValue('AES_IV',  $aesIv);

        $this->info('AES_KEY and AES_IV generated and written to .env.');
    }

    private function writeEnvValue(string $key, string $value): void
    {
        $envPath  = base_path('.env');
        $contents = file_get_contents($envPath);
        $pattern  = '/^' . preg_quote($key, '/') . '=.*/m';

        $updated = preg_match($pattern, $contents)
            ? preg_replace($pattern, "{$key}={$value}", $contents)
            : $contents . PHP_EOL . "{$key}={$value}";

        file_put_contents($envPath, $updated);
    }
}
