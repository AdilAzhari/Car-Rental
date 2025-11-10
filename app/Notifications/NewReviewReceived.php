<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReviewReceived extends Notification
{
    use Queueable;

    public function __construct(
        public Review $review
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $reviewerName = $this->review->renter->name ?? $this->review->reviewer->name ?? 'Unknown User';

        return [
            'title' => 'New Review Received',
            'body' => "{$reviewerName} left a {$this->review->rating}-star review for {$this->review->vehicle->make} {$this->review->vehicle->model}",
            'icon' => 'heroicon-o-star',
            'icon_color' => $this->review->rating >= 4 ? 'success' : ($this->review->rating >= 3 ? 'warning' : 'danger'),
            'review_id' => $this->review->id,
            'vehicle_id' => $this->review->vehicle_id,
            'rating' => $this->review->rating,
            'actions' => [
                [
                    'label' => 'View Review',
                    'url' => route('filament.admin.resources.reviews.edit', ['record' => $this->review->id]),
                ],
                [
                    'label' => 'View Vehicle',
                    'url' => route('filament.admin.resources.vehicles.view', ['record' => $this->review->vehicle_id]),
                ],
            ],
        ];
    }
}
