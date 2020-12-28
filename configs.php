<?php

return [
    'REPOSITORY_ORGANIZATION'  => 'softwaretalks',
    'REPOSITORY_NAME'          => 'newsletter',
    'LABELS'                   => [
                                    'content',
                                    'current-week',
                                    'verified'
                               ],
    'STATE'                    => 'open',
    'EMAIL_TEMPLATE_DIR'       => './EMAIL_TEMPLATES/',
    'EMAIL_TEMPLATE_FILE_NAME' => 'newsletter.html',
    'PAKAT_API_KEY'            => getenv('PAKAT_API_KEY'),
    'PAKAT_SMTP_HOST'          => 'smtp-relay.sendinblue.com',
    'PAKAT_SMTP_PORT'          => '578',
    'PAKAT_SMTP_USERNAME'      => getenv('PAKAT_SMTP_USERNAME'),
    'PAKAT_SMTP_PASSWORD'      => getenv('PAKAT_SMTP_PASSWORD'),
    'PAKAT_SMTP_EMAIL_NAME'    => 'Softwaretalks newsletter',
    'PAKAT_SMTP_EMAIL_ADDRESS' => 'newsletter@softwaretalks.ir',
    'PAKAT_SMTP_DEBUG'         => false,
    'NEWSLETTER_LIST_ID'       => '2',
    'CAN_SEND_EMAIL'           => true
];
