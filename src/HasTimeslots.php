<?php

namespace Moonshiner\SafeQueuing;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Moonshiner\SafeQueuing\Objects\Reservation;
use Moonshiner\SafeQueuing\Objects\Timeslot;

trait HasTimeslots
{
    /**
     * Return the reservations of Object.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function reservations(): MorphMany
    {
        return $this->morphMany(Reservation::class, 'reservable');
    }

    /**
     * Check if Object has a Reservation
     *
     * @return bool
     */
    public function hasReservation(): bool
    {
        return !$this->reservations->isEmpty();
    }

    /**
     * get the timeslots for the Object
     *
     * @return \Illuminate\Support\Collection
     */
    public function timeslots(): Collection
    {
        $sq = SafeQueuing::init();
        $sq->start_date = $this->timeslotStartDate();
        $sq->end_date = $this->timeslotEndDate();

        $sq->start_time = $this->timeslotStartTime();
        $sq->end_time = $this->timeslotEndTime();
        $sq->duration = $this->timeslotDuration();
        $sq->break = $this->timeslotBreak();

        $sq->available_days = $this->timeslotAvailableDays();
        $sq->exclude_dates = $this->timeslotExcludedDates();
        $sq->include_dates = $this->timeslotIncludedDates();

        $sq->exclude_times = $this->timeslotExcludedTimes();

        $sq->reservable = $this;

        return $sq->display();
    }

    /**
     * Check if the maximum amount of reservations is reached for a timeslot
     *
     * @param Timeslot $timeslot
     * @return bool
     */
    public function isTimeslotFull(Timeslot $timeslot): bool
    {
        $count = $this->reservations()->where('reservation_start', $timeslot->start->toDateTimeString())->count();
        return $this->timeslotCapacity() <= $count;
    }

    /**
     * Define how many reservations can be set per timeslot
     *
     * @return int
     */
    public function timeslotCapacity(): int
    {
        return 4;
    }

    /**
     * Define when the Timeslot starts (Date)
     *
     * @return \Carbon\Carbon
     */
    public function timeslotStartDate(): Carbon
    {
        return Carbon::now();
    }

    /**
     * Define when the Timeslot ends (Date)
     *
     * @return \Carbon\Carbon|null
     */
    public function timeslotEndDate(): ?Carbon
    {
        return null;
    }

    /**
     * Define when the Timeslot starts (Time)
     *
     * @return \Carbon\Carbon|null
     */
    public function timeslotStartTime(): ?Carbon
    {
        return Carbon::now()->setTimeFromTimeString('09:00');
    }

    /**
     * Define when the Timeslot ends (Time)
     *
     * @return \Carbon\Carbon|null
     */
    public function timeslotEndTime(): ?Carbon
    {
        return null;
    }

    /**
     * Define the Interval of the slots
     *
     * @return \Carbon\CarbonInterval
     */
    public function timeslotDuration(): CarbonInterval
    {
        return CarbonInterval::make('30m');
    }

    /**
     * Define if there should be a break inbetween
     *
     * @return \Carbon\CarbonInterval|null
     */
    public function timeslotBreak(): ?CarbonInterval
    {
        return null;
    }

    /**
     * Define the week days timeslots are available
     *
     * @return array
     */
    public function timeslotAvailableDays(): array
    {
        return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    }

    /**
     * Define which days should be excluded
     *
     * @return array
     */
    public function timeslotExcludedDates(): array
    {
        return [];
    }

    /**
     * Define which days should be included
     *
     * @return array
     */
    public function timeslotIncludedDates(): array
    {
        return [];
    }

    /**
     * Define which times should be excluded
     *
     * @return array
     */
    public function timeslotExcludedTimes(): array
    {
        return [];
    }
}
