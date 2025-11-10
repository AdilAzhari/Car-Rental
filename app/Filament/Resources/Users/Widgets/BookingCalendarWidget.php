<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\Schemas\BookingInfolist;
use App\Models\Booking;
use Filament\Actions\ViewAction;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\Actions\EditAction;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BookingCalendarWidget extends CalendarWidget
{
    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    protected bool $dateClickEnabled = true;

    protected bool $eventClickEnabled = true;

    public function getHeading(): string
    {
        return __('Booking Calendar - Complete Overview');
    }

    public function getDescription(): ?string
    {
        return __('View all bookings across time with detailed information. Click dates to create new bookings, click events to view details.');
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        return Booking::query()
            ->with(['renter', 'vehicle', 'payment', 'payments'])
            ->whereDate('end_date', '>=', $info->start)
            ->whereDate('start_date', '<=', $info->end)
            ->orderBy('start_date');
    }

    protected function getDateClickContextMenuActions(): array
    {
        return [
            $this->createBookingAction(),
        ];
    }

    protected function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewBookingAction(),
            $this->editBookingAction(),
            $this->viewPaymentAction(),
        ];
    }

    public function createBookingAction(): CreateAction
    {
        return $this->createAction(Booking::class)
            ->modalHeading(__('Create New Booking'))
            ->modalDescription(__('Create a new booking for the selected date'))
            ->modalSubmitActionLabel(__('Create Booking'));
    }

    public function viewBookingAction(): ViewAction
    {
        return ViewAction::make('view_booking')
            ->label(__('View Booking Details'))
            ->icon('heroicon-m-eye')
            ->color('info')
            ->visible(fn ($record): bool => $record !== null)
            ->modalHeading(fn ($record): string => __('Booking Details').' #'.$record?->id)
            ->infolist(fn (): array => BookingInfolist::configure(new \Filament\Schemas\Schema)->getComponents());
    }

    public function editBookingAction(): EditAction
    {
        return EditAction::make('edit_booking')
            ->label(__('Edit Booking'))
            ->icon('heroicon-m-pencil-square')
            ->color('warning')
            ->visible(fn ($record): bool => $record !== null)
            ->modalHeading(fn ($record): string => __('Edit Booking').' #'.$record?->id);
    }

    public function viewPaymentAction(): ViewAction
    {
        return ViewAction::make('view_payment')
            ->label(__('View Payment'))
            ->icon('heroicon-m-currency-dollar')
            ->color('success')
            ->visible(fn ($record): bool => $record !== null && $record->payment !== null)
            ->modalHeading(fn ($record): string => __('Payment Details').' #'.$record->payment?->id)
            ->infolist(function ($record) {
                if (! $record || ! $record->payment) {
                    return null;
                }

                $paymentInfolistClass = \App\Filament\Resources\Payments\Schemas\PaymentInfolist::class;

                return $paymentInfolistClass::configure(new \Filament\Schemas\Schema)->getComponents();
            });
    }

    protected function getCalendarEvents(): array
    {
        $events = parent::getCalendarEvents();

        // Add additional statistics to the events
        foreach ($events as &$event) {
            if (isset($event['extendedProps']['model'])) {
                $booking = $event['extendedProps']['model'];

                // Add payment status indicator
                if ($booking->payment) {
                    $event['title'] .= ' 💳';
                }

                // Add days remaining indicator for ongoing bookings
                if ($booking->status->value === 'ongoing') {
                    $daysLeft = now()->diffInDays($booking->end_date);
                    $event['title'] .= " ({$daysLeft}d left)";
                }
            }
        }

        return $events;
    }
}
