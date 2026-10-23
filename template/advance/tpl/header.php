<?php
$themeBase = rtrim((string) get_config('baseurl'), '/') . '/template/' . get_config('template');
$escape = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
$registrationLabel = !$signupAllowed ? 'Registration closed' : ($registrationRequiresInvite ? 'Invite-only registration' : 'Registration open');
$mainLinks = ['home'=>'Home','status'=>'Realm','arena'=>'Arena','raids'=>'Raids','guilds'=>'Guilds','pvp'=>'PvP'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#17110d">
    <title><?php echo $escape($page === 'home' ? get_config('page_title') . ' — A home in Azeroth' : $meta['title'] . ' — ' . get_config('page_title')); ?></title>
    <meta name="description" content="Grim Guzzler: a home in Azeroth. Realm status, arena and PvP rankings, raid victories, guilds, and your Wrath of the Lich King game account.">
    <link href="<?php echo $escape(get_config('baseurl')); ?>/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?php echo $escape($themeBase); ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $escape($themeBase); ?>/assets/css/style.css?v=20261023-2" rel="stylesheet">
    <?php if ($page === 'home') { ?><link rel="preload" as="image" href="<?php echo $escape($themeBase); ?>/assets/img/grimguzzler/tavern.webp"><?php } ?>
    <noscript><style>@media(max-width:991px){body.gg-site .menu-toggle{display:none}body.gg-site .header-inner{flex-wrap:wrap;padding-top:12px}body.gg-site .site-navigation{position:static;width:100%;visibility:visible;opacity:1;transform:none}body.gg-site .site-header{position:static}}</style></noscript>
    <?php echo getCaptchaJS(); ?>
    <?php echo !empty(lang('custom_css')) ? '<style>' . lang('custom_css') . '</style>' : ''; ?>
    <?php echo !empty(lang('tpl_advance_custom_css')) ? '<style>' . lang('tpl_advance_custom_css') . '</style>' : ''; ?>
</head>
<body class="gg-site page-<?php echo $escape($page); ?>">
<a class="skip-link" href="#main">Skip to content</a>
<svg xmlns="http://www.w3.org/2000/svg" class="icon-definitions" aria-hidden="true">
    <symbol id="icon-tankard" viewBox="0 0 64 64"><path d="M16 22h30l-3 31H19l-3-31Zm30 4h5c9 0 9 17-1 17h-5M24 28v18m8-18v18m8-18v18M13 20c-1-5 3-9 8-8 0-9 13-11 16-3 8-3 15 4 11 11H13Z"/><path d="m22 57 18 0M28 6l3-3"/></symbol>
    <symbol id="icon-arrow" viewBox="0 0 24 24"><path d="M4 12h15m-6-6 6 6-6 6"/></symbol>
    <symbol id="icon-sword" viewBox="0 0 24 24"><path d="m4 20 5-5m-5-2 7 7M8 14 18 3h3v3L10 17M3 21l1-1"/></symbol>
    <symbol id="icon-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c6 5 6 13 0 18-6-5-6-13 0-18Z"/></symbol>
    <symbol id="icon-key" viewBox="0 0 24 24"><circle cx="8" cy="8" r="5"/><path d="m12 12 9 9m-4-4 3-3m-6 0 3-3"/></symbol>
    <symbol id="icon-coins" viewBox="0 0 64 64"><ellipse cx="26" cy="18" rx="18" ry="8"/><path d="M8 18v10c0 10 36 10 36 0V18M8 28v10c0 5 10 8 20 8M8 38v10c0 6 15 9 26 7"/><ellipse cx="46" cy="39" rx="12" ry="6"/><path d="M34 39v10c0 8 24 8 24 0V39"/></symbol>
    <symbol id="icon-copy" viewBox="0 0 24 24"><rect x="8" y="8" width="12" height="13" rx="1"/><path d="M16 8V3H3v13h5"/></symbol>
</svg><div class="utility-bar"><div class="container utility-inner"><span>WRATH OF THE LICH KING <b>3.3.5a</b></span><nav aria-label="Account navigation"><a href="<?php echo $escape(gg_url('support')); ?>">Rules & support</a><?php if (store::is_enabled()) { ?><a href="<?php echo $escape(gg_url('account')); ?>">My account <span aria-hidden="true">↗</span></a><?php } ?></nav></div></div>
<header id="header" class="site-header">
    <div class="container header-inner">
        <a href="<?php echo $escape(gg_url()); ?>" class="brand" aria-label="Grim Guzzler home"><img class="brand-logo" src="<?php echo $escape($themeBase); ?>/assets/img/grimguzzler/logo.webp" alt="" width="1324" height="1188"><span class="brand-name">Grim Guzzler<small>A HOME IN AZEROTH</small></span></a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-navigation"><span></span><span></span><span></span><span class="sr-only">Toggle navigation</span></button>
        <nav id="site-navigation" class="site-navigation" aria-label="Main navigation">
            <?php foreach ($mainLinks as $key => $label) { ?><a href="<?php echo $escape(gg_url($key)); ?>" <?php echo $page === $key ? 'aria-current="page"' : ''; ?>><?php echo $escape($label); ?></a><?php } ?>
            <?php if (store::is_enabled()) { ?><a class="mobile-account-link" href="<?php echo $escape(gg_url('account')); ?>">My account</a><?php } ?>
            <a class="mobile-account-link" href="<?php echo $escape(gg_url('support')); ?>">Rules & support</a>
            <a href="<?php echo $escape(gg_url('play')); ?>" class="btn btn-primary nav-cta" <?php echo in_array($page, ['play','register'], true) ? 'aria-current="page"' : ''; ?>>Play now <span aria-hidden="true">↗</span></a>
        </nav>
    </div>
</header>
<main id="main">
<?php if ($page !== 'home') { ?>
<div class="page-banner"><div class="container"><nav aria-label="Breadcrumb"><a href="<?php echo $escape(gg_url()); ?>">Home</a><span aria-hidden="true">/</span><span><?php echo $escape($meta['title']); ?></span></nav><span class="eyebrow"><?php echo $escape($meta['eyebrow']); ?></span><h1><?php echo $escape($meta['title']); ?></h1></div></div>
<?php } ?>
