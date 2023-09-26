<?php

namespace TcgWebdev\PusherBeams;

use Illuminate\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use Pusher\PushNotifications\PushNotifications;

class PusherBeams
{
    /**
     * @var \Pusher\PushNotifications\PushNotifications
     */
    protected $beams;

    /**
     * @param \Pusher\PushNotifications\PushNotifications $beams
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function __construct(PushNotifications $beams, Dispatcher $events)
    {
        $this->beams = $beams;
        $this->events = $events;
    }

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'pushNotificationInterest')) {
            $interest = $notification->pushNotificationInterest();
        } else {
            $interest = $notifiable->routeNotificationFor('PusherBeams')
                ?: $this->interestName($notifiable);
        }

        if (is_string($interest)) {
            $interest = [$interest];
        }

        try {
            if (method_exists($this->beams, 'publishToInterests')) {
                $response = $this->beams->publishToInterests(
                    $interest,
                    $notification->toPusherBeamsNotification($notifiable)->toArray()
                );
            } else {
                $response = $this->beams->publish(
                    $interest,
                    $notification->toPusherBeamsNotification($notifiable)->toArray()
                );
            }
        } catch (\Exception $e) {
            if (app()::VERSION < '5.8') {
                $this->events->fire(
                    new NotificationFailed($notifiable, $notification, 'pusher-beams')
                );
            } else {
                $this->events->dispatch(
                    new NotificationFailed($notifiable, $notification, 'pusher-beams')
                );
            }
        }
    }

    /**
     * Get the interest name for the notifiable.
     *
     * @param  string $notifiable
     * @return string
     */
    protected function interestName($notifiable)
    {
        $class = str_replace('\\', '.', get_class($notifiable));

        return $class . '.' . $notifiable->getKey();
    }
}
