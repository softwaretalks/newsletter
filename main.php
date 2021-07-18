<?php

$scriptStartedAt = microtime(true);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/modules/counter.php';
require_once __DIR__ . '/modules/github.php';
require_once __DIR__ . '/modules/template.php';
$configs = require_once __DIR__ . '/configs.php';

use SendinBlue\Client\Model\CreateEmailCampaignRecipients;
use SendinBlue\Client\Model\CreateEmailCampaignSender;
use SendinBlue\Client\Model\CreateEmailCampaign;
use SendinBlue\Client\Api\EmailCampaignsApi;
use SendinBlue\Client\Api\ContactsApi;
use SendinBlue\Client\Configuration;
use PHPMailer\PHPMailer\PHPMailer;
use Amirbagh75\Chalqoz\Chalqoz;
use Carbon\Carbon;

printf('SEND_ENV: %s', $configs['SEND_ENV'] . PHP_EOL . PHP_EOL);

// config pakat api
$pakatConfig = Configuration::getDefaultConfiguration()->setApiKey('api-key', $configs['PAKAT_API_KEY']);

/*
 * 1- Calculate newsletter number
 */
$newsletterNumber = newsletterCounter();
printf('--> Newsletter number: ' . $newsletterNumber . PHP_EOL);


/*
 * 2- Fetch current-week posts from GitHub
 */
printf('--> Fetching issues from GitHub ...' . PHP_EOL);

$repoOrganization = $configs['REPOSITORY_ORGANIZATION'];
$repoName         = $configs['REPOSITORY_NAME'];
$labels           = $configs['LABELS'];
$state            = $configs['STATE'];

$posts        = getPostsFromGitHub($repoOrganization, $repoName, $labels, $state);
$postsCounter = count($posts);
if ($postsCounter === 0) {
    die('There is no post :( such a bad day bro, but do not despair. nobody knows about tomorrow.' . PHP_EOL);
}
printf("--> We have $postsCounter posts. such a good day bro :)" . PHP_EOL);

/*
 * 3- Generate HTML template
 */
printf('--> Generate HTML template' . PHP_EOL);

$BOTTOM_CONTENT_HTML = $configs['BOTTOM_CONTENT_HTML'];
$TOP_CONTENT_HTML    = $configs['TOP_CONTENT_HTML'];
$emailTemplateName   = $configs['EMAIL_TEMPLATE_FILE_NAME'];
$emailTemplateDir    = $configs['EMAIL_TEMPLATE_DIR'];

$htmlTemplate = generateHtmlTemplate(
    $posts,
    $emailTemplateName,
    $emailTemplateDir,
    $TOP_CONTENT_HTML,
    $BOTTOM_CONTENT_HTML,
    $newsletterNumber
);
$minifiedHtmlTemplate = convertToMinifiedHtmlTemplate($htmlTemplate);

/*
 * 4- Generate HTML archive file
 */
if($configs['SEND_ENV'] === 'production') {
    printf('--> Generate archive file' . PHP_EOL);
    $archiveFileName = 'archives/num' . $newsletterNumber . '.html';
    $isFileCreated = file_put_contents($archiveFileName, $htmlTemplate);
    if(!$isFileCreated) {
        die('Archive not created.');
    }
}

/*
 * 5- Create campaign
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
 * 6- Send campaign
 */
printf('--> Send campaign. ID: ' . $campaignID . PHP_EOL);

try {
    $campaignAPI->sendEmailCampaignNow($campaignID);
} catch (Exception $exception) {
    die("Exception when calling campaignAPI->sendEmailCampaignNow: {$exception->getMessage()}" . PHP_EOL);
}

/*
 * 7- Close related issues
 * It is currently manual.
 */
printf(PHP_EOL . '** Please close the current week issues **');

/*
 * 8- Add archive to website
 * It is currently semi-manual.
 */
if($configs['SEND_ENV'] === 'production') {
    $newsletterNumberFaChar = Chalqoz::convertEnglishNumbersToPersian($newsletterNumber);
    $todayDateWithoutYear = Chalqoz::convertEnglishNumbersToPersian(jdate()->format('%A، %d %B'));
    printf(PHP_EOL . '** Please add below link to the index.html**' . PHP_EOL);
    printf(PHP_EOL . "<li>خبرنامه شماره $newsletterNumberFaChar - $todayDateWithoutYear  <a class='link' href='/$archiveFileName' target='_blank'><i class='em em-arrow_upper_left' aria-role='presentation' aria-label='NORTH WEST ARROW' style='font-size: 12px;'></i></a></li>" . PHP_EOL);
}
/*
 * Done.
 */
$scriptEndedAt = microtime(true);
printf(PHP_EOL . '--> Done. Good job, it took %s seconds.' . PHP_EOL, $scriptEndedAt-$scriptStartedAt);
