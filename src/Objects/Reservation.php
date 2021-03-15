<?php

namespace Moonshiner\SafeQueuing\Objects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($reservation) {
            // set uuid
            $reservation->{$reservation->getKeyName()} = (string) Str::uuid();
            // set reservation_start time
            $reservation->reservation_start = $reservation->timeslot->start->toDateTimeString();
        });

        static::updating(function ($reservation) {
            // set reservation_start time
            $reservation->reservation_start = $reservation->timeslot->start->toDateTimeString();
        });
    }

    /**
     * Indicates the Key Type
     *
     * @var string
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'details', // json, can be filled with anything (Customer Data if you wish so)
        'timeslot', // full Timeslot, because the timeslot can change but not the reservation
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'details' => 'array',
        'timeslot' => Timeslot::class
    ];

    /**
     * The attributes that should be hidden when serialized
     *
     * @var array
     */
    protected $hidden = [
        'reservable_id',
        'reservable_type'
    ];


    /**
     * The Relationship to the Object the Reservation is attached to
     *
     * @return MorphTo|null
     */
    public function reservable(): MorphTo
    {
        return $this->morphTo();
    }
}
