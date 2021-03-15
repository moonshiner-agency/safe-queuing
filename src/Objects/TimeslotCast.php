<?php

namespace Moonshiner\SafeQueuing\Objects;

use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TimeslotCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \App\Models\Address
     */
    public function get($model, $key, $value, $attributes)
    {
        $timeslotArray = json_decode($value, true);

        return new Timeslot([
            'start' => Carbon::parse($timeslotArray['start']),
            'end' => Carbon::parse($timeslotArray['end']),
        ]);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  \App\Models\Timeslot  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes)
    {
        if (!$value instanceof Timeslot) {
            throw new InvalidArgumentException('The given value is not an Timeslot instance.');
        }
        // only store start and end in database
        return collect($value)->only(['start', 'end'])->toJson();
    }
}
