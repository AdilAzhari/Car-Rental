# MACROKIOSK SMS Integration Documentation

## Overview

This document explains how the MACROKIOSK Bulk SMS API is integrated with the Car Rental system to check traffic violations from JPJ (Jabatan Pengangkutan Jalan Malaysia - Road Transport Department of Malaysia).

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Configuration](#configuration)
3. [Components](#components)
4. [API Endpoints](#api-endpoints)
5. [Usage Examples](#usage-examples)
6. [Testing](#testing)
7. [Troubleshooting](#troubleshooting)

---

## System Architecture

### Flow Diagram

```
┌─────────────────┐
│  Car Rental     │
│  Application    │
└────────┬────────┘
         │
         │ 1. Send JPJ query
         ▼
┌─────────────────┐
│  MACROKIOSK     │
│  SMS Gateway    │
└────────┬────────┘
         │
         │ 2. Forward to JPJ
         ▼
┌─────────────────┐
│  JPJ Service    │
│  (15888)        │
└────────┬────────┘
         │
         │ 3. Reply with violations
         ▼
┌─────────────────┐
│  MACROKIOSK     │
│  SMS Gateway    │
└────────┬────────┘
         │
         │ 4. Webhook callback (MO)
         ▼
┌─────────────────┐
│  Car Rental     │
│  Webhook        │
│  /api/webhooks/ │
│  sms/receive    │
└────────┬────────┘
         │
         │ 5. Parse & Store
         ▼
┌─────────────────┐
│  Database       │
│  sms_messages   │
│  vehicles       │
└─────────────────┘
```

### Components Overview

1. **Configuration** (`config/sms.php`) - Centralized SMS API settings
2. **JWT Service** (`MacrokioskJwtService`) - Handles JWT authentication
3. **SMS Service** (`MacrokioskSmsService`) - Sends SMS messages
4. **Webhook Controller** (`SmsWebhookController`) - Receives incoming SMS
5. **Error Mapper** (`MacrokioskErrorMapper`) - Maps API error codes
6. **Test Command** (`sms:test`) - Testing utility

---

## Configuration

### 1. Environment Variables

Copy the SMS configuration from `.env.example` to your `.env` file:

```env
# MACROKIOSK SMS Configuration
SMS_PROVIDER=macrokiosk
SMS_USERNAME=your_macrokiosk_username
SMS_PASSWORD=your_macrokiosk_password
SMS_SERVICE_ID=your_service_id
SMS_API_KEY=your_api_key_for_jwt
SMS_USE_JWT=false
SMS_BASE_URL=https://www.etracker.cc/bulksms
SMS_DEFAULT_SENDER=CarRental
SMS_LOG_REQUESTS=true
SMS_LOG_RESPONSES=true

# SMS Queue Settings
SMS_QUEUE_ENABLED=true
SMS_QUEUE_CONNECTION=database
SMS_QUEUE_NAME=sms
```

### 2. Get MACROKIOSK Credentials

Contact MACROKIOSK to obtain:
- Username
- Password
- Service ID
- API Key (for JWT authentication)

**MACROKIOSK Support:**
- Phone: +603 2163 2100 (24 hours)
- Email: techsupport@macrokiosk.com
- Website: https://www.macrokiosk.com

### 3. Database Migration

Run the migration to create the `sms_messages` table:

```bash
php artisan migrate
```

This creates the table structure:
- `vehicle_id` - Links to vehicles table
- `plate_number` - Vehicle plate number
- `message_sid` - Unique message identifier
- `from_number` / `to_number` - Phone numbers
- `direction` - inbound/outbound
- `message_body` - SMS content
- `message_type` - jpj_response, jpj_query, general
- `status` - received, processed, failed, sent, delivered, pending
- `parsed_data` - JSON with extracted violation data
- `received_at` / `processed_at` - Timestamps

---

## Components

### 1. MacrokioskJwtService

**Location:** `app/Services/MacrokioskJwtService.php`

Handles JWT token authentication for secure API access.

**Key Methods:**
- `getToken()` - Get cached token or generate new one
- `generateToken()` - Request new JWT token from API
- `invalidateToken()` - Clear cached token
- `hasValidToken()` - Check if valid token exists

**Token Caching:**
- Tokens are cached for 50 minutes (expire at 60 minutes)
- Auto-refresh before expiration
- Stored in Laravel cache

**Example:**
```php
$jwtService = new MacrokioskJwtService();
$token = $jwtService->getToken();
```

---

### 2. MacrokioskSmsService

**Location:** `app/Services/MacrokioskSmsService.php`

Main service for sending SMS messages through MACROKIOSK API.

**Key Methods:**

#### `send(string|array $to, string $message, ?string $sender = null): array`

Send SMS to one or multiple recipients.

**Parameters:**
- `$to` - Phone number(s) (e.g., "60123456789" or array)
- `$message` - Message content
- `$sender` - Sender ID (optional, uses default if not provided)

**Returns:**
```php
[
    'success' => true|false,
    'error_code' => 200,
    'message' => 'Success - Message accepted',
    'raw_response' => '...'
]
```

**Example:**
```php
$smsService = new MacrokioskSmsService();

// Single recipient
$result = $smsService->send('60123456789', 'Hello World');

// Multiple recipients
$result = $smsService->send(
    ['60123456789', '60198765432'],
    'Bulk message'
);
```

#### `checkTrafficViolations(string $plateNumber, string $toNumber = '15888'): array`

Send JPJ traffic violation query.

**Parameters:**
- `$plateNumber` - Vehicle plate number (e.g., "ABC1234")
- `$toNumber` - JPJ SMS number (default: "15888")

**Example:**
```php
$smsService = new MacrokioskSmsService();
$result = $smsService->checkTrafficViolations('ABC1234');
// Sends SMS: "JPJ ABC1234" to 15888
```

**Features:**
- Auto-detects message type (ASCII or Unicode)
- Validates message length (ASCII: 1071, Unicode: 1000)
- Normalizes phone numbers (adds country code)
- Retry logic (3 attempts with 2-second delay)
- Logs all messages to database
- JWT or basic authentication

---

### 3. SmsWebhookController

**Location:** `app/Http/Controllers/Api/SmsWebhookController.php`

Handles incoming SMS webhooks and API requests.

#### Webhook Endpoints

##### POST `/api/webhooks/sms/receive`

Receives incoming SMS (MO - Mobile Originated) from MACROKIOSK.

**Expected Payload:**
```json
{
  "message_id": "unique-message-id",
  "from": "+60123456789",
  "to": "+60987654321",
  "body": "SMS message content",
  "received_at": "2024-01-01 12:00:00"
}
```

**Flexible Field Names:**
- Message ID: `message_id`, `id`, `sid`
- From: `from`, `sender`, `from_number`
- To: `to`, `recipient`, `to_number`
- Body: `body`, `message`, `text`, `content`
- Timestamp: `received_at`, `timestamp`

**Response:**
```json
{
  "success": true,
  "message": "SMS received and stored successfully",
  "sms_id": 123,
  "plate_number": "ABC1234",
  "vehicle_found": true
}
```

**Processing:**
1. Extracts message data (flexible field names)
2. Parses SMS content to extract plate number
3. Detects message type (JPJ response or general)
4. Finds vehicle by plate number
5. Stores message in database
6. If JPJ response, updates vehicle with violation data

##### POST `/api/webhooks/sms/delivery`

Receives delivery notifications (DN) from MACROKIOSK.

**Expected Payload:**
```json
{
  "msgID": "unique-message-id",
  "msisdn": "60123456789",
  "status": "DELIVRD",
  "statusDetail": "Message delivered to handset"
}
```

**Delivery Status Mapping:**
- `DELIVRD` → delivered
- `ACCEPTD` → sent
- `EXPIRED` → failed
- `DELETED` → failed
- `UNDELIV` → failed
- `REJECTD` → failed
- `UNKNOWN` → pending

#### API Endpoints (Authenticated)

##### GET `/api/vehicles/{vehicleId}/sms`

Get all SMS messages for a specific vehicle.

**Authorization:** Owner or Admin

**Response:**
```json
{
  "success": true,
  "vehicle": {
    "id": 1,
    "plate_number": "ABC1234",
    "make": "Toyota",
    "model": "Camry"
  },
  "messages": [...],
  "total_count": 5
}
```

##### GET `/api/sms/plate/{plateNumber}`

Get SMS messages by plate number.

**Example:** `/api/sms/plate/ABC1234`

##### GET `/api/sms/jpj-responses`

Get all JPJ responses for authenticated user's vehicles.

**Authorization:** Owner (own vehicles) or Admin (all vehicles)

---

### 4. MacrokioskErrorMapper

**Location:** `app/Services/MacrokioskErrorMapper.php`

Maps MACROKIOSK API error codes (200-435) to meaningful messages.

**Methods:**

#### `getMessage(int|string $code): string`

Get error message for code.

**Example:**
```php
MacrokioskErrorMapper::getMessage(200);
// Returns: "Success - Message accepted for delivery"

MacrokioskErrorMapper::getMessage(405);
// Returns: "Insufficient credit balance"
```

#### `isSuccess(int|string $code): bool`

Check if code indicates success (200).

#### `isRetryable(int|string $code): bool`

Check if error is retryable (system errors, timeouts, etc.).

**Retryable Codes:**
- 420: System error
- 422: Queue full
- 423: Gateway timeout
- 424: Gateway unavailable
- 425: Network error
- 426: Service temporarily unavailable
- 428: Request timeout
- 429: Too many requests

#### `getCategory(int|string $code): string`

Get error category: `success`, `authentication`, `message`, `system`, `api`, `unknown`

#### `formatError(int|string $code, ?string $additionalInfo = null): array`

Format detailed error response.

**Returns:**
```php
[
    'error_code' => 405,
    'error_message' => 'Insufficient credit balance',
    'error_category' => 'authentication',
    'is_retryable' => false,
    'additional_info' => null
]
```

---

## API Endpoints

### Public Webhooks (No Authentication)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/webhooks/sms/receive` | Receive incoming SMS (MO) |
| POST | `/api/webhooks/sms/delivery` | Receive delivery notifications (DN) |

### Authenticated Endpoints

| Method | Endpoint | Description | Rate Limit |
|--------|----------|-------------|------------|
| GET | `/api/vehicles/{vehicleId}/sms` | Get SMS for vehicle | 100/min |
| GET | `/api/sms/plate/{plateNumber}` | Get SMS by plate | 100/min |
| GET | `/api/sms/jpj-responses` | Get all JPJ responses | 100/min |

---

## Usage Examples

### 1. Check Traffic Violations Programmatically

```php
use App\Services\MacrokioskSmsService;

$smsService = new MacrokioskSmsService();

// Check violations for plate ABC1234
$result = $smsService->checkTrafficViolations('ABC1234');

if ($result['success']) {
    echo "JPJ query sent successfully!\n";
    echo "Waiting for reply at webhook...\n";
} else {
    echo "Error: " . $result['error_code'] . "\n";
    echo "Message: " . $result['message'] . "\n";
}
```

### 2. Send Custom SMS

```php
use App\Services\MacrokioskSmsService;

$smsService = new MacrokioskSmsService();

$result = $smsService->send(
    '60123456789',
    'Your booking has been confirmed!',
    'CarRental'
);
```

### 3. Retrieve Vehicle SMS History

```php
use App\Models\SmsMessage;

// Get all SMS for a vehicle
$messages = SmsMessage::where('vehicle_id', 1)
    ->orderBy('received_at', 'desc')
    ->get();

// Get only JPJ responses
$jpjResponses = SmsMessage::where('vehicle_id', 1)
    ->jpjResponses()
    ->get();

// Get by plate number
$messages = SmsMessage::forPlateNumber('ABC1234')->get();
```

### 4. Parse Violation Data

When JPJ replies with violation data, the system automatically:

1. Extracts plate number using regex
2. Detects violation keywords
3. Parses fine amounts (RM pattern)
4. Links to vehicle record
5. Updates vehicle with violation data

**Example parsed data:**
```json
{
  "plate_number": "ABC1234",
  "has_violations": true,
  "has_pending_violations": true,
  "total_fines_amount": 300.00,
  "violations": [
    {
      "type": "Speeding",
      "date": "2024-01-15",
      "location": "As per SMS",
      "fine_amount": 150.00,
      "status": "pending",
      "reference": "REF000001",
      "due_date": "2024-02-15",
      "description": "Full SMS text"
    }
  ]
}
```

---

## Testing

### Test Command

A comprehensive test command is provided: `php artisan sms:test`

#### 1. Test Configuration

```bash
php artisan sms:test config
```

Checks all required environment variables:
- Username
- Password
- Service ID
- API Key
- Base URL
- Default Sender

**Output:**
```
✅ Username: your_username
✅ Password: ***
✅ Service ID: 12345
✅ API Key: ***
✅ Base URL: https://www.etracker.cc/bulksms
✅ Use JWT: No
✅ Default Sender: CarRental

✅ Configuration is complete!
```

#### 2. Test JWT Authentication

```bash
php artisan sms:test jwt
```

Tests JWT token generation:
- Requests token from API
- Checks token caching
- Validates response

**Output:**
```
🔐 Testing JWT Authentication...

Requesting JWT token...
✅ JWT token obtained successfully!
Token (first 50 chars): eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
Token cached: Yes
```

#### 3. Test Sending SMS

```bash
php artisan sms:test send --to=60123456789 --message="Test message"
```

Optional sender ID:
```bash
php artisan sms:test send --to=60123456789 --message="Test" --sender=MyApp
```

**Output:**
```
📤 Testing SMS Send...

To: 60123456789
Message: Test message
Sender: CarRental

Sending SMS...

✅ SMS sent successfully!
Response: Success - Message accepted for delivery
```

#### 4. Test JPJ Traffic Violation Check

```bash
php artisan sms:test jpj --plate=ABC1234
```

Custom JPJ number:
```bash
php artisan sms:test jpj --plate=ABC1234 --to=15888
```

**Output:**
```
🚗 Testing JPJ Traffic Violation Check...

Plate Number: ABC1234
JPJ Number: 15888

Sending JPJ query SMS...

✅ JPJ query sent successfully!
Message: JPJ ABC1234
Response: Success - Message accepted for delivery

⏳ Waiting for JPJ reply at your webhook: http://localhost/api/webhooks/sms/receive
```

---

## Troubleshooting

### Common Issues

#### 1. Configuration Not Found

**Error:** "Configuration is incomplete"

**Solution:**
- Copy `.env.example` to `.env`
- Fill in MACROKIOSK credentials
- Run `php artisan config:cache`

#### 2. JWT Token Failed

**Error:** "Failed to obtain JWT token"

**Possible Causes:**
- Invalid credentials
- API key incorrect
- Network connectivity

**Solution:**
```bash
# Test with basic auth first
SMS_USE_JWT=false

# Check logs
tail -f storage/logs/laravel.log
```

#### 3. SMS Not Received

**Error:** "SMS send failed"

**Check:**
1. Credit balance sufficient?
2. Sender ID approved?
3. Phone number format correct?
4. Check error code with mapper

**Example:**
```php
use App\Services\MacrokioskErrorMapper;

$code = 405;
echo MacrokioskErrorMapper::getMessage($code);
// Output: "Insufficient credit balance"
```

#### 4. Webhook Not Working

**Error:** "SMS received but not stored"

**Check:**
1. Webhook URL accessible from internet
2. No authentication blocking
3. Check logs for errors
4. Test with manual POST request

**Test Webhook:**
```bash
curl -X POST http://your-domain/api/webhooks/sms/receive \
  -H "Content-Type: application/json" \
  -d '{
    "message_id": "test123",
    "from": "15888",
    "to": "60123456789",
    "body": "JPJ ABC1234: TIADA SAMAN"
  }'
```

#### 5. Vehicle Not Found

**Issue:** SMS received but vehicle_id is null

**Reason:** Plate number doesn't match any vehicle

**Solution:**
- Check plate number format in database
- SMS parser uses regex: `/\b([A-Z]{1,3}\s?\d{1,4}\s?[A-Z]?)\b/i`
- Ensure vehicle plate_number matches format

#### 6. Rate Limiting

**Error:** "429 - Too many requests"

**Solution:**
- MACROKIOSK limit: 30 TPS (transactions per second)
- Implement queuing for bulk messages
- Add delays between requests

**Queue Example:**
```php
// Future implementation with Laravel queues
dispatch(new SendSmsJob($to, $message))->onQueue('sms');
```

---

## Webhook Configuration at MACROKIOSK

Contact MACROKIOSK support to configure webhooks:

1. **MO (Mobile Originated) Webhook:**
   - URL: `https://your-domain.com/api/webhooks/sms/receive`
   - Method: POST
   - Purpose: Receive incoming SMS replies from JPJ

2. **DN (Delivery Notification) Webhook:**
   - URL: `https://your-domain.com/api/webhooks/sms/delivery`
   - Method: POST
   - Purpose: Receive delivery status updates

**Important:** Your webhook URLs must be:
- Publicly accessible (not localhost)
- HTTPS recommended for security
- No authentication required (MACROKIOSK can't authenticate)

---

## Security Considerations

1. **JWT Authentication**
   - More secure than basic auth
   - Tokens cached and auto-refresh
   - Set `SMS_USE_JWT=true` in production

2. **Webhook Security**
   - Webhooks have no auth (provider limitation)
   - Validate incoming data format
   - Check sender numbers
   - Log all webhook requests

3. **Credentials**
   - Never commit .env file
   - Use strong passwords
   - Rotate API keys periodically
   - Restrict IP addresses at MACROKIOSK portal

4. **Rate Limiting**
   - API endpoints have rate limits
   - Prevent abuse with throttling
   - Monitor usage patterns

---

## Future Enhancements

- [ ] Queue jobs for bulk SMS sending
- [ ] SMS templates system
- [ ] Scheduled violation checks
- [ ] SMS notification to vehicle owners
- [ ] Dashboard for SMS statistics
- [ ] Cost tracking per message
- [ ] Multi-provider support
- [ ] SMS scheduling

---

## Support

### MACROKIOSK Support
- **Phone:** +603 2163 2100 (24 hours)
- **Email:** techsupport@macrokiosk.com
- **Website:** https://www.macrokiosk.com
- **Documentation:** Bulk SMS API Specifications v4.1

### Project Support
- Check logs: `storage/logs/laravel.log`
- Run tests: `php artisan sms:test config`
- Check database: `sms_messages` table

---

## API Reference

### MACROKIOSK Endpoints

**Base URL:** `https://www.etracker.cc/bulksms`

#### Authenticate (JWT)
```
POST /Authenticate
Content-Type: application/x-www-form-urlencoded

username=xxx&password=xxx&token=jwt_token
```

#### Send SMS
```
POST /Send
Content-Type: application/x-www-form-urlencoded
Authorization: Bearer {jwt_token} (if using JWT)

msisdn=60123456789&sid=12345&msg=Hello&gwid=Sender&type=0&fl=0
```

**Parameters:**
- `msisdn` - Recipient number (comma-separated for bulk)
- `sid` - Service ID
- `msg` - Message content (URL-encoded for ASCII, HEX for Unicode)
- `gwid` - Sender ID
- `type` - 0 (ASCII) or 5 (Unicode)
- `fl` - Flash message flag (0 = normal, 1 = flash)
- `username` - Required if not using JWT
- `password` - Required if not using JWT

**Response Codes:**
- `200` - Success
- `400-409` - Authentication errors
- `410-419` - Message errors
- `420-429` - System errors
- `430-435` - API errors

---

## Conclusion

The MACROKIOSK SMS integration provides a complete solution for:
- Checking JPJ traffic violations via SMS
- Receiving and parsing violation data automatically
- Tracking SMS history per vehicle
- Monitoring delivery status
- Testing and debugging with built-in commands

For additional help, refer to the MACROKIOSK API documentation or contact their support team.
