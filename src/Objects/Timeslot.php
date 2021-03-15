<?php

namespace Moonshiner\SafeQueuing\Objects;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;

class Timeslot implements Castable, Arrayable, Jsonable, JsonSerializable
{
    public Carbon $start;
    public Carbon $end;
    public int $reservation_count = 0;
    public ?Model $reservable = null;

    /**
     * Create a new Object instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct($attributes = [])
    {
        if (isset($attributes['start'])) {
            $this->start = $attributes['start'];
        }

        if (isset($attributes['end'])) {
            $this->end = $attributes['end'];
        }

        if (isset($attributes['reservation_count'])) {
            $this->reservation_count = $attributes['reservation_count'];
        }

        if (isset($attributes['reservable'])) {
            $this->reservable = $attributes['reservable'];
        }
    }

    /**
     * Convert the model instance to an array.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function reservations()
    {
        $reservations = Reservation::where('timeslot', collect($this)->only('start', 'end')->toJson());
        if ($this->reservable !== null) {
            $reservations = $reservations
                ->where('reservable_id', $this->reservable->id)
                ->where('reservable_type', get_class($this->reservable));
        }

        return $reservations->get();
    }

    /**
     * Return the number of reservations of this timeslot.
     *
     * @return int
     */
    public function reservationCount()
    {
        return $this->reservation_count;
    }

    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param array $arguments
     * @return string
     * @return string|\Illuminate\Contracts\Database\Eloquent\CastsAttributes|\Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes
     */
    public static function castUsing(array $arguments)
    {
        return TimeslotCast::class;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $output = [
            'start' => $this->start->toJson(),
            'end' => $this->end->toJson(),
        ];

        if (isset($this->reservation_count)) {
            $output['reservation_count'] = $this->reservation_count;
        }

        return $output;
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     *
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
