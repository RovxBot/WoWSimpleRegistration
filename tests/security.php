<?php

require __DIR__ . '/../application/vendor/autoload.php';
require __DIR__ . '/../application/include/functions.php';
require __DIR__ . '/../application/include/security.php';
require __DIR__ . '/../application/include/database.php';
require __DIR__ . '/../application/include/user.php';
require __DIR__ . '/../application/include/store.php';

$checks = 0;
function check($condition, $message)
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}

$config = ['baseurl' => 'https://example.invalid', 'server_core' => 1, 'srp6_support' => true, 'captcha_type' => 4];
$core_config = ['salt_field' => 'salt', 'verifier_field' => 'verifier'];
$language = [];
$_SESSION = [];
check(!csrf_is_valid('forged'), 'A token is required before any POST');
$csrf = csrf_token();
check(strlen($csrf) === 64 && csrf_token() === $csrf, 'CSRF tokens are random and session-bound');
check(csrf_is_valid($csrf) && !csrf_is_valid(str_repeat('0', 64)), 'Only the session CSRF token is accepted');
check(!csrf_is_valid([$csrf]), 'Array-shaped CSRF input is rejected');

[$token, $stored] = account_action_token('reset');
check(!str_contains($stored, $token), 'Recovery stores a digest rather than the bearer token');
check(account_action_token_is_valid($stored, $token, 'reset'), 'A fresh recovery token works');
check(!account_action_token_is_valid($stored, $token, '2fa'), 'Recovery tokens cannot enable 2FA');
check(!account_action_token_is_valid($stored, $token, 'reset', time() + 3601), 'Recovery expires after one hour');
check(!account_action_token_is_valid($stored, $token, 'reset', time() - 5), 'Future-dated tokens are rejected');
check(!account_action_token_is_valid('0e12345', '0e54321', 'reset'), 'Legacy and magic-hash recovery tokens are rejected');
check(!account_action_token_is_valid($stored, str_repeat('0', 64), 'reset'), 'Forged recovery tokens fail');

$_SERVER['REMOTE_ADDR'] = '192.0.2.1';
$_SERVER['HTTP_CF_CONNECTING_IP'] = '198.51.100.1';
$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.2';
check(getIP() === '192.0.2.1', 'Untrusted forwarded headers cannot spoof the client address');

$config['login_rate_limit_path'] = sys_get_temp_dir() . '/registration-test-limits-' . bin2hex(random_bytes(8));
for ($attempt = 0; $attempt < 20; $attempt++) {
    check(portal_login_rate_limit('testuser'), 'The configured login-attempt allowance works');
}
$_SESSION = [];
$_SERVER['HTTP_CF_CONNECTING_IP'] = '203.0.113.2';
check(!portal_login_rate_limit('testuser'), 'New sessions and forged proxy headers cannot reset the limiter');
foreach (glob($config['login_rate_limit_path'] . '/*') as $file) {
    unlink($file);
}
rmdir($config['login_rate_limit_path']);

// RFC 6238 SHA-1 test vector at Unix time 59, reduced to six digits.
$totpSecret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
check(portal_totp_is_valid($totpSecret, '287082', 59), 'RFC 6238 authenticator vector matches');
check(!portal_totp_is_valid($totpSecret, '287083', 59), 'Incorrect authenticator code fails');
check(!portal_totp_is_valid($totpSecret, '', 59), 'Enabled 2FA cannot be bypassed by omitting the code');
check(!portal_totp_is_valid("\x00encrypted", '287082', 59), 'Unrecognized encrypted 2FA secrets fail closed');
check(portal_totp_is_valid('', '', 59), 'Accounts without 2FA can log in');

$salt = str_repeat("\x01", 32);
$verifier = calculateSRP6Verifier('TESTUSER', 'TestPassword1', $salt);
check(verifySRP6('TESTUSER', 'TestPassword1', $salt, $verifier), 'Game-compatible SRP6 authentication works');
check(!verifySRP6('TESTUSER', 'wrong', $salt, $verifier), 'Wrong SRP6 passwords fail');

