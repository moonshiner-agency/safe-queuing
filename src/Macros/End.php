<?php

namespace Moonshiner\SafeQueuing\Macros;

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
class End
{
    public function __invoke()
    {
        return function ($date) {
            return $this->where('end', '<', $date);
        };
    }
}
