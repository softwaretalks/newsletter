<?php

use SendinBlue\Client\Model\CreateEmailCampaignRecipients;
use SendinBlue\Client\Model\CreateEmailCampaignSender;
use SendinBlue\Client\Model\CreateEmailCampaign;
use SendinBlue\Client\Api\EmailCampaignsApi;
use SendinBlue\Client\Configuration;
use Amirbagh75\Chalqoz\Chalqoz;

function createNewCampaign(
        Configuration $pakatConfig,
        GuzzleHttp\Client $httpClient,
        $newsletterNumber,
        $minifiedHtmlTemplate,
        $isProduction,
        $pakatEmailAddress,
        $pakatEmailName,
        $newsletterTestListID,
        $newsletterListID
    ): string
{
    $campaignID = "";

    $campaignAPI = new EmailCampaignsApi(
        $httpClient,
        $pakatConfig
    );

    $emailCampaign = new CreateEmailCampaign([
        'name'        => 'SoftwareTalks #'. $newsletterNumber . ($isProduction ? ' - Production' : ' - Test'),
        'subject'     => 'خبرنامه شماره ' . Chalqoz::convertEnglishNumbersToPersian($newsletterNumber),
        'htmlContent' => $minifiedHtmlTemplate,
        'sender'      => new CreateEmailCampaignSender([
            'email'   => $pakatEmailAddress,
            'name'    => $pakatEmailName
        ]),
        'recipients'  => new CreateEmailCampaignRecipients([
            'listIds' => $isProduction ? [$newsletterListID] : [$newsletterTestListID],
        ]),
    ]);
    
    try {
        $result = $campaignAPI->createEmailCampaign($emailCampaign);
        $campaignID = $result['id'];
    } catch (Exception $exception) {
        die("Exception when calling campaignAPI->createEmailCampaign: {$exception->getMessage()}" . PHP_EOL);
    }

    return $campaignID;
}

function sendCampaignByID(string $campaignID, Configuration $pakatConfig, GuzzleHttp\Client $httpClient)
{
    $campaignAPI = new EmailCampaignsApi(
        $httpClient,
        $pakatConfig
    );
    
    try {
        $campaignAPI->sendEmailCampaignNow($campaignID);
    } catch (Exception $exception) {
        die("Exception when calling campaignAPI->sendEmailCampaignNow: {$exception->getMessage()}" . PHP_EOL);
    }
}