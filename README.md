# Laravel Safe Queuing | manage timeslots and limit reservations

[![Latest Version on Packagist](https://img.shields.io/packagist/v/moonshiner/safe-queuing.svg?style=flat-square)](https://packagist.org/packages/moonshiner/safe-queuing)
[![Build Status](https://img.shields.io/travis/moonshiner/safe-queuing/master.svg?style=flat-square)](https://travis-ci.org/moonshiner/safe-queuing)
[![Quality Score](https://img.shields.io/scrutinizer/g/moonshiner/safe-queuing.svg?style=flat-square)](https://scrutinizer-ci.com/g/moonshiner/safe-queuing)
[![Total Downloads](https://img.shields.io/packagist/dt/moonshiner/safe-queuing.svg?style=flat-square)](https://packagist.org/packages/moonshiner/safe-queuing)

This package helps you to attach timeslot and reservation capabilities to your existing Eloquent Models in Laravel.

## Installation

You can install the package via composer:

```bash
composer require moonshiner/safe-queuing
```

You can use the package for timeslot calculation only. To use the reservation capabilities of the package you need to publish the migrations. If you want to change the table name publishing the config is necessary as well. To run the publish command you need to add the Service Provider:

```php
// config/app.php
'providers' => [
    // ...
    Moonshiner\SafeQueuing\SafeQueuingServiceProvider::class,
];
```

If you want to specify a custom table name, you'll need to publish and edit
the configuration file:

```bash
php artisan vendor:publish --provider="Moonshiner\SafeQueuing\SafeQueuingServiceProvider" --tag="config"
```

Publishing and running the migrations for the usage of reservations:

```bash
php artisan vendor:publish --provider="Moonshiner\SafeQueuing\SafeQueuingServiceProvider" --tag="migrations"
php artisan migrate
```

## Usage

You can simply add timeslots to your existing Model via the `HasTimeslots` Trait.

```php
use Moonshiner\SafeQueuing\HasTimeslots;

//...

class Event extends Model
{
    use HasTimeslots;
```

To better configure the contraints for the timeslots you can add the following functions to your model to configure when timeslots are available:

```php
public function timeslotStartDate(){
    return \Carbon\Carbon::now();
}
public function timeslotEndDate(){}
public function timeslotStartTime(){}
public function timeslotEndTime(){}
public function timeslotDuration(){}
public function timeslotBreak(){}
public function timeslotAvailableDays(){}
public function timeslotExcludedDates(){}
public function timeslotIncludedDates(){}
public function timeslotExcludedTimes(){}
```

To show all the timeslots available you can use:

```php
$event = Event::first();

dd($event->timeslots());

```

To show all the reservations run:

```php
$event = Event::first();

dd($event->reservations);
```

You can filter Timeslots

```php
use Carbon\Carbon;
$event = Event::first();


// timeslots after some date
dd($event->timeslots()->findSlot(['start'=>Carbon::now(), 'end'=>Carbon::now()->addMinutes('30')]));

// only timeslots on a specific date
dd($event->timeslots()->onDay(Carbon::today()));

// timeslots after some date
dd($event->timeslots()->afterDate(Carbon::yesterday()));

// timeslots that end before given time
dd($event->timeslots()->beforeDate(Carbon::tomorrow()));
```

To add a reservations run:

```php
$event = Event::first();
$timeslot = $event->timeslots()->first();

$event->reservations()->create([
    'details' => 'Person specific data',
    'timeslot' => $timeslot
]);
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email florian.bauer@moonshiner.at instead of using the issue tracker.

## Credits

-   [Florian Bauer](https://github.com/moonshiner)
-   [Raphael Fleischmann](https://github.com/moonshiner)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
