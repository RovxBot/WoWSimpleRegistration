<?php
// Email links only display a form. Changes require a CSRF-protected POST.
$reset = !empty($_GET['restore']) && !empty($_GET['key']);
$enable2fa = !empty($_GET['enabletfa']) && !empty($_GET['account']);
if (!$reset && !$enable2fa) {
    return;
}
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$isGrimTheme = get_config('template') === 'advance';
$maxLength = get_config('battlenet_support') && get_config('srp6_support') && get_config('srp6_version') == 2 ? 128 : 16;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $reset ? 'Reset password' : 'Enable two-factor authentication'; ?></title>
<?php if ($isGrimTheme) { ?>
<meta name="theme-color" content="#101318">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?php echo $escape(rtrim((string) get_config('baseurl'), '/')); ?>/template/advance/assets/css/style.css?v=20261023-2" rel="stylesheet">
<?php } ?>
</head>
<body class="account-confirmation<?php echo $isGrimTheme ? ' gg-confirmation' : ''; ?>">
<main class="account-confirmation-panel">
    <?php if ($isGrimTheme) { ?><a href="<?php echo $escape(get_config('baseurl')); ?>" class="confirmation-brand"><img src="<?php echo $escape(rtrim((string) get_config('baseurl'), '/')); ?>/template/advance/assets/img/grimguzzler/logo.webp" alt="Grim Guzzler" width="1324" height="1188"></a><?php } ?>
    <h1><?php echo $reset ? 'Reset password' : 'Enable two-factor authentication'; ?></h1>
    <?php error_msg(); success_msg(); ?>
    <form method="post" action="">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="submit" value="<?php echo $reset ? 'confirm_restore' : 'confirm_2fa'; ?>">
        <input type="hidden" name="account" value="<?php echo $escape($reset ? $_GET['restore'] : $_GET['account']); ?>">
        <input type="hidden" name="key" value="<?php echo $escape($reset ? $_GET['key'] : $_GET['enabletfa']); ?>">
        <?php if ($reset) { ?>
        <p><label>New password <input type="password" name="password" autocomplete="new-password" minlength="4" maxlength="<?php echo $maxLength; ?>" required></label></p>
        <p><label>Confirm password <input type="password" name="repassword" autocomplete="new-password" minlength="4" maxlength="<?php echo $maxLength; ?>" required></label></p>
        <?php } else { ?>
        <p>Confirm to enable two-factor authentication for your account. Your setup key will be sent to your registered email address.</p>
        <?php } ?>
        <button type="submit" class="btn btn-primary">Confirm</button>
    </form>
    <p><a href="<?php echo $escape(get_config('baseurl')); ?>">Return to the website</a></p>
</main>
</body>
</html>
<?php exit; ?>
