<?php

/*
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


date_default_timezone_set('UTC');

require_once __DIR__ . '../../vendor/autoload.php';
use Morilog\Jalali\Jalalian;
use FeedWriter\ATOM;
use Amirbagh75\Chalqoz\Chalqoz;

use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\preg_match;

function findJdate(string $html): Jalalian
{
    $JMONTHS = array(
      "فروردین",
      "اردیبهشت",
      "خرداد",
      "تیر",
      "مرداد",
      "شهریور",
      "مهر",
      "آبان",
      "آذر",
      "دی",
      "بهمن",
      "اسفند"
    );

    preg_match("/<h3.*?، (.+) <\/h3>/s", $html, $matches);
    $jdateStr = $matches[1];
    for ($i=0; $i < count($JMONTHS); $i++) {
        $jdateStr = str_replace($JMONTHS[$i], sprintf("%02d", $i+1), $jdateStr);
    }
    $jdateStr = Chalqoz::convertPersianNumbersToEnglish($jdateStr);
    $jdateStr = str_replace("99", "1399", $jdateStr);
    preg_match("/(\S*) (\S+) (\S+)/s", $jdateStr, $matches);
    $jdate = Jalalian::fromFormat('d m Y', $matches[0]);
    return $jdate;
}

function generateAtomFeed()
{
    $AtomFeed = new ATOM();
    $AtomFeed->setTitle(' خبرنامهٔ نرم‌افزاریِ SoftwareTalks');
    $AtomFeed->setDescription('ما مهندسایِ نرم‌افزار و علاقه‌مندانِ کامپیوتر هرازگاهی با مطالبِ جالبی برخورد می‌کنیم که می‌تونه به دردِ بقیه هم بخوره.در حال حاضر، هر پنجشنبه خبرنامه ارسال میشود');
    $AtomFeed->setLink('https://newsletter.softwaretalks.ir');
    $AtomFeed->setDate(new DateTime());
    $AtomFeed->setImage('https://twemoji.maxcdn.com/2/72x72/2709.png');

    // Now Add html files from 'archives' directory to the feed
    $dir = new DirectoryIterator(__DIR__ . '/../../archives');
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $newItem = $AtomFeed->createNewItem();
            $newItem->setTitle(str_replace("num", "شماره خبرنامه ", $fileinfo->getBasename()));
            $newItem->setLink('https://newsletter.softwaretalks.ir/archives/' . $fileinfo->getFilename());
            $newItem->setDate(findJdate(file_get_contents($fileinfo->getPathname()))->toCarbon());
            $newItem->setContent(file_get_contents($fileinfo->getPathname()));

            //Now add the feed item
            $AtomFeed->addItem($newItem);
        }
    }
    //OK. Everything is done. Now generate the feed.
    file_put_contents(__DIR__ . '/../../atom.xml', $AtomFeed->generateFeed());
}