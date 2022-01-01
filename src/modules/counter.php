<?php

use Carbon\Carbon;

function newsletterCounter(): int
{
    $now = Carbon::now();
    // This is our first posting date. (number 1)
    $newsletterStartDate = $now->createFromDate(2021, 01, 02); 
    // minus 6, because we did not send the newsletter for 4 weeks in Farvardin 1400 & 2 week in Mordad 1400.
    $newsletterNumber = (int)(($newsletterStartDate->diffInWeeks($now)) + 1) - 8;

    return $newsletterNumber;
}
