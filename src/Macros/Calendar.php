<?php

namespace Moonshiner\SafeQueuing\Macros;

use Moonshiner\SafeQueuing\Objects\Timeslot;

/**
 * Get the next item from the collection.
 *
 * @mixin \Illuminate\Support\Collection
 *
 * @return mixed
 */
class Calendar
{
    public function __invoke()
    {
        // @todo: that should be better
        return function ($days, $event) {
            $this->each(function ($timeslot) use (&$days, $event) {

                /** @var Timeslot $timeslot */
                $date = $timeslot->start->format('Y-m-d');
                $status = $event->getStatus($timeslot);

                if (!isset($days[$date])) {
                    // day is not yet in array
                    $days[$date] = [
                        'status' => $status,
                    ];
                } else if ($days[$date]['status'] == 'full' && $status == 'available') {
                    // day is already in array
                    // there are still slots available for this slot
                    $days[$date]['status'] = 'available';
                }
            });

            return $days;
        };
    }
}
