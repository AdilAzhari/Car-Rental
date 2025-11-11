<?php

namespace App\Filament\Pages\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Override the throwFailureValidationException method to check user role
     *
     * @throws ValidationException
     */
    protected function throwFailureValidationException(): never
    {
        // Check if user exists and get their role
        $user = User::query()->where('email', $this->data['email'])->first();

        if ($user && $user->role === UserRole::RENTER) {
            // Renter trying to access admin panel
            Notification::make()
                ->danger()
                ->title('Access Denied')
                ->body('You do not have permission to access the admin panel. Renters can only access the customer portal.')
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'data.email' => 'You do not have permission to access the admin panel.',
            ]);
        }

        // Default authentication failure message
        parent::throwFailureValidationException();
    }

    /**
     * Get the email form component with hint
     */
    #[\Override]
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1])
            ->hint('Only admins and vehicle owners can log in here')
            ->hintColor('warning');
    }
}
