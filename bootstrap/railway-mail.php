<?php

/**
 * Railway Hobby blocks outbound SMTP. Prefer a Brevo API key (HTTPS).
 * If MAIL_PASSWORD is already an API key (xkeysib-...), copy it to BREVO_KEY.
 */
$password = getenv('MAIL_PASSWORD');
$existing = getenv('BREVO_KEY');

if ((! is_string($existing) || $existing === '' || $existing === 'null')
    && is_string($password)
    && str_starts_with($password, 'xkeysib-')) {
    putenv('BREVO_KEY='.$password);
    $_ENV['BREVO_KEY'] = $password;
    $_SERVER['BREVO_KEY'] = $password;
}
