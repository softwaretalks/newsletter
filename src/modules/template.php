<?php

use WyriHaximus\HtmlCompress\Factory;
use Twig\Loader\FilesystemLoader;
use Amirbagh75\Chalqoz\Chalqoz;
use Twig\Environment;

function generateHtmlTemplate(
        array $posts,
        string $emailTemplateName,
        string $emailTemplateDir,
        string $TOP_CONTENT_HTML,
        string $BOTTOM_CONTENT_HTML,
        int $newsletterNumber
    ): string
{
    $todayDate = Chalqoz::convertEnglishNumbersToPersian(jdate()->format('%A، %d %B %Y'));
    $loader = new FilesystemLoader($emailTemplateDir);
    $twig = new Environment($loader, [
        'strict_variables' => true,
    ]);

    try {
        $htmlTemplate = $twig->render($emailTemplateName, [
            'currentDate'      => $todayDate,
            'newsletterNumber' => Chalqoz::convertEnglishNumbersToPersian((string)$newsletterNumber),
            'posts'            => $posts,
            'contributors'     => array_unique(array_column($posts, 'userFullName')),
            'topContent'       => $TOP_CONTENT_HTML,
            'bottomContent'    => $BOTTOM_CONTENT_HTML
        ]);
        return $htmlTemplate;

    } catch (Exception $exception) {
        die("Unable to render template: {$exception->getMessage()}" . PHP_EOL);
    }
}

function convertToMinifiedHtmlTemplate(string $htmlTemplate): string
{
    $minifier = Factory::constructSmallest();
    $minifiedHtmlTemplate = $minifier->compress($htmlTemplate);

    return $minifiedHtmlTemplate;
}