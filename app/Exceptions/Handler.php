<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    #[\Override]
    public function register(): void
    {
        $this->reportable(function (Throwable $throwable): void {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    #[\Override]
    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        // Handle Inertia.js requests
        if ($request->header('X-Inertia')) {
            $statusCode = $response->getStatusCode();

            if (in_array($statusCode, [404, 403, 419, 500, 503])) {
                return Inertia::render('Error', [
                    'status' => $statusCode,
                    'message' => $this->getErrorMessage($statusCode),
                ])->toResponse($request)->setStatusCode($statusCode);
            }
        }

        return $response;
    }

    /**
     * Get error message based on status code.
     */
    private function getErrorMessage(int $statusCode): string
    {
        return match ($statusCode) {
            404 => 'Page Not Found',
            403 => 'Access Forbidden',
            419 => 'Page Expired',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
            default => 'An error occurred',
        };
    }
}