database::$auth = Doctrine\DBAL\DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
database::$auth->executeStatement('CREATE TABLE account (id INTEGER PRIMARY KEY, username TEXT, email TEXT, salt BLOB, verifier BLOB, sha_pass_hash TEXT, restore_key TEXT, totp_secret TEXT)');
database::$auth->insert('account', ['id' => 1, 'username' => 'TESTUSER', 'email' => 'user@example.invalid', 'salt' => $salt, 'verifier' => $verifier, 'restore_key' => $stored]);
$_POST = [];
check(user::authenticate_normal_portal('TESTUSER', 'TestPassword1')['id'] === 1, 'Portal accepts valid game credentials');
check(user::authenticate_normal_portal('TESTUSER', 'wrong') === false, 'Portal rejects wrong game credentials');
database::$auth->update('account', ['totp_secret' => $totpSecret], ['id' => 1]);
check(user::authenticate_normal_portal('TESTUSER', 'TestPassword1') === false, 'Portal enforces game-account 2FA');
database::$auth->update('account', ['totp_secret' => null], ['id' => 1]);

$account = user::get_user_by_id(1);
$consume = new ReflectionMethod(user::class, 'consume_account_action_token');
check(!$consume->invoke(null, $account, $token, '2fa'), 'Purpose confusion cannot consume recovery');
check($consume->invoke(null, $account, $token, 'reset'), 'A valid recovery token is consumed atomically');
check(!$consume->invoke(null, $account, $token, 'reset'), 'Concurrent/replayed recovery cannot consume the token again');

$_SESSION['portal_account_id'] = 1;
$_SESSION['portal_credentials'] = user::portal_credential_fingerprint($account);
$_SESSION['portal_login_time'] = time();
check(store::get_portal_account()['id'] === 1, 'A fresh portal session is accepted');
database::$auth->update('account', ['verifier' => str_repeat('x', 32)], ['id' => 1]);
check(store::get_portal_account() === false && !store::is_portal_logged_in(), 'Changing credentials revokes existing portal sessions');

$resolve = new ReflectionMethod(store::class, 'resolve_target_character');
try {
    $resolve->invoke(null, ['scope' => 'character'], [['guid' => 10, 'name' => 'Owned']], 20);
    throw new RuntimeException('Checkout accepted another account character');
} catch (store_user_exception $expected) {
    $checks++;
}

$config['allow_signup'] = false;
$validateInvite = new ReflectionMethod(user::class, 'validate_registration_invite_code');
check(!$validateInvite->invoke(null), 'Closed registration rejects direct submissions');
$config['allow_signup'] = true;
$config['invite_code'] = 'test-invite';
$_POST['invite_code'] = 'wrong';
check(!$validateInvite->invoke(null), 'Registration rejects a wrong invite');
$_POST['invite_code'] = 'test-invite';
check($validateInvite->invoke(null), 'Shared invite behavior is preserved');

$_POST = ['g-recaptcha-response' => ['invalid']];
check(!validate_recaptcha(), 'CAPTCHA rejects array-shaped responses before making network requests');
$config['soap_for_register'] = true;
check(!soap_account_password_is_valid("password\ncommand"), 'SOAP password fields cannot insert control characters');
check(soap_account_password_is_valid('Valid$Password'), 'SOAP accepts a valid password without altering it');

// New dependency majors still satisfy the APIs used by the application.
$captcha = new Gregwar\Captcha\CaptchaBuilder();
$captcha->build();
check(str_starts_with($captcha->inline(), 'data:image/'), 'CAPTCHA renders with the updated package');
$mail = new PHPMailer\PHPMailer\PHPMailer(true);
$mail->setFrom('sender@example.invalid');
$mail->addAddress('receiver@example.invalid');
$mail->Subject = 'Test';
$mail->Body = 'Test';
check($mail->preSend(), 'Updated PHPMailer builds mail without sending it');
check(is_string((new SebastianBergmann\Timer\ResourceUsageFormatter())->resourceUsageSinceStartOfRequest()), 'Updated timer supports existing footers');
check((new voku\helper\AntiXSS())->xss_clean('<script>alert(1)</script>') === '', 'Updated sanitizer removes script tags');

echo $checks . " security and compatibility checks passed on PHP " . PHP_VERSION . ".\n";
