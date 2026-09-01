<?php

/**
 * Railway Hobby blocks outbound SMTP. Prefer a Brevo API key (HTTPS).
 * If MAIL_PASSWORD is already an API key (xkeysib-...), copy it to BREVO_KEY.
 * railway.toml variables often never land on the service, so APP_NAME stays Laravel.
 */
foreach (['APP_NAME', 'MAIL_FROM_NAME'] as $nameKey) {
    $current = getenv($nameKey);

    if (! is_string($current) || $current === '' || $current === 'null' || strcasecmp($current, 'Laravel') === 0) {
        putenv($nameKey.'=SlotBook');
        $_ENV[$nameKey] = 'SlotBook';
        $_SERVER[$nameKey] = 'SlotBook';
    }
}

$password = getenv('MAIL_PASSWORD');
$existing = getenv('BREVO_KEY');

if ((! is_string($existing) || $existing === '' || $existing === 'null')
    && is_string($password)
    && str_starts_with($password, 'xkeysib-')) {
    putenv('BREVO_KEY='.$password);
    $_ENV['BREVO_KEY'] = $password;
    $_SERVER['BREVO_KEY'] = $password;
}
