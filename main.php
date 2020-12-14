<?php

// TODO: This code just works. Must be cleaned after finishing work!

require_once __DIR__ . '/vendor/autoload.php';

use Github\Client;
use Twig\Environment;
use Symfony\Component\Yaml\Yaml;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Yaml\Exception\ParseException;

/*
 * 1- get current-week posts from issues
 */
$githubClient = new Client();
$posts = [];
$issues = $githubClient->api('issue')->all('softwaretalks', 'newsletter', [
    'labels' => 'content,current-week,verified',
    'state' => 'open',
]);

foreach ($issues as $issue) {
    try {
        $posts[] = Yaml::parse($issue['body']);
    } catch (ParseException $exception) {
        printf('Unable to parse the YAML string: %s', $exception->getMessage());
    }
}

/*
 * 2- make html template based on contents
 */
$loader = new FilesystemLoader('./EMAIL_TEMPLATES/');
$twig = new Environment($loader, [
    'strict_variables' => true,
]);

try {
    $template = $twig->render('newsletter.html', [
        'currentDate' => jdate()->format('%A, %d %B %y'),
        'newsletterNumber' => '1',
        'posts' => $posts,
    ]);
    print_r($template);
} catch (Exception $exception) {
    printf('Unable to render template: %s', $exception->getMessage());
}

/*
 * 3- send email to all subscribers
 */


/*
 * 4- close related issues
 */


/*
 * 5- add archive to website
 */
