<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateTestKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:generate-test-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RS256 test keys and add them to .gitignore';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keysDir = base_path('tests/keys');

        if (!File::exists($keysDir)) {
            File::makeDirectory($keysDir, 0755, true);
        }

        $privateKeyPath = $keysDir . '/test-private.pem';
        $publicKeyPath = $keysDir . '/test-public.pem';

        if (File::exists($privateKeyPath) && File::exists($publicKeyPath)) {
            $this->info("Test keys already exist at {$keysDir}");
            return;
        }

        // Generate private key
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($privateKey, $privateKeyOut);
        File::put($privateKeyPath, $privateKeyOut);
        chmod($privateKeyPath, 0600);

        // Generate public key
        $keyDetails = openssl_pkey_get_details($privateKey);
        $publicKeyOut = $keyDetails['key'];
        File::put($publicKeyPath, $publicKeyOut);

        // Add to gitignore if not already present
        $gitignorePath = base_path('.gitignore');
        $gitignoreContent = File::exists($gitignorePath) ? File::get($gitignorePath) : '';
        if (!str_contains($gitignoreContent, '/tests/keys')) {
            File::append($gitignorePath, "\n/tests/keys\n");
        }

        $this->info('Test keys generated successfully.');
        $this->line("Private Key: {$privateKeyPath}");
        $this->line("Public Key:  {$publicKeyPath}");
    }
}
