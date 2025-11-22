<?php

namespace App\Console\Commands;

use App\Services\MacrokioskErrorMapper;
use App\Services\MacrokioskJwtService;
use App\Services\MacrokioskSmsService;
use Illuminate\Console\Command;

class TestMacrokioskSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test
                            {action : The action to perform: config|jwt|send|jpj}
                            {--to= : Recipient phone number (required for send/jpj)}
                            {--message= : Message content (required for send)}
                            {--plate= : Vehicle plate number (required for jpj)}
                            {--sender= : Sender ID (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MACROKIOSK SMS integration (config, JWT auth, send SMS, check JPJ violations)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        $this->info("🧪 MACROKIOSK SMS Integration Test\n");

        return match ($action) {
            'config' => $this->testConfig(),
            'jwt' => $this->testJwt(),
            'send' => $this->testSend(),
            'jpj' => $this->testJpj(),
            default => $this->error("❌ Invalid action. Use: config, jwt, send, or jpj"),
        };
    }

    /**
     * Test configuration
     */
    private function testConfig(): int
    {
        $this->info("📋 Testing Configuration...\n");

        $config = config('sms.macrokiosk');

        $checks = [
            'Username' => $config['username'] ?? null,
            'Password' => $config['password'] ? '***' : null,
            'Service ID' => $config['service_id'] ?? null,
            'API Key' => $config['api_key'] ? '***' : null,
            'Base URL' => $config['base_url'] ?? null,
            'Use JWT' => $config['use_jwt'] ? 'Yes' : 'No',
            'Default Sender' => $config['default_sender'] ?? null,
        ];

        $allValid = true;

        foreach ($checks as $key => $value) {
            if ($value === null) {
                $this->error("❌ {$key}: Not configured");
                $allValid = false;
            } else {
                $this->line("✅ {$key}: {$value}");
            }
        }

        $this->newLine();

        if ($allValid) {
            $this->info("✅ Configuration is complete!");
            return Command::SUCCESS;
        }

        $this->error("❌ Configuration is incomplete. Check your .env file.");
        return Command::FAILURE;
    }

    /**
     * Test JWT authentication
     */
    private function testJwt(): int
    {
        $this->info("🔐 Testing JWT Authentication...\n");

        if (!config('sms.macrokiosk.use_jwt')) {
            $this->warn("⚠️  JWT authentication is disabled. Enable it in config/sms.php");
            return Command::SUCCESS;
        }

        try {
            $jwtService = new MacrokioskJwtService();

            $this->info("Requesting JWT token...");

            $token = $jwtService->generateToken();

            if ($token) {
                $this->info("✅ JWT token obtained successfully!");
                $this->line("Token (first 50 chars): " . substr($token, 0, 50) . "...");
                $this->line("Token cached: " . ($jwtService->hasValidToken() ? 'Yes' : 'No'));
                return Command::SUCCESS;
            }

            $this->error("❌ Failed to obtain JWT token");
            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->error("❌ JWT test failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Test sending SMS
     */
    private function testSend(): int
    {
        $to = $this->option('to');
        $message = $this->option('message');
        $sender = $this->option('sender');

        if (!$to || !$message) {
            $this->error("❌ Missing required options: --to and --message");
            $this->line("Example: php artisan sms:test send --to=60123456789 --message=\"Test message\"");
            return Command::FAILURE;
        }

        $this->info("📤 Testing SMS Send...\n");
        $this->line("To: {$to}");
        $this->line("Message: {$message}");
        $this->line("Sender: " . ($sender ?? config('sms.macrokiosk.default_sender')));
        $this->newLine();

        try {
            $smsService = new MacrokioskSmsService();

            $this->info("Sending SMS...");

            $result = $smsService->send($to, $message, $sender);

            $this->newLine();

            if ($result['success']) {
                $this->info("✅ SMS sent successfully!");
                $this->line("Response: " . ($result['message'] ?? 'No message'));
            } else {
                $this->error("❌ SMS send failed!");
                $this->line("Error Code: " . ($result['error_code'] ?? 'Unknown'));
                $this->line("Error Message: " . MacrokioskErrorMapper::getMessage($result['error_code'] ?? 0));
            }

            return $result['success'] ? Command::SUCCESS : Command::FAILURE;

        } catch (\Exception $e) {
            $this->error("❌ SMS test failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Test JPJ traffic violation check
     */
    private function testJpj(): int
    {
        $to = $this->option('to') ?? '15888'; // Default JPJ number
        $plate = $this->option('plate');

        if (!$plate) {
            $this->error("❌ Missing required option: --plate");
            $this->line("Example: php artisan sms:test jpj --plate=ABC1234");
            return Command::FAILURE;
        }

        $this->info("🚗 Testing JPJ Traffic Violation Check...\n");
        $this->line("Plate Number: {$plate}");
        $this->line("JPJ Number: {$to}");
        $this->newLine();

        try {
            $smsService = new MacrokioskSmsService();

            $this->info("Sending JPJ query SMS...");

            $result = $smsService->checkTrafficViolations($plate, $to);

            $this->newLine();

            if ($result['success']) {
                $this->info("✅ JPJ query sent successfully!");
                $this->line("Message: JPJ {$plate}");
                $this->line("Response: " . ($result['message'] ?? 'No message'));
                $this->newLine();
                $this->info("⏳ Waiting for JPJ reply at your webhook: " . config('sms.macrokiosk.mo_webhook_url'));
            } else {
                $this->error("❌ JPJ query failed!");
                $this->line("Error Code: " . ($result['error_code'] ?? 'Unknown'));
                $this->line("Error Message: " . MacrokioskErrorMapper::getMessage($result['error_code'] ?? 0));
            }

            return $result['success'] ? Command::SUCCESS : Command::FAILURE;

        } catch (\Exception $e) {
            $this->error("❌ JPJ test failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
