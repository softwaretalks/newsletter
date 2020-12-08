<?php

require_once __DIR__ . '/vendor/autoload.php';

use Github\Client;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

$client = new Client();
$issues = $client->api('issue')->all('softwaretalks', 'newsletter', [
    'labels' => 'content',
    'state' => 'open',
]);

foreach ($issues as $issue) {
    try {
        $parsed = Yaml::parse($issue['body']);
        print_r($parsed);
    } catch (ParseException $exception) {
        printf('Unable to parse the YAML string: %s', $exception->getMessage());
    }
}