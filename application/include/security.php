<?php

/** Request, session and account-action protections shared by every theme. */
function security_bootstrap()
{
    ini_set('display_errors', '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => strtolower((string) parse_url((string) get_config('baseurl'), PHP_URL_SCHEME)) === 'https',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    header_remove('X-Powered-By');
    header('Cache-Control: no-store');
    header('Referrer-Policy: no-referrer');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header("Content-Security-Policy: base-uri 'self'; object-src 'none'; frame-ancestors 'none'");

    // All application form fields are scalars. Reject array-shaped input before
    // it can reach string functions or a database/SOAP command.
    foreach ([$_POST, $_GET] as $fields) {
        foreach ($fields as $value) {
            if (!is_string($value)) {
                http_response_code(400);
                exit('Invalid request.');
            }
        }
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrf_is_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Your form has expired. Reload the page and try again.');
    }
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_is_valid($token)
{
    return is_string($token) && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function account_action_token($purpose)
{
    $token = bin2hex(random_bytes(32));
    return [$token, $purpose . ':' . time() . ':' . hash('sha256', $token)];
}

function account_action_token_is_valid($stored, $token, $purpose, $now = null)
{
    if (!is_string($stored) || !is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/D', $token)) {
        return false;
    }
    $parts = explode(':', $stored);
    $now = $now ?? time();
    return count($parts) === 3 && $parts[0] === $purpose && ctype_digit($parts[1])
        && (int) $parts[1] <= $now && $now - (int) $parts[1] <= 3600
        && hash_equals($parts[2], hash('sha256', $token));
}

/** A shared, atomic limiter; opening a new session does not reset it. */
function portal_login_rate_limit($identity)
{
    $directory = get_config('login_rate_limit_path') ?: sys_get_temp_dir() . '/registration-login-limits';
    if (!is_dir($directory) && !@mkdir($directory, 0700) && !is_dir($directory)) {
        return false;
    }

    // Limit both the account and peer address. Forwarded headers are deliberately
    // excluded: they are attacker controlled unless the proxy is configured.
    $keys = [
        ['account', strtoupper(trim($identity)), 20],
        ['peer', getIP(), 200],
    ];
    foreach ($keys as [$scope, $key, $limit]) {
        // Bound disk use even when an attacker submits random account names.
        $file = @fopen($directory . '/' . $scope . '-' . substr(hash('sha256', $key), 0, 3), 'c+');
        if ($file === false || !flock($file, LOCK_EX)) {
            if (is_resource($file)) {
                fclose($file);
            }
            return false;
        }
        $data = json_decode(stream_get_contents($file), true);
        $now = time();
        if (!is_array($data) || ($data['start'] ?? 0) + 900 <= $now) {
            $data = ['start' => $now, 'attempts' => 0];
        }
        $allowed = $data['attempts'] < $limit;
        if ($allowed) {
            $data['attempts']++;
            rewind($file);
            ftruncate($file, 0);
            $allowed = fwrite($file, json_encode($data)) !== false;
        }
        flock($file, LOCK_UN);
        fclose($file);
        if (!$allowed) {
            return false;
        }
    }
    return true;
}

/** Verify RFC 6238 six-digit authenticator codes without a remote QR service. */
function portal_totp_is_valid($secret, $code, $now = null)
{
    if ($secret === '' || $secret === null) {
        return true;
    }
    if (!is_string($secret) || !is_string($code) || !preg_match('/\A[0-9]{6}\z/D', $code)) {
        return false;
    }
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = strtoupper($secret);
    if (!preg_match('/\A[A-Z2-7]+\z/D', $secret)) {
        return false;
    }
    $bits = '';
    foreach (str_split($secret) as $character) {
        $bits .= str_pad(decbin(strpos($alphabet, $character)), 5, '0', STR_PAD_LEFT);
    }
    $key = '';
    for ($offset = 0; $offset + 8 <= strlen($bits); $offset += 8) {
        $key .= chr(bindec(substr($bits, $offset, 8)));
    }
    $counter = intdiv($now ?? time(), 30);
    for ($window = -1; $window <= 1; $window++) {
        $hash = hash_hmac('sha1', pack('N2', 0, $counter + $window), $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $number = unpack('N', substr($hash, $offset, 4))[1] & 0x7fffffff;
        if (hash_equals(str_pad((string) ($number % 1000000), 6, '0', STR_PAD_LEFT), $code)) {
            return true;
        }
    }
    return false;
}
