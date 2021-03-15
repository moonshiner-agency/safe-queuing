<?php

namespace Moonshiner\SafeQueuing;

use Exception;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Moonshiner\SafeQueuing\Objects\Reservation;
use Moonshiner\SafeQueuing\Objects\Timeslot;


class SafeQueuing
{
    /**
     *
     * @var Singleton
     */
    private static $instance;

    // reservable model
    public ?Model $reservable = null;

    // units for timeslots in DateInterval Format
    public CarbonInterval $duration;

    /* start/end dates */
    public ?Carbon $start_date;
    public ?Carbon $start_time;
    public ?Carbon $end_date;
    public ?Carbon $end_time;

    public ?CarbonInterval $break;

    /* days */
    public array $available_days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

    /* exclude dates */
    public array $exclude_dates = [];
    public array $include_dates = [];

    /* exclude times */
    public array $exclude_times = [];

    /**
     * Create a new Object instance.
     *
     * @return void
     */
    public function __construct()
    {
        // set default startDate to today
        $this->start_date = Carbon::now();

        // set default DateInterval to 30min
        $this->duration = CarbonInterval::make('30m');
    }

    /**
     * Singleton Pattern
     *
     * @return SafeQueuing
     */
    public static function init(): SafeQueuing
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Displaying the Available timeslots
     *
     * @return \Illuminate\Support\Collection;
     */
    public function display(): Collection
    {
        // we validate the values
        $this->validate();

        // lets create timeslots based on the constraints
        $output = Collection::make([]);

        // we go in the future till the enddate (or 30 days from now)
        if (isset($this->end_date)) {
            $days_to_the_future = $this->start_date->diffInDays($this->end_date);
        } else {
            $days_to_the_future = $this->start_date->diffInDays(Carbon::now()->addDays(30));
        }

        // get reservations for this date range in one query
        // it should be a fast query, maybe we have to cache it nevertheless (optional?)
        $reservations = Reservation::where('reservation_start', '>=', $this->start_date->toDateString())
            ->where('reservation_start', '<', $this->start_date->copy()->addDays($days_to_the_future + 1)->toDateString());

        if ($this->reservable !== null) {
            $reservations = $reservations->where('reservable_id', $this->reservable->id)
                ->where('reservable_type', get_class($this->reservable));
        }

        $reservations = $reservations->select('reservation_start', DB::raw('count(*) as count'))
            ->groupBy('reservation_start')
            ->orderBy('reservation_start')
            ->get()
            ->keyBy('reservation_start')
            ->map(function ($entry) {
                return $entry['count'];
            });


        // if duration is longer than a day we need a day collection
        $day_collector = [];

        // we loop through the days for proper filling
        foreach (range(0, $days_to_the_future) as $day) {
            $current = $this->start_date->copy()->addDays($day);
            //
            // #1
            // we start on the day specific level

            // check if the date is excluded
            if (in_array($current->format('Y-m-d'), $this->exclude_dates)) {
                continue;
            }

            // check if the day is included
            if (in_array($current->format('Y-m-d'), $this->include_dates)) {
                // day is included
            }
            // if not check if the weekday is included
            else if (!in_array($current->format('D'), $this->available_days)) {
                continue;
            }

            //
            // Alright, we now can be sure, that this day is a day with timeslots

            //
            // #2
            // duration is longer than a day

            // Duration is longer than a day
            if ((new CarbonInterval('P1D'))->lessThan($this->duration)) {

                // if empty we start a day sequence
                if (empty($day_collector)) {
                    $day_collector = [
                        'start' => $current->setTimeFrom($this->start_time),
                        'day_count' => 1
                    ];
                    continue;
                }

                // if ongoing we add the days it is going so far
                $day_collector['day_count'] = $day_collector['day_count'] + 1;
                if ($this->duration->dayz > $day_collector['day_count']) {
                    continue;
                }

                // we check how many reservations were already made
                $rcount = $reservations->get($day_collector['start']);

                // if days are reached we end day sequence
                $timeslot = new Timeslot([
                    'start' => $day_collector['start'],
                    'end' => $current->setTimeFrom($this->end_time),
                    'reservation_count' => $rcount,
                    'reservable' => $this->reservable,
                ]);
                $output->push($timeslot);

                $day_collector = [];
                continue;
            }

            //
            // #3
            // we now check specific times for the day
            $start = $current->copy()->setTimeFrom($this->start_time);
            $end = $current->copy()->setTimeFrom($this->end_time);
            $interval = $this->duration->copy();

            // if there is a break we add it to the interval
            if (isset($this->break)) {
                $interval->add($this->break);
            }

            // we create a period for looping
            $period = new CarbonPeriod($start, $interval, $end);

            foreach ($period as $date) {

                $endTime = $date->copy()->add($this->duration);

                // shouldn't be longer than final Time
                if ($endTime->format('H:i:s') > $this->end_time->format('H:i:s')) {
                    continue;
                }

                // check if the time is excluded
                if (in_array($date->format('H:i:s'), $this->exclude_times)) {
                    continue;
                }

                // we check how many reservations were already made
                $rcount = $reservations->get($date->toDateTimeString());

                $timeslot = new Timeslot([
                    'start' => $date,
                    'end' => $endTime,
                    'reservation_count' => $rcount,
                    'reservable' => $this->reservable,
                ]);

                $output->push($timeslot);
            }
        }

        return $output;
    }

    /**
     * Validation Function
     *
     * @return \Illuminate\Support\Collection;
     */
    private function validate(): void
    {
        // make sure startDate is before endDate
        if (isset($this->end_date) && $this->start_date > $this->end_date) {
            throw new Exception('Start Date must be before End Date');
        }

        // if no time isset we use the full day
        if (!isset($this->start_time)) {
            $this->start_time = Carbon::createFromTimeString('00:00');
        }
        if (!isset($this->end_time)) {
            $this->end_time = Carbon::createFromTimeString('23:59');
        }

        // make sure startTime is before endTime
        if (isset($this->start_time) && isset($this->end_time) && $this->start_time > $this->end_time) {
            throw new Exception('Start Time must be before End Time');
        }
    }
}
