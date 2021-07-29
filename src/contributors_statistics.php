<?php

require_once __DIR__ . '/vendor/autoload.php';

use Github\Client;
use Github\ResultPager;

$githubClient = new Client();
$paginator    = new ResultPager($githubClient);

$issuesClient = $githubClient->api('issue');
$parameters = ['softwaretalks', 'newsletter', [
    'labels' => 'content,verified',
    'state' => 'closed'
]];

$issuesList = $paginator->fetchAll($issuesClient, 'all' ,$parameters);

$contributorsTemp = [];
foreach($issuesList as $issue) {
    array_push($contributorsTemp, [
       'githubURL'  => $issue['user']['html_url'],
       'username'   => $issue['user']['login'],
       'avatarURL'  => $issue['user']['avatar_url'] . '&s=50',
       'postsCount' => 0
    ]);
}
$contributors = array_values(array_unique($contributorsTemp, SORT_REGULAR));

// Calculate the number of posts per contributor
foreach($issuesList as $issue) {
    $contributorKey = array_search($issue['user']['login'], array_column($contributors, 'username'), true);
    $contributors[$contributorKey]['postsCount']++;
}

// This is some shit code for remove my test issues from my real issues. sorry god.
$ohmydevopsKey = array_search('ohmydevops', array_column($contributors, 'username'), true);
$contributors[$ohmydevopsKey]['postsCount'] -= 6;

// Sort contributors by postsCount
usort($contributors, fn($a,$b) => ($a['postsCount'] <= $b['postsCount']) ? 1 : -1);

// print_r($contributors);
// generate README.md contents for copy paste
foreach ($contributors as $contributor) {
    $username = $contributor['username'];
    $githubURL = $contributor['githubURL'];
    $avatarURL = $contributor['avatarURL'];
    $postsCount = $contributor['postsCount'];

    echo "<a href='$githubURL'><img src='$avatarURL' width='50' alt='$username' title='$postsCount'></a> " . PHP_EOL;
}
