<?php

use Github\AuthMethod;
use Symfony\Component\Yaml\Yaml;
use Github\Client;

class github
{
    private Client $githubClient;
    private string $tokenOrLogin;
    private string $password;
    private bool $isAuthenticated = false;

    public function __construct(string $tokenOrLogin, string $password)
    {
        $this->tokenOrLogin = $tokenOrLogin;
        $this->password = $password;
        $this->githubClient = new Client();
    }

    function getPostsFromGitHub(string $repoOrganization, string $repoName, array $labels, string $state): array
    {
        $posts = [];

        $this->authenticate();

        $issues = $this->githubClient
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

    public function authenticate(bool $reAuthenticate = false): void
    {
        if (!$this->isAuthenticated || $reAuthenticate) {
            $this->githubClient
                ->authenticate(
                    $this->tokenOrLogin,
                    $this->password,
                    AuthMethod::CLIENT_ID
                );
            $this->isAuthenticated = true;
        }
    }
}