<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Messaging\DTOs\OutgoingMessage;
use App\Messaging\MessengerAdapterInterface;
use App\Models\ShortUrl;
use App\Services\EventService;
use App\Services\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SendEventAttendancePrompts implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     * Check for events that need attendance prompts and send them
     */
    public function handle(EventService $eventService, MessageService $messageService, MessengerAdapterInterface $adapter): void
    {
        $events = $eventService->getEventsPendingAttendancePrompt();
        
        foreach ($events as $event) {
            // Generate signed URL
            $signedUrl = URL::temporarySignedRoute(
                'events.attendance',
                now()->addDays(7),
                ['event' => $event->id]
            );
            
            // Create short URL
            $shortCode = ShortUrl::generateUniqueCode(6);
            ShortUrl::create([
                'code' => $shortCode,
                'target_url' => $signedUrl,
                'expires_at' => now()->addDays(7),
            ]);
            
            $message = "📊 *Attendance Recording*\n\n";
            $message .= "Event: {$event->name}\n";
            $message .= "Please record who attended:\n\n";
            $message .= url('/l/' . $shortCode);
            
            $adapter->sendMessage(new OutgoingMessage(
                chatId: $event->group->platform_chat_id,
                text: $message,
                parseMode: 'Markdown'
            ));
            
            // Mark as prompted to avoid spam
            $event->update(['attendance_prompt_sent_at' => now()]);
        }
    }
}
