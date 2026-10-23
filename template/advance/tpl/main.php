<?php
require_once __DIR__ . '/site.php';

$pageAction = is_string($_POST['submit'] ?? null) ? $_POST['submit'] : '';
$showRegistrationMessages = !store::is_portal_action($pageAction);
$signupAllowed = user::is_signup_allowed();
$registrationRequiresInvite = user::registration_requires_invite();
$passwordMaxLength = get_config('battlenet_support') && get_config('srp6_support') && get_config('srp6_version') == 2 ? 128 : 16;
$showOnlinePlayers = !get_config('disable_online_players');
$showTopPlayers = !get_config('disable_top_players');
$showServerData = $showOnlinePlayers || $showTopPlayers;
$pages = [
    'home' => ['title'=>'Welcome to the tavern', 'eyebrow'=>'A home in Azeroth'],
    'status' => ['title'=>'Realm status', 'eyebrow'=>'Azeroth, right now'],
    'arena' => ['title'=>'Arena rankings', 'eyebrow'=>'Steel yourself. Climb the ladder.'],
    'raids' => ['title'=>'Raid clears & boss kills', 'eyebrow'=>'Legends are made together'],
    'guilds' => ['title'=>'Guild rankings', 'eyebrow'=>'Find your people. Make your mark.'],
    'pvp' => ['title'=>'PvP leaderboards', 'eyebrow'=>'For the Alliance. For the Horde.'],
    'play' => ['title'=>'Your adventure starts here', 'eyebrow'=>'Three steps to Azeroth'],
    'register' => ['title'=>'Join Grim Guzzler', 'eyebrow'=>'There’s a seat at the table'],
    'account' => ['title'=>'Player account', 'eyebrow'=>'Your characters. Your next chapter.'],
    'support' => ['title'=>'The tavern handbook', 'eyebrow'=>'Rules, answers & a helping hand'],
];
$page = is_string($_GET['page'] ?? null) ? $_GET['page'] : 'home';
if (store::is_portal_action($pageAction)) {
    $page = 'account';
} elseif (in_array($pageAction, ['register','etfa','changepass','restorepassword'], true)) {
    $page = 'register';
}
if (!isset($pages[$page])) {
    http_response_code(404);
    $page = 'not-found';
}
$realms = realm_stats::realms();
$realmId = (int) ($_GET['realm'] ?? array_key_first($realms) ?? 0);
if ($realmId && !isset($realms[$realmId])) {
    http_response_code(404);
    $page = 'not-found';
}
$selectedRealm = $realms[$realmId] ?? null;
$realmSummary = $selectedRealm && in_array($page, ['home','status'], true) ? realm_stats::summary($realmId) : ['available'=>false];
$meta = $pages[$page] ?? ['title'=>'Page not found', 'eyebrow'=>'A wrong turn in Azeroth'];
require __DIR__ . '/header.php';
if ($page === 'home') {
    require __DIR__ . '/pages/home.php';
} elseif ($page === 'not-found') {
?>
<div class="container page-content"><div class="empty-state"><h2>That path leads nowhere.</h2><p>The page or realm you’re looking for could not be found.</p><a class="btn btn-primary" href="<?php echo $escape(gg_url()); ?>">Back to the tavern</a></div></div>
<?php
} else {
    require __DIR__ . '/pages/' . $page . '.php';
}
require __DIR__ . '/footer.php';
