<?php

$scriptStartedAt = microtime(true);

require_once __DIR__ . '/vendor/autoload.php';
$configs = require_once __DIR__ . '/configs.php';

use SendinBlue\Client\Api\ContactsApi;
use WyriHaximus\HtmlCompress\Factory;
use SendinBlue\Client\Configuration;
use PHPMailer\PHPMailer\PHPMailer;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Yaml\Yaml;
use Amirbagh75\Chalqoz\Chalqoz;
use Twig\Environment;
use Github\Client;

/*
 *
 * 1- Fetch issues from GitHub
 *
 */
printf('--> Fetch issues from GitHub' . PHP_EOL);
$posts = [];

$githubClient = new Client();
$issues = $githubClient->api('issue')->all($configs['REPOSITORY_ORGANIZATION'], $configs['REPOSITORY_NAME'], [
    'labels' => implode(",", $configs['LABELS']),
    'state' => $configs['STATE']
]);

try {
    foreach ($issues as $issue) {
        $posts[] = Yaml::parse($issue['body']);
    }
    if (count($posts) === 0) {
        die('There is no post!' . PHP_EOL);
    }
} catch (Exception $exception) {
    die("Unable to parse the YAML string: {$exception->getMessage()}" . PHP_EOL);
}

/*
 *
 * 2- Generate HTML template based on issues
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
        'newsletterNumber' => Chalqoz::convertEnglishNumbersToPersian('1'),
        'posts'            => $posts,
    ]);
    $minifier = Factory::constructSmallest();
    $minifiedHtmlTemplate = $minifier->compress($htmlTemplate);
} catch (Exception $exception) {
    die("Unable to render template: {$exception->getMessage()}" . PHP_EOL);
}

/*
 *
 * 3- Fetch a list of registered users
 *
 */
printf('--> Fetch a list of registered users' . PHP_EOL);
$userEmails = [];

$pakatConfig = Configuration::getDefaultConfiguration()->setApiKey('api-key', $configs['PAKAT_API_KEY']);
$contactsInstance = new ContactsApi(
    new GuzzleHttp\Client(),
    $pakatConfig
);

try {
    $newsletterListID = "";
    if($configs['SEND_ENV'] === 'test') {
        $newsletterListID = $configs['NEWSLETTER_TEST_LIST_ID'];
    }
    elseif($configs['SEND_ENV'] === 'production') {
        $newsletterListID = $configs['NEWSLETTER_LIST_ID'];
    }
    $contacts = $contactsInstance->getContactsFromList($newsletterListID)->getContacts();
    if (count($contacts) === 0) {
        die("There is no user!" . PHP_EOL);
    }
    foreach ($contacts as $contact) {
        if ($contact['emailBlacklisted'] === false) {
            array_push($userEmails, $contact['email']);
        }
    }
} catch (Exception $exception) {
    die("Exception when calling AccountApi->getContactsFromList: {$exception->getMessage()}" . PHP_EOL);
}

/*
 *
 * 4- Send email to all users with SMTP server
 *
 */
printf('--> Send email to all users with SMTP server' . PHP_EOL);

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->SMTPDebug  = $configs['PAKAT_SMTP_DEBUG'];
    $mail->SMTPAuth   = true;
    $mail->Timeout    = 60;
    $mail->Host       = $configs['PAKAT_SMTP_HOST'];
    $mail->Port       = $configs['PAKAT_SMTP_PORT'];
    $mail->Username   = $configs['PAKAT_SMTP_USERNAME'];
    $mail->Password   = $configs['PAKAT_SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet    = 'UTF-8';
    $mail->Subject    = 'خبرنامه Software Talks، شماره یک';
    $mail->Body       = $minifiedHtmlTemplate;
    foreach ($userEmails as $key => $value) {
        $mail->addBCC($value);
    }
    $mail->setFrom($configs['PAKAT_SMTP_EMAIL_ADDRESS'], $configs['PAKAT_SMTP_EMAIL_NAME']);
    $mail->send();
} catch (Exception $exception) {
    die("Message could not be sent. Mailer Error: {$exception->getMessage()}" . PHP_EOL);
}

/*
 *
 * 5- Close related issues
 * It is currently manual.
 *
 */


/*
 *
 * 6- Add archive to website
 * It is currently manual.
 *
 */


/*
 *
 * Done.
 *
 */

$scriptEndedAt = microtime(true);
printf('--> Done. Good job, it took %s seconds.' . PHP_EOL, $scriptEndedAt-$scriptStartedAt);
