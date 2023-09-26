<?php

namespace TcgWebdev\PusherBeams\Test;

use Illuminate\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notifiable;
use TcgWebdev\PusherBeams\PusherBeams;
use Illuminate\Notifications\Notification;
use TcgWebdev\PusherBeams\PusherBeamsMessage;
use Mockery;
use Pusher\PushNotifications\PushNotifications;
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    public function setUp()
    {
        $this->beams = Mockery::mock(PushNotifications::class);
        $this->events = Mockery::mock(Dispatcher::class);
        $this->channel = new PusherBeams($this->beams, $this->events);
        $this->notification = new TestNotification;
        $this->notifiable = new TestNotifiable;
    }

    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_send_a_notification()
    {
        $message = $this->notification->toPusherBeamsNotification($this->notifiable);
        $data = $message->toArray();
        $this->beams->shouldReceive('publishToInterests')->with(['interest_name'], $data)->andReturn(['publishId' => '12345']);
        $this->channel->send($this->notifiable, $this->notification);
    }

    /** @test */
    public function it_fires_failure_event_on_failure()
    {
        $message = $this->notification->toPusherBeamsNotification($this->notifiable);
        $data = $message->toArray();
        $this->beams->shouldReceive('publishToInterests')->with(['interest_name'], $data)->andReturn([]);
        $this->events->shouldReceive('fire')->with(Mockery::type(NotificationFailed::class));
        $this->channel->send($this->notifiable, $this->notification);
    }
}

class TestNotifiable
{
    use Notifiable;

    public function routeNotificationForPusherBeams()
    {
        return 'interest_name';
    }
}

class TestNotification extends Notification
{
    public function toPusherBeamsNotification($notifiable)
    {
        return new PusherBeamsMessage();
    }
}
