<?php

if(empty(getenv('PAKAT_API_KEY'))){
    die('Please set PAKAT_API_KEY' . PHP_EOL);
}

if(!in_array(getenv('SEND_ENV', true), ['test', 'production'])) {
    putenv('SEND_ENV=test');
}

return [
    'REPOSITORY_ORGANIZATION'  => 'softwaretalks',
    'REPOSITORY_NAME'          => 'newsletter',
    'LABELS'                   => [
                                    'content',
                                    'current-week',
                                    'verified'
                               ],
    'STATE'                    => 'open',
    'EMAIL_TEMPLATE_DIR'       => __DIR__ . '/EMAIL_TEMPLATES/',
    'EMAIL_TEMPLATE_FILE_NAME' => 'newsletter.html',
    'PAKAT_API_KEY'            => getenv('PAKAT_API_KEY', true),
    'PAKAT_SMTP_HOST'          => 'smtp-relay.sendinblue.com',
    'PAKAT_SMTP_PORT'          => '587',
    'PAKAT_EMAIL_NAME'         => 'Softwaretalks newsletter',
    'PAKAT_EMAIL_ADDRESS'      => 'newsletter@softwaretalks.ir',
    'PAKAT_SMTP_DEBUG'         => false,
    'NEWSLETTER_TEST_LIST_ID'  => 8,
    'NEWSLETTER_LIST_ID'       => 2,
    'SEND_ENV'                 => getenv('SEND_ENV', true),
    'TOP_CONTENT_HTML'         => getenv('TOP_CONTENT_HTML', true),
    'BOTTOM_CONTENT_HTML'      => getenv('BOTTOM_CONTENT_HTML', true)
];
