<?php

$scriptStartedAt = microtime(true);

require_once __DIR__ . '/vendor/autoload.php';
$configs = require_once __DIR__ . '/configs.php';

use SendinBlue\Client\Model\CreateEmailCampaignRecipients;
use SendinBlue\Client\Model\CreateEmailCampaignSender;
use SendinBlue\Client\Model\CreateEmailCampaign;
use SendinBlue\Client\Api\EmailCampaignsApi;
use SendinBlue\Client\Api\ContactsApi;
use WyriHaximus\HtmlCompress\Factory;
use SendinBlue\Client\Configuration;
use PHPMailer\PHPMailer\PHPMailer;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Yaml\Yaml;
use Amirbagh75\Chalqoz\Chalqoz;
use Twig\Environment;
use Github\Client;
use Carbon\Carbon;

printf('SEND_ENV: %s', $configs['SEND_ENV'] . PHP_EOL . PHP_EOL);

// config pakat api
$pakatConfig = Configuration::getDefaultConfiguration()->setApiKey('api-key', $configs['PAKAT_API_KEY']);

/*
 *
 * 1- Calculate newsletter number
 *
 */
printf('--> Calculate newsletter number' . PHP_EOL);
$now = Carbon::now();
$newsletterStartDate = Carbon::createFromDate(2021, 01, 02); // This is our first posting date. (number 1)
$newsletterNumber = (int)($newsletterStartDate->diffInWeeks($now)) + 1;
printf('--> Newsletter number: ' . $newsletterNumber . PHP_EOL);


/*
 *
 * 2- Fetch issues from GitHub
 *
 */
printf('--> Fetch issues from GitHub' . PHP_EOL);
$posts = [];
$contributors = [];

$githubClient = new Client();
$issues = $githubClient->api('issue')->all($configs['REPOSITORY_ORGANIZATION'], $configs['REPOSITORY_NAME'], [
    'labels' => implode(",", $configs['LABELS']),
    'state' => $configs['STATE']
]);

try {
    $contributorsTemp = [];
    foreach ($issues as $issue) {
        $body = Yaml::parse($issue['body']);
        $contributorsTemp[] = $body['userFullName'];
        unset($body['userFullName']);
        $posts[] = $body;
    }
    if (count($posts) === 0) {
        die('There is no post!' . PHP_EOL);
    }
    $contributors = array_values(array_unique($contributorsTemp));
} catch (Exception $exception) {
    die("Unable to parse the YAML string: {$exception->getMessage()}" . PHP_EOL);
}


/*
 *
 * 3- Generate HTML template based on issues
 *
 */
printf('--> Generate HTML template based on issues' . PHP_EOL);
$htmlTemplate = "";

$loader = new FilesystemLoader($configs['EMAIL_TEMPLATE_DIR']);
$twig = new Environment($loader, [
    'strict_variables' => true,
]);

try {
    $htmlTemplate = $twig->render($configs['EMAIL_TEMPLATE_FILE_NAME'], [
        'currentDate'      => Chalqoz::convertEnglishNumbersToPersian(jdate()->format('%A، %d %B %y')),
        'newsletterNumber' => Chalqoz::convertEnglishNumbersToPersian($newsletterNumber),
        'posts'            => $posts,
        'contributors'     => $contributors,
        'topContent'       => $configs['TOP_CONTENT_HTML'],
        'bottomContent'    => $configs['BOTTOM_CONTENT_HTML']
    ]);
    $minifier = Factory::constructSmallest();
    $minifiedHtmlTemplate = $minifier->compress($htmlTemplate);
} catch (Exception $exception) {
    die("Unable to render template: {$exception->getMessage()}" . PHP_EOL);
}


/*
 *
 * 4- Create campaign
 *
 */
printf('--> Create campaign' . PHP_EOL);
$campaignID = "";

$campaignAPI = new EmailCampaignsApi(
    new GuzzleHttp\Client(),
    $pakatConfig
);

$emailCampaign = new CreateEmailCampaign([
    'name'        => 'SoftwareTalks #'. $newsletterNumber . (($configs['SEND_ENV'] === 'test') ? ' - Test' : ' - Production'),
    'subject'     => 'خبرنامه شماره ' . Chalqoz::convertEnglishNumbersToPersian($newsletterNumber),
    'htmlContent' => $minifiedHtmlTemplate,
    'sender'      => new CreateEmailCampaignSender([
        'email'   => $configs['PAKAT_EMAIL_ADDRESS'],
        'name'    => $configs['PAKAT_EMAIL_NAME']
    ]),
    'recipients'  => new CreateEmailCampaignRecipients([
        'listIds' => ($configs['SEND_ENV'] === 'test') ? [$configs['NEWSLETTER_TEST_LIST_ID']] : [$configs['NEWSLETTER_LIST_ID']],
    ]),
]);

try {
    $result = $campaignAPI->createEmailCampaign($emailCampaign);
    $campaignID = $result['id'];
} catch (Exception $exception) {
    die("Exception when calling campaignAPI->createEmailCampaign: {$exception->getMessage()}" . PHP_EOL);
}


/*
 *
 * 5- Send campaign
 *
 */
printf('--> Send campaign. ID: ' . $campaignID . PHP_EOL);

try {
    $campaignAPI->sendEmailCampaignNow($campaignID);
} catch (Exception $exception) {
    die("Exception when calling campaignAPI->sendEmailCampaignNow: {$exception->getMessage()}" . PHP_EOL);
}


/*
 *
 * 6- Close related issues
 * It is currently manual.
 *
 */
printf(PHP_EOL . '** Please close the current week issues **');


/*
 *
 * 7- Add archive to website
 * It is currently manual.
 *
 */
printf(PHP_EOL . '** Please add archive to the website**' . PHP_EOL);


/*
 *
 * Done.
 *
 */
$scriptEndedAt = microtime(true);
printf(PHP_EOL . '--> Done. Good job, it took %s seconds.' . PHP_EOL, $scriptEndedAt-$scriptStartedAt);
