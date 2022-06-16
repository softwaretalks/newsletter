<?php

use Carbon\Carbon;

function newsletterCounter(): int
{
    $now = Carbon::now();
    // This is our first posting date. (number 1)
    $newsletterStartDate = $now->createFromDate(2021, 01, 02); 
    // minus 23, because we did not send the newsletter for 23 weeks
    $newsletterNumber = (int)(($newsletterStartDate->diffInWeeks($now)) + 1) - 23;

    return $newsletterNumber;
}
