<?php

$scriptStartedAt = microtime(true);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/modules/counter.php';
require_once __DIR__ . '/modules/github.php';
require_once __DIR__ . '/modules/archive.php';
require_once __DIR__ . '/modules/template.php';
require_once __DIR__ . '/modules/campaign.php';
$configs = require_once __DIR__ . '/configs.php';

use SendinBlue\Client\Configuration;
use Amirbagh75\Chalqoz\Chalqoz;

// config pakat api and http client
printf('SEND_ENV: %s', $configs['SEND_ENV'] . PHP_EOL . PHP_EOL);
$pakatConfig  = Configuration::getDefaultConfiguration()->setApiKey('api-key', $configs['PAKAT_API_KEY']);
$httpClient   = new GuzzleHttp\Client(['timeout'  => 30]);
$isProduction = ($configs['SEND_ENV'] === 'production') ? true : false;

/*
 * 1- Calculate newsletter number
 */
$newsletterNumber = newsletterCounter();
printf('--> Newsletter number: ' . $newsletterNumber . PHP_EOL);


/*
 * 2- Fetch current-week posts from GitHub
 */
printf('--> Fetching issues from GitHub ...' . PHP_EOL);
$posts = getPostsFromGitHub(
    $configs['REPOSITORY_ORGANIZATION'],
    $configs['REPOSITORY_NAME'],
    $configs['LABELS'],
    $configs['STATE']
);
$postsCounter = count($posts);
if ($postsCounter === 0) {
    die('There is no post :( such a bad day bro, but do not despair. nobody knows about tomorrow.' . PHP_EOL);
}
printf("--> We have $postsCounter posts. such a good day bro :)" . PHP_EOL);

// add dots end of description without dots
for ($i=0; $i < $postsCounter; $i++) { 
    $lastCharacterDescription = substr(ltrim(rtrim($posts[$i]["postDescription"])), -1);
    if($lastCharacterDescription != ".") {
        $posts[$i]["postDescription"] .= ".";
    }
}

/*
 * 3- Generate HTML template
 */
printf('--> Generate HTML template' . PHP_EOL);
if($configs['IS_DARK'] === 'true')
    $htmlTemplate = generateHtmlTemplate(
        $posts,
        $configs['EMAIL_TEMPLATE_DARK_FILE_NAME'],
        $configs['EMAIL_TEMPLATE_DIR'],
        $configs['TOP_CONTENT_HTML_DARK'],
        $configs['BOTTOM_CONTENT_HTML_DARK'],
        $newsletterNumber
    );
else
    $htmlTemplate = generateHtmlTemplate(
        $posts,
        $configs['EMAIL_TEMPLATE_FILE_NAME'],
        $configs['EMAIL_TEMPLATE_DIR'],
        $configs['TOP_CONTENT_HTML'],
        $configs['BOTTOM_CONTENT_HTML'],
        $newsletterNumber
    );
$minifiedHtmlTemplate = convertToMinifiedHtmlTemplate($htmlTemplate);

/*
 * 4- Create campaign
 */
printf('--> Create campaign' . PHP_EOL);
$listID = $isProduction ? $configs['NEWSLETTER_LIST_ID'] : $configs['NEWSLETTER_TEST_LIST_ID'];
$campaignNamePostFix = $isProduction ? 'Production' : 'Test';

$campaignID = createNewCampaign(
    $pakatConfig,
    $httpClient,
    $newsletterNumber,
    $minifiedHtmlTemplate,
    $listID,
    $campaignNamePostFix,
    $configs['PAKAT_EMAIL_ADDRESS'],
    $configs['PAKAT_EMAIL_NAME'],
);

/*
 * 5- Send campaign
 */
printf('--> Sending campaign. ID: ' . $campaignID . PHP_EOL);
sendCampaignByID($campaignID, $pakatConfig, $httpClient);

/*
 * 7- Close related issues
 * It is currently manual.
 */
printf(PHP_EOL . '** Please close the current week issues by your hand. You can change your life with your hands! No sweat. **');

/*
 * 8- Add archive to website
 * It is currently semi-manual.
 */
if($isProduction) {
    printf('--> Generate archive file' . PHP_EOL);
    $archiveFileName = generateArchiveName($newsletterNumber);
    generateArchiveFile($htmlTemplate, $archiveFileName);
    printArchiveFileNameForCopyPaste($newsletterNumber, $archiveFileName);
}

/*
 * Done.
 */
$scriptEndedAt = microtime(true);
printf(PHP_EOL . '--> Done. Good job, it took %s seconds.' . PHP_EOL, $scriptEndedAt-$scriptStartedAt);
