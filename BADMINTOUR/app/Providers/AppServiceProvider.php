<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\MailManager;
use Resend\Client;
use Resend\Transporters\HttpTransporter;
use Resend\ValueObjects\ApiKey;
use Resend\ValueObjects\Transporter\BaseUri;
use Resend\ValueObjects\Transporter\Headers;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure Resend HTTP client to handle SSL certificates properly
        // This fixes cURL error 60 on Windows/local development environments
        // Register the extension when Resend is configured (regardless of whether it's the default)
        if (config('services.resend.key')) {
            $this->app->resolving(MailManager::class, function ($mailManager) {
                $mailManager->extend('resend', function (array $config) {
                    $apiKey = $config['key'] ?? config('services.resend.key');
                    
                    if (!$apiKey) {
                        throw new \RuntimeException('Resend API key is not configured. Please set RESEND_KEY in your .env file.');
                    }

                    // Create a custom HTTP client with SSL verification configured
                    $httpClient = new \GuzzleHttp\Client([
                        'verify' => $this->getSslVerifyPath(),
                        'timeout' => 30,
                        'connect_timeout' => 10,
                    ]);

                    // Create Resend transporter components
                    $apiKeyObj = ApiKey::from($apiKey);
                    $baseUri = BaseUri::from(getenv('RESEND_BASE_URL') ?: 'api.resend.com');
                    $headers = Headers::withAuthorization($apiKeyObj);

                    // Create custom transporter with configured HTTP client
                    $transporter = new HttpTransporter($httpClient, $baseUri, $headers);
                    
                    // Create Resend client with custom transporter
                    $resendClient = new Client($transporter);
                    
                    // Return ResendTransport with custom client
                    return new \Illuminate\Mail\Transport\ResendTransport($resendClient);
                });
            });
        }
    }

    /**
     * Get SSL certificate verification path.
     * Returns false for local development on Windows if SSL verification fails,
     * or the path to CA bundle for proper SSL verification.
     */
    private function getSslVerifyPath()
    {
        // Check if SSL verification is explicitly disabled in .env (for local dev only)
        if (env('MAIL_SSL_VERIFY') === 'false') {
            \Log::warning('SSL verification is disabled for email. This should only be used in local development.');
            return false;
        }

        // Common CA bundle paths to check
        $caBundlePaths = [
            // Windows (common locations)
            'C:/php/extras/ssl/cacert.pem',
            'C:/xampp/apache/bin/curl-ca-bundle.crt',
            'C:/xampp/php/extras/ssl/cacert.pem',
            'C:/wamp64/bin/php/php*/extras/ssl/cacert.pem',
            // Linux
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/usr/share/ssl/certs/ca-bundle.crt',
            // macOS
            '/usr/local/etc/openssl/cert.pem',
            '/opt/homebrew/etc/openssl/cert.pem',
            '/etc/ssl/cert.pem',
        ];

        // Try to find an existing CA bundle
        foreach ($caBundlePaths as $path) {
            // Handle wildcards in Windows paths
            if (strpos($path, '*') !== false) {
                $globPaths = glob($path);
                if (!empty($globPaths) && file_exists($globPaths[0])) {
                    return $globPaths[0];
                }
            } elseif (file_exists($path)) {
                return $path;
            }
        }

        // For local development, disable SSL verification if no CA bundle found
        // This prevents cURL error 60 on Windows systems without proper CA bundle setup
        if (config('app.env') === 'local' || config('app.env') === 'testing') {
            \Log::info('No CA bundle found. SSL verification disabled for local development.');
            return false;
        }

        // For production, use system default (true) - this will fail if CA bundle is missing
        // which is the desired behavior to ensure security
        return true;
    }
}
