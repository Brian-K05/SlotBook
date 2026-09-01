<?php

/**
 * Railway often skips railway.toml [variables]. If SMTP credentials exist,
 * force a live mailer instead of Laravel's default "log" driver.
 */
$username = getenv('MAIL_USERNAME');

if (! is_string($username) || $username === '' || $username === 'null') {
    return;
}

$mailer = getenv('MAIL_MAILER');

if (is_string($mailer) && $mailer === 'array') {
    return;
}

if (! is_string($mailer) || $mailer === '' || $mailer === 'log' || $mailer === 'null' || $mailer === 'resend') {
    putenv('MAIL_MAILER=smtp');
    $_ENV['MAIL_MAILER'] = 'smtp';
    $_SERVER['MAIL_MAILER'] = 'smtp';
}

$host = getenv('MAIL_HOST');

if (! is_string($host) || $host === '' || in_array($host, ['127.0.0.1', 'localhost', 'null'], true)) {
    putenv('MAIL_HOST=smtp-relay.brevo.com');
    $_ENV['MAIL_HOST'] = 'smtp-relay.brevo.com';
    $_SERVER['MAIL_HOST'] = 'smtp-relay.brevo.com';
}

$port = getenv('MAIL_PORT');

if (! is_string($port) || $port === '' || in_array($port, ['2525', 'null'], true)) {
    putenv('MAIL_PORT=587');
    $_ENV['MAIL_PORT'] = '587';
    $_SERVER['MAIL_PORT'] = '587';
}

$scheme = getenv('MAIL_SCHEME');

if (is_string($scheme) && in_array(strtolower($scheme), ['tls', 'smtps', 'ssl', 'null'], true)) {
    putenv('MAIL_SCHEME');
    unset($_ENV['MAIL_SCHEME'], $_SERVER['MAIL_SCHEME']);
}

$domain = getenv('RAILWAY_PUBLIC_DOMAIN');

if (is_string($domain) && $domain !== '' && (! getenv('MAIL_EHLO_DOMAIN') || getenv('MAIL_EHLO_DOMAIN') === 'localhost')) {
    putenv('MAIL_EHLO_DOMAIN='.$domain);
    $_ENV['MAIL_EHLO_DOMAIN'] = $domain;
    $_SERVER['MAIL_EHLO_DOMAIN'] = $domain;
}
