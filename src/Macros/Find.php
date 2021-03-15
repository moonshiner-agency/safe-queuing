<?php

namespace Moonshiner\SafeQueuing\Macros;

use Moonshiner\SafeQueuing\Objects\Timeslot;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

/**
 * Get the next item from the collection.
 *
 * @param mixed $currentItem
 * @param mixed $fallback
 *
 * @mixin \Illuminate\Support\Collection
 *
 * @return mixed
 */
class Find
{
    public function __invoke()
    {
        return function ($timeslot) {

            if ($timeslot instanceof Timeslot) {
                $start = $timeslot->start;
                $end = $timeslot->end;
            } else if (is_array($timeslot)) {
                $start = Carbon::parse($timeslot['start']);
                $end = Carbon::parse($timeslot['end']);
            }

            $timeslot = $this->where('start', '=', $start)->where('end', '=', $end)->first();

            if (!$timeslot) {
                throw new ModelNotFoundException('The timeslot does not exist.');
            }

            return $timeslot;
        };
    }
}
