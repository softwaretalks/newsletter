<?php

use Amirbagh75\Chalqoz\Chalqoz;

function generateArchiveFile($htmlTemplate, $archiveFileName): string
{
    $isFileCreated = file_put_contents($archiveFileName, $htmlTemplate);
    if(!$isFileCreated) {
        die('Archive not created.');
    }
    return $archiveFileName;
}

function printArchiveFileNameForCopyPaste($newsletterNumber, $archiveFileName)
{
    $newsletterNumberFaChar = Chalqoz::convertEnglishNumbersToPersian($newsletterNumber);
    $todayDateWithoutYear = Chalqoz::convertEnglishNumbersToPersian(jdate()->format('%A، %d %B'));
    printf(PHP_EOL . '** Please add below link to the index.html**' . PHP_EOL);
    printf(PHP_EOL . "<li>خبرنامه شماره $newsletterNumberFaChar - $todayDateWithoutYear  <a class='link' href='/$archiveFileName' target='_blank'><i class='em em-arrow_upper_left' aria-role='presentation' aria-label='NORTH WEST ARROW' style='font-size: 12px;'></i></a></li>" . PHP_EOL);
}

function generateArchiveName($newsletterNumber)
{
    return 'archives/num' . $newsletterNumber . '.html';
}