<?php

if(empty(getenv('PAKAT_API_KEY'))){
    die('Please set PAKAT_API_KEY' . PHP_EOL);
}

if(
    empty(getenv('GITHUB_TOKEN')) ||
    empty(getenv('GITHUB_USER_NAME'))
){
    die('Please config Github ' . PHP_EOL);
}

if(!in_array(getenv('SEND_ENV', true), ['test', 'production'])) {
    putenv('SEND_ENV=test');
}

return [
    'REPOSITORY_ORGANIZATION'       => 'softwaretalks',
    'REPOSITORY_NAME'               => 'newsletter',
    'LABELS'                        => [
                                        'content',
                                        'current-week',
                                        'verified'
                                    ],
    'STATE'                         => 'open',
    'EMAIL_TEMPLATE_DIR'            => __DIR__ . '/EMAIL_TEMPLATES/',
    'EMAIL_TEMPLATE_FILE_NAME'      => 'newsletter.html',
    'EMAIL_TEMPLATE_DARK_FILE_NAME' => 'newsletter_dark.html',
    'PAKAT_API_KEY'                 => getenv('PAKAT_API_KEY', true),
    'PAKAT_SMTP_HOST'               => 'smtp-relay.sendinblue.com',
    'PAKAT_SMTP_PORT'               => '587',
    'PAKAT_EMAIL_NAME'              => 'Softwaretalks newsletter',
    'PAKAT_EMAIL_ADDRESS'           => 'newsletter@softwaretalks.ir',
    'PAKAT_SMTP_DEBUG'              => false,
    'GITHUB_USER_NAME'              => getenv('GITHUB_USER_NAME', true),
    'GITHUB_TOKEN'                  => getenv('GITHUB_TOKEN', true),
    'NEWSLETTER_TEST_LIST_ID'       => 8,
    'NEWSLETTER_LIST_ID'            => 2,
    'SEND_ENV'                      => getenv('SEND_ENV', true),
    'TOP_CONTENT_HTML'              => getenv('TOP_CONTENT_HTML', true),
    'TOP_CONTENT_HTML_DARK'         => getenv('TOP_CONTENT_HTML_DARK', true),
    'BOTTOM_CONTENT_HTML'           => getenv('BOTTOM_CONTENT_HTML', true),
    'BOTTOM_CONTENT_HTML_DARK'      => getenv('BOTTOM_CONTENT_HTML_DARK', true),
    'IS_DARK'                       => getenv('IS_DARK', true)
];
