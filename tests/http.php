<?php

// Run against an isolated Apache instance configured with fixtures/config.php.
$base = $argv[1] ?? 'http://127.0.0.1';
$checks = 0;
function expect_http($condition, $message)
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}
function request_http($base, $path, $post = null, $cookie = '')
{
    $headers = '';
    $curl = curl_init($base . $path);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_COOKIE => $cookie,
        CURLOPT_HEADERFUNCTION => static function ($curl, $line) use (&$headers) {
            $headers .= $line;
            return strlen($line);
        },
    ]);
    if ($post !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = curl_exec($curl);
    return [curl_getinfo($curl, CURLINFO_RESPONSE_CODE), $body, $headers];
}

[$status, $page, $headers] = request_http($base, '/');
$cookieHeaders = $headers;
$headers = strtolower($headers);
expect_http($status === 200 && str_contains($page, 'page=account'), 'The multi-page home links to the player account');
expect_http(str_contains($headers, 'httponly') && str_contains($headers, 'samesite=lax'), 'Session cookies have browser protections');
expect_http(str_contains($headers, 'secure'), 'HTTPS deployments mark session cookies Secure');
expect_http(str_contains($headers, 'cache-control: no-store') && str_contains($headers, 'referrer-policy: no-referrer'), 'Account pages avoid caching and token referrer leaks');
expect_http(str_contains($headers, 'x-frame-options: deny') && !str_contains($headers, 'x-powered-by'), 'Anti-framing and runtime disclosure protections apply');
preg_match('/set-cookie: (PHPSESSID=[^;\r\n]+)/i', $cookieHeaders, $cookie);
[$status, $page] = request_http($base, '/index.php?page=account', null, $cookie[1]);
expect_http($status === 200 && str_contains($page, 'player-portal'), 'The dedicated player account page renders');
preg_match('/name="csrf_token" value="([a-f0-9]{64})"/', $page, $csrf);
expect_http(isset($csrf[1]), 'Forms contain a CSRF token');
[$status] = request_http($base, '/index.php', ['submit' => 'store_checkout']);
expect_http($status === 403, 'Forged store checkout is blocked');
[$status] = request_http($base, '/index.php', ['submit' => 'portal_logout', 'csrf_token' => str_repeat('0', 64)], $cookie[1]);
expect_http($status === 403, 'An incorrect token is blocked with the session cookie');
[$status] = request_http($base, '/index.php', ['submit' => 'portal_logout', 'csrf_token' => $csrf[1]], $cookie[1]);
expect_http($status === 200, 'A valid logout form works');
[$status] = request_http($base, '/index.php', ['password' => ['malformed'], 'csrf_token' => $csrf[1]], $cookie[1]);
expect_http($status === 400, 'Array-shaped fields cannot trigger PHP type errors');

foreach (['/.git/HEAD', '/.git/config', '/.env', '/AGENT.md', '/application/composer.json', '/tests/security.php'] as $path) {
    [$status] = request_http($base, $path);
    expect_http(in_array($status, [403, 404], true), 'Sensitive path is denied: ' . $path);
}

[$status, $page] = request_http($base, '/index.php?restore=testuser&key=' . str_repeat('a', 64));
expect_http($status === 200 && str_contains($page, 'confirm_restore') && str_contains($page, 'name="repassword"'), 'Recovery GET displays a confirmation form without contacting the database');
expect_http(!str_contains($page, 'Your new account information'), 'Recovery never displays or emails a generated plaintext password');
echo "$checks HTTP security checks passed.\n";
