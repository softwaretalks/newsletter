<?php

require_once __DIR__ . '/vendor/autoload.php';

use Github\Client;
use Github\ResultPager;

$githubClient = new Client();
$paginator    = new ResultPager($githubClient);

$issues = $githubClient->api('issue');
$parameters = ['softwaretalks', 'newsletter', [
    'labels' => implode(",", ['content', 'verified']),
    'state' => 'all'
]];

$result = $paginator->fetchAll($issues, 'all' ,$parameters);

$contributorsTemp = [];
foreach($result as $key => $item) {
    array_push($contributorsTemp, [
       'githubURL'  => $item['user']['html_url'],
       'username'   => $item['user']['login'],
       'avatar'     => $item['user']['avatar_url'],
       'postsCount' => 0
    ]);
}
$contributors = array_values(array_unique($contributorsTemp, SORT_REGULAR));

// Calculate the number of posts per contributor 
foreach($result as $key => $item) {
    $contributorKey = array_search($item['user']['login'], array_column($contributors, 'username'), true);
    $contributors[$contributorKey]['postsCount']++;
}

// This is some shit code for remove my test issues from my real issues. sorry god.
$amirbagh75Key = array_search('amirbagh75', array_column($contributors, 'username'), true);
$contributors[$amirbagh75Key]['postsCount'] -= 6;

// Sort contributors by postsCount
usort($contributors, fn($a,$b) => ($a['postsCount'] <= $b['postsCount']) ? 1 : -1);

print_r($contributors);
