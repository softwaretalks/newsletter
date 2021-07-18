<?php

use Symfony\Component\Yaml\Yaml;
use Github\Client;

function getPostsFromGitHub(string $repoOrganization, string $repoName, array $labels, string $state): array
{
    $posts = [];

    $githubClient = new Client();
    $issues = $githubClient
        ->api('issue')
        ->all($repoOrganization, $repoName, [
            'labels' => implode(",", $labels),
            'state' => $state
        ]);

    try {
        foreach ($issues as $issue) {
            $posts[] = Yaml::parse($issue['body']);
        }
    } catch (Exception $exception) {
        die("Unable to parse the YAML string: {$exception->getMessage()}" . PHP_EOL);
    }

    return $posts;
}