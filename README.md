# Pusher Beams - push notifications for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tcg-webdev/pusher-beams.svg?style=flat-square)](https://packagist.org/packages/tcg-webdev/pusher-beams)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Quality Score](https://img.shields.io/scrutinizer/g/neoighodaro/pusher-beams.svg?style=flat-square)](https://scrutinizer-ci.com/g/tcg-webdev/pusher-beams)

This package makes it easy to send [Pusher push notifications](https://docs.pusher.com/push-notifications) with Laravel (should work with other non-laravel PHP projects). It's based off [this package](https://github.com/laravel-notification-channels/pusher-push-notifications) by Mohamed Said.

This fork exists to allow us to run both the Pusher Beams and old Pusher Channels code side by side.

## Contents

- [Installation](#installation)
	- [Setting up your Pusher account](#setting-up-your-pusher-account)
	- [Configuration](#configuration)
- [Usage](#usage)
	- [Available Message methods](#available-message-methods)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

You can install the package via composer:

``` bash
composer require tcg-webdev/pusher-beams
```

You must install the service provider:

``` php
// config/app.php
'providers' => [
    ...
    TcgWebdev\PusherBeams\PusherBeamsServiceProvider::class,
],
```


### Setting up your Pusher account

Before using this package you should set up a Pusher account. Here are the steps required.

* Login to https://dash.pusher.com/
* Select *Beams* from the side bar, and click *Create* from the right to create your Instance.
* Go to the settings tab (you can close the wizard)
* Upload your iOS .p8 auth key (they guides you through this), your iOS Team Id and/or your CM server key.
* Now select the *Credentials* tab.
* Copy your instanceId and SecretKey.
* Update the values in your `config/broadcasting.php` file under the pusher connection, see below.
* You're now good to go.

### Configuration

In `config/broadcasting.php`

``` php
'connections' => [
    ...
    'pusher' => [
        'beams' => [
            'secret_key' => env('PUSHER_BEAMS_SECRET'),
            'instance_id' => env('PUSHER_BEAMS_INSTANCE_ID'),
        ],
    ],

],

```

## Usage

Now you can use the channel in your `via()` method inside the `Notification` class.

``` php
use TcgWebdev\PusherBeams\PusherBeams;
use TcgWebdev\PusherBeams\PusherBeamsMessage;
use Illuminate\Notifications\Notification;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [PusherBeams::class];
    }

    public function toPusherBeamsNotification($notifiable)
    {
        return PusherBeamsMessage::create()
            ->iOS()
            ->badge(1)
            ->sound('success')
            ->body("Your {$notifiable->service} account was approved!");
    }
}
```

### Available Message methods

- `platform('')`: Accepts a string value of `iOS` or `Android`.
- `iOS()`: Sets the platform value to iOS.
- `android()`: Sets the platform value to Android.
- `title('')`: Accepts a string value for the title.
- `body('')`: Accepts a string value for the body.
- `sound('')`: Accepts a string value for the notification sound file. Notice that if you leave blank the default sound value will be `default`.
- `icon('')`: Accepts a string value for the icon file. (Android Only)
- `badge(1)`: Accepts an integer value for the badge. (iOS Only)
- `setOption($key, $value)`: Allows you to set any value in the message payload. For more information [check here for iOS](https://docs.pusher.com/beams/getting-started/ios/publish-notifications#custom-data), [or here for Android](https://docs.pusher.com/beams/getting-started/android/publish-notifications#custom-data).
- `withiOS(PusherBeamsMessage $message)`: Set an extra message to be sent to iOS
- `withAndroid(PusherBeamsMessage $message)`: Set an extra message to be sent to Android

### Sending to multiple platforms

You can send a single message to an iOS device and an Android device at the same time using the `withiOS()` and `withAndroid()` method:

``` php
use TcgWebdev\PusherBeams\PusherBeams;
use TcgWebdev\PusherBeams\PusherBeamsMessage;
use Illuminate\Notifications\Notification;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [PusherBeams::class];
    }

    public function toPusherBeamsNotification($notifiable)
    {
        return PusherBeamsMessage::create()
            ->android()
            ->sound('success')
            ->body("Your {$notifiable->service} account was approved!")
            ->withiOS(PusherBeamsMessage::create()
                ->body("Your {$notifiable->service} account was approved!")
                ->badge(1)
                ->sound('success')
            );
    }
}
```

### Routing a message

By default the Pusher beams "interest" messages will be sent to will be defined using the {notifiable}.{id} convention, for example `App.User.1`, however you can change this behaviour by including a `routeNotificationForPusherPushNotifications()` in the notifiable class method that returns the interest name.

Whatever interest you set in the app is the interest you should register for within your mobil

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email rhyland@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Richard Hyland](https://github.com/TcgWebdev)
- [Neo Ighodaro](https://github.com/neoighodaro)
- Mohamed Said
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
