<?php

namespace App\Console\Commands;

use App\Services\SmsService;
use Exception;
use Illuminate\Console\Command;

class TestTwilioSms extends Command
{
    protected $signature = 'twilio:test-sms {phone} {--message=Test message from CarRent System}';

    protected $description = 'Test Twilio SMS integration by sending a test message';

    public function handle(SmsService $smsService): int
    {
        $phone = $this->argument('phone');
        $message = $this->option('message');

        $this->info('Testing Twilio SMS integration...');
        $this->line("Phone: {$phone}");
        $this->line("Message: {$message}");

        try {
            // Validate phone number first
            $this->info('Validating phone number...');
            $validation = $smsService->validatePhoneNumber($phone);

            if (! $validation['success'] || ! $validation['valid']) {
                $this->error('Phone number validation failed: '.($validation['message'] ?? 'Invalid number'));

                return self::FAILURE;
            }

            $this->info("✓ Phone number is valid: {$validation['phone_number']} ({$validation['country_code']})");

            // Send SMS
            $this->info('Sending SMS...');
            $result = $smsService->sendSms($phone, $message);

            if ($result['success']) {
                $this->info('✓ SMS sent successfully!');
                $this->line("Message SID: {$result['response']['sid']}");
                $this->line("Status: {$result['response']['status']}");
                $this->line("To: {$result['response']['to']}");
                $this->line("From: {$result['response']['from']}");

                // Check status after a moment
                if ($this->confirm('Check message status?', true)) {
                    sleep(2);
                    $status = $smsService->getMessageStatus($result['response']['sid']);
                    if ($status['success']) {
                        $this->line("Current Status: {$status['status']}");
                        if ($status['error_code']) {
                            $this->warn("Error Code: {$status['error_code']}");
                            $this->warn("Error Message: {$status['error_message']}");
                        }
                    }
                }

                return self::SUCCESS;
            } else {
                $this->error('✗ SMS sending failed: '.$result['message']);

                return self::FAILURE;
            }

        } catch (Exception $e) {
            $this->error('Exception occurred: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
