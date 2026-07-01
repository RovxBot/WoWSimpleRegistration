<?php
/**
 * Created by Amin.MasterkinG
 * Website : MasterkinG32.CoM
 * Email : lichwow_masterking@yahoo.com
 * Date: 04/02/2020 - 6:55 PM
 */

require_once 'header.php';
require_once 'server-info.php';
require_once 'how-connect.php';
require_once 'rules.php';
?>
<?php
$pageAction = !empty($_POST['submit']) ? (string) $_POST['submit'] : '';
$showRegistrationMessages = !store::is_portal_action($pageAction);
$signupAllowed = user::is_signup_allowed();
$registrationRequiresInvite = user::registration_requires_invite();
$passwordMaxLength = (get_config('battlenet_support') && get_config('srp6_support') && get_config('srp6_version') == 2) ? 128 : 16;
$showOnlinePlayers = !get_config('disable_online_players');
$showTopPlayers = !get_config('disable_top_players');
$showServerData = $showOnlinePlayers || $showTopPlayers;
?>
<section id="register" class="services">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2><?php elang('register'); ?></h2>
            <p><?php elang('create_new_game_account'); ?></p>
        </div>
        <div class="row">
            <div class="col-lg-6 order-2 order-lg-1">
                <div style="padding: 10px;" data-aos="fade-right" data-aos-delay="100">
                    <?php if ($showRegistrationMessages) {
                        error_msg();
                        success_msg();
                    } ?>
                    <?php if ($signupAllowed) { ?>
                    <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php#register" method="post">
                        <div class="input-group">
                            <span class="input-group"><?php elang('email'); ?></span>
                            <input type="email" class="form-control" required placeholder="<?php elang('email'); ?>" name="email">
                        </div>
                        <?php if (!get_config('battlenet_support')) { ?>
                        <div class="input-group">
                            <span class="input-group"><?php elang('username'); ?></span>
                            <input type="text" class="form-control" pattern="[A-Za-z0-9_-]{2,16}" required placeholder="<?php elang('username'); ?>" name="username">
                        </div>
                        <?php } ?>
                        <div class="input-group">
                            <span class="input-group"><?php elang('password'); ?></span>
                            <input type="password" class="form-control" minlength="4" maxlength="<?php echo $antiXss->xss_clean((string) $passwordMaxLength); ?>" required placeholder="<?php elang('password'); ?>" name="password">
                        </div>
                        <div class="input-group">
                            <span class="input-group"><?php elang('retype_password'); ?></span>
                            <input type="password" class="form-control" minlength="4" maxlength="<?php echo $antiXss->xss_clean((string) $passwordMaxLength); ?>" required placeholder="<?php elang('retype_password'); ?>" name="repassword">
                        </div>
                        <?php if ($registrationRequiresInvite) { ?>
                        <div class="input-group">
                            <span class="input-group"><?php echo $antiXss->xss_clean(lang_or('invite_code', 'Invite Code')); ?></span>
                            <input type="text" class="form-control" required placeholder="<?php echo $antiXss->xss_clean(lang_or('invite_code', 'Invite Code')); ?>" name="invite_code">
                        </div>
                        <?php } ?>
                        <?php echo GetCaptchaHTML(); ?>
                        <input name="submit" type="hidden" value="register">
                        <div class="text-center" style="margin-top: 10px;">
                            <input type="submit" class="btn btn-success" value="<?php elang('register'); ?>">
                        </div>
                    </form>
                    <?php } else { ?>
                    <div class="portal-panel registration-closed-panel">
                        <h3><?php echo $antiXss->xss_clean(lang_or('registration_closed_title', 'Registration Closed')); ?></h3>
                        <p><?php echo $antiXss->xss_clean(lang_or('registration_closed', 'Registration is currently closed.')); ?></p>
                    </div>
                    <?php } ?>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <?php if (empty(get_config('disable_changepassword'))) { ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#changepassword-modal">
                        <?php elang('change_password'); ?>
                    </button>
                    <?php } ?>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#restorepassword-modal">
                        <?php elang('restore_password'); ?>
                    </button>
                </div>
                <?php if (get_config('2fa_support')) { ?>
                <div class="text-center" data-aos="fade-up" data-aos-delay="100" style="margin-top: 5px;">
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#e2fa-modal">
                        <?php elang('two_factor_authentication'); ?>
                    </button>
                </div>
                <div class="modal" id="e2fa-modal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title"><?php elang('two_factor_authentication'); ?></h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php#register" method="post">
                                    <div>
                                        <ul>
                                            <li><?php elang('two_factor_authentication_tip1'); ?> <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Google Store</a> - <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank">Apple Store</a></li>
                                        </ul>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('email'); ?></span>
                                        <input type="email" class="form-control" placeholder="<?php elang('email'); ?>" name="email">
                                    </div>
                                    <?php if (empty(get_config('battlenet_support'))) { ?>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('username'); ?></span>
                                        <input type="text" class="form-control" placeholder="<?php elang('username'); ?>" name="username">
                                    </div>
                                    <?php } ?>
                                    <?php echo GetCaptchaHTML(); ?>
                                    <input name="submit" type="hidden" value="etfa">
                                    <div class="text-center" style="margin-top: 10px;"><input type="submit" class="btn btn-primary" value="<?php elang('two_factor_authentication_enable'); ?>"></div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><?php elang('close'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }
                if (get_config('vote_system')) { ?>
                <div class="text-center" data-aos="fade-up" data-aos-delay="100" style="margin-top: 5px;">
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#vote-modal">
                        <?php elang('vote_for_us'); ?>
                    </button>
                </div>
                <div class="modal" id="vote-modal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title"><?php elang('vote'); ?></h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php#register" method="post" target="_blank">
                                    <?php if (get_config('battlenet_support')) { ?>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('email'); ?></span>
                                        <input type="email" class="form-control" placeholder="<?php elang('email'); ?>" name="account">
                                    </div>
                                    <?php } else { ?>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('username'); ?></span>
                                        <input type="text" class="form-control" placeholder="<?php elang('username'); ?>" name="account">
                                    </div>
                                    <?php } ?>
                                    <div class="text-center" style="margin-top: 10px;">
                                        <?php
                                        $vote_sites = get_config('vote_sites');
                                        if (!empty($vote_sites)) {
                                            foreach ($vote_sites as $siteID => $vote_site) {
                                                $tmp_id = $siteID + 1;
                                                echo '<button type="submit" name="siteid" value="' . $tmp_id . '" style="border:none; background-color: transparent;"><img src="' . $vote_site['image'] . '"></button>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><?php elang('close'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <div class="modal" id="restorepassword-modal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title"><?php elang('restore_password'); ?></h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php#register" method="post">
                                    <?php if (get_config('battlenet_support')) { ?>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('email'); ?></span>
                                        <input type="email" class="form-control" placeholder="<?php elang('email'); ?>" name="email">
                                    </div>
                                    <?php } else { ?>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('username'); ?></span>
                                        <input type="text" class="form-control" placeholder="<?php elang('username'); ?>" name="username">
                                    </div>
                                    <?php } ?>
                                    <?php echo GetCaptchaHTML(); ?>
                                    <input name="submit" type="hidden" value="restorepassword">
                                    <div class="text-center" style="margin-top: 10px;"><input type="submit" class="btn btn-primary" value="<?php elang('restore_password'); ?>"></div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><?php elang('close'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="changepassword-modal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title"><?php elang('change_password'); ?></h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php#register" method="post">
                                    <?php if (get_config('battlenet_support')) { ?>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('email'); ?></span>
                                        <input type="email" class="form-control" placeholder="<?php elang('email'); ?>" name="email">
                                    </div>
                                    <?php } else { ?>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('username'); ?></span>
                                        <input type="text" class="form-control" placeholder="<?php elang('username'); ?>" name="username">
                                    </div>
                                    <?php } ?>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('old_password'); ?></span>
                                        <input type="password" class="form-control" placeholder="<?php elang('old_password'); ?>" name="old_password">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('password'); ?></span>
                                        <input type="password" class="form-control" placeholder="<?php elang('password'); ?>" name="password">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group"><?php elang('retype_password'); ?></span>
                                        <input type="password" class="form-control" placeholder="<?php elang('retype_password'); ?>" name="repassword">
                                    </div>
                                    <?php echo GetCaptchaHTML(); ?>
                                    <input name="submit" type="hidden" value="changepass">
                                    <div class="text-center" style="margin-top: 10px;"><input type="submit" class="btn btn-primary" value="<?php elang('change_password'); ?>"></div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><?php elang('close'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="image col-lg-6 order-1 order-lg-2" style='background-image: url("<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/img/demonhunter.png");background-size: auto 100%;background-position: center;background-repeat: no-repeat;' data-aos="fade-left" data-aos-delay="100"></div>
        </div>
    </div>
</section>
<?php if (store::is_enabled()) {
    $portalAccount = store::get_portal_account();
    $portalLoggedIn = is_array($portalAccount) && !empty($portalAccount['id']);
    $tokenSummary = $portalLoggedIn ? store::get_account_token_summary($portalAccount) : array(
        'total' => 0,
        'has_online_character' => false,
        'characters' => array(),
    );
    $catalog = store::get_catalog();
    $recentOrders = $portalLoggedIn ? store::get_recent_orders($portalAccount, 8) : array();
?>
<section id="player-portal" class="services section-bg">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2><?php echo $antiXss->xss_clean(lang_or('player_portal', 'Player Portal')); ?></h2>
            <p><?php echo $antiXss->xss_clean(lang_or('store_intro_text', 'Log in with your game account to access the Grim Token shop.')); ?></p>
        </div>
        <div class="row">
            <div class="col-lg-5" data-aos="fade-right" data-aos-delay="100">
                <div class="portal-panel">
                    <h3><?php echo $antiXss->xss_clean(lang_or('player_login', 'Player Login')); ?></h3>
                    <?php if (store::is_portal_action($pageAction)) {
                        error_msg();
                        success_msg();
                    } ?>
                    <?php if (!$portalLoggedIn) { ?>
                    <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php#player-portal" method="post">
                        <?php if (get_config('battlenet_support')) { ?>
                        <div class="input-group">
                            <span class="input-group"><?php elang('email'); ?></span>
                            <input type="email" class="form-control" required placeholder="<?php elang('email'); ?>" name="email">
                        </div>
                        <?php } else { ?>
                        <div class="input-group">
                            <span class="input-group"><?php elang('username'); ?></span>
                            <input type="text" class="form-control" required placeholder="<?php elang('username'); ?>" name="username">
                        </div>
                        <?php } ?>
                        <div class="input-group">
                            <span class="input-group"><?php elang('password'); ?></span>
                            <input type="password" class="form-control" required placeholder="<?php elang('password'); ?>" name="password">
                        </div>
                        <input name="submit" type="hidden" value="<?php echo $antiXss->xss_clean(store::ACTION_LOGIN); ?>">
                        <div class="text-center" style="margin-top: 10px;">
                            <input type="submit" class="btn btn-primary" value="<?php echo $antiXss->xss_clean(lang_or('player_login', 'Player Login')); ?>">
                        </div>
                    </form>
                    <p class="portal-note" style="margin-top: 20px;"><?php echo $antiXss->xss_clean(lang_or('store_login_required', 'Log in with your player account to view your store balance and catalog.')); ?></p>
                    <?php } else { ?>
                    <div class="portal-account-meta">
                        <p><strong><?php echo $antiXss->xss_clean(lang_or('account', 'Account')); ?>:</strong> <?php echo $antiXss->xss_clean($portalAccount['username']); ?></p>
                        <p><strong><?php echo $antiXss->xss_clean(lang_or('email', 'Email')); ?>:</strong> <?php echo $antiXss->xss_clean(strtolower($portalAccount['email'])); ?></p>
                        <p><strong><?php echo $antiXss->xss_clean(lang_or('grim_token_total', 'Grim Token Total')); ?>:</strong> <?php echo $antiXss->xss_clean((string) $tokenSummary['total']); ?></p>
                    </div>
                    <div class="portal-requirement">
                        <?php echo $antiXss->xss_clean(lang_or('store_characters_must_be_offline', 'All characters on this account must be offline before using the Grim Token shop.')); ?>
                    </div>
                    <div class="portal-character-list">
                        <?php foreach ($tokenSummary['characters'] as $character) { ?>
                        <div class="portal-character-row">
                            <div>
                                <strong><?php echo $antiXss->xss_clean($character['name']); ?></strong>
                                <span><?php echo $antiXss->xss_clean(lang_or('level', 'Level')); ?> <?php echo $antiXss->xss_clean((string) $character['level']); ?></span>
                            </div>
                            <div class="portal-character-meta">
                                <span class="portal-character-badge<?php echo !empty($character['online']) ? ' is-online' : ''; ?>">
                                    <?php echo $antiXss->xss_clean(!empty($character['online']) ? lang_or('online', 'Online') : lang_or('offline', 'Offline')); ?>
                                </span>
                                <span class="portal-character-tokens"><?php echo $antiXss->xss_clean((string) $character['grim_tokens']); ?> <?php echo $antiXss->xss_clean(lang_or('grim_tokens', 'Grim Tokens')); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php#player-portal" method="post">
                        <input name="submit" type="hidden" value="<?php echo $antiXss->xss_clean(store::ACTION_LOGOUT); ?>">
                        <div class="text-center" style="margin-top: 18px;">
                            <input type="submit" class="btn btn-outline-secondary" value="<?php echo $antiXss->xss_clean(lang_or('logout', 'Logout')); ?>">
                        </div>
                    </form>
                    <?php } ?>
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left" data-aos-delay="100">
                <div class="portal-panel">
                    <h3><?php echo $antiXss->xss_clean(lang_or('grim_token_shop', 'Grim Token Shop')); ?></h3>
                    <?php if (!$portalLoggedIn) { ?>
                    <p class="portal-note"><?php echo $antiXss->xss_clean(lang_or('store_login_required', 'Log in with your player account to view your store balance and catalog.')); ?></p>
                    <?php } else { ?>
                    <div class="store-balance-card">
                        <span class="store-balance-label"><?php echo $antiXss->xss_clean(lang_or('grim_token_total', 'Grim Token Total')); ?></span>
                        <strong class="store-balance-value"><?php echo $antiXss->xss_clean((string) $tokenSummary['total']); ?></strong>
                    </div>
                    <?php if (!empty($tokenSummary['has_online_character'])) { ?>
                    <div class="portal-warning">
                        <?php echo $antiXss->xss_clean(lang_or('store_online_blocked', 'Checkout is blocked while any character on this account is online.')); ?>
                    </div>
                    <?php } ?>
                    <?php if (empty($catalog)) { ?>
                    <p class="portal-note"><?php echo $antiXss->xss_clean(lang_or('store_no_services', 'No Grim Token services are configured right now.')); ?></p>
                    <?php } else { ?>
                    <div class="row store-grid">
                        <?php foreach ($catalog as $service) {
                            $serviceDisabled = !empty($tokenSummary['has_online_character']) || $tokenSummary['total'] < $service['price'];
                            if ($service['scope'] === 'character' && empty($tokenSummary['characters'])) {
                                $serviceDisabled = true;
                            }
                        ?>
                        <div class="col-md-6">
                            <div class="store-item-card">
                                <div class="store-item-price"><?php echo $antiXss->xss_clean((string) $service['price']); ?> <?php echo $antiXss->xss_clean(lang_or('grim_tokens', 'Grim Tokens')); ?></div>
                                <h4><?php echo $antiXss->xss_clean($service['title']); ?></h4>
                                <?php if ($service['description'] !== '') { ?>
                                <p><?php echo $antiXss->xss_clean($service['description']); ?></p>
                                <?php } ?>
                                <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php#player-portal" method="post">
                                    <?php if ($service['scope'] === 'character') { ?>
                                    <div class="input-group store-form-group">
                                        <span class="input-group"><?php echo $antiXss->xss_clean(lang_or('character', 'Character')); ?></span>
                                        <select class="form-control" name="target_character_guid" required>
                                            <option value=""><?php echo $antiXss->xss_clean(lang_or('choose_character', 'Choose a character')); ?></option>
                                            <?php foreach ($tokenSummary['characters'] as $character) { ?>
                                            <option value="<?php echo $antiXss->xss_clean((string) $character['guid']); ?>">
                                                <?php echo $antiXss->xss_clean($character['name'] . ' - ' . lang_or('level', 'Level') . ' ' . $character['level']); ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <?php } ?>
                                    <input name="submit" type="hidden" value="<?php echo $antiXss->xss_clean(store::ACTION_CHECKOUT); ?>">
                                    <input name="service_id" type="hidden" value="<?php echo $antiXss->xss_clean($service['id']); ?>">
                                    <button type="submit" class="btn btn-success btn-block" <?php echo $serviceDisabled ? 'disabled' : ''; ?>>
                                        <?php echo $antiXss->xss_clean(lang_or('purchase_service', 'Purchase Service')); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                    <?php if (!empty($recentOrders)) { ?>
                    <div class="store-orders">
                        <h4><?php echo $antiXss->xss_clean(lang_or('recent_orders', 'Recent Orders')); ?></h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo $antiXss->xss_clean(lang_or('service', 'Service')); ?></th>
                                        <th><?php echo $antiXss->xss_clean(lang_or('character', 'Character')); ?></th>
                                        <th><?php echo $antiXss->xss_clean(lang_or('cost', 'Cost')); ?></th>
                                        <th><?php echo $antiXss->xss_clean(lang_or('status', 'Status')); ?></th>
                                        <th><?php echo $antiXss->xss_clean(lang_or('date', 'Date')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order) { ?>
                                    <tr>
                                        <td><?php echo $antiXss->xss_clean($order['service_title']); ?></td>
                                        <td><?php echo $antiXss->xss_clean($order['target_character_name'] ?: lang_or('store_account_scope', 'Account')); ?></td>
                                        <td><?php echo $antiXss->xss_clean((string) $order['token_cost']); ?></td>
                                        <td><span class="store-status-badge <?php echo $antiXss->xss_clean(store::get_order_status_badge_class($order['status'])); ?>"><?php echo $antiXss->xss_clean(store::get_order_status_label($order['status'])); ?></span></td>
                                        <td><?php echo $antiXss->xss_clean((string) $order['created_at']); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php } ?>
<?php if ($showServerData) { ?>
<section id="server-status" class="contact section-bg">
    <div class="container">
        <div class="section-title" data-aos="fade-up" data-aos-delay="100">
            <h2><?php elang('server_status'); ?></h2>
            <p><?php echo $antiXss->xss_clean(lang_or('live_server_data', 'Live Realm Data')); ?></p>
        </div>
        <?php if ($showOnlinePlayers) { ?>
        <div class="row" data-aos="fade-up" data-aos-delay="100">
            <div class="col-lg-12 text-center" style="margin-top: -30px;">
                <?php
                    foreach (get_config('realmlists') as $onerealm_key => $onerealm) {
                        echo "<p><span style='color: #005cbf;font-weight: bold;'>{$onerealm['realmname']}</span> <span style='font-size: 12px;'>(" . lang('online_players_msg1') . " " . user::get_online_players_count($onerealm['realmid']) . ")</span></p><hr>";
                        $online_chars = user::get_online_players($onerealm['realmid']);
                        if (!is_array($online_chars)) {
                            echo "<span style='color: #0d99e5;'>" . lang('online_players_msg2') . "</span>";
                        } else {
                            echo '<table class="table table-striped"><thead><tr><th scope="col">' . lang('name') . '</th><th scope="col">' . lang('race') . '</th> <th scope="col">' . lang('class') . '</th><th scope="col">' . lang('level') . '</th></tr></thead><tbody>';
                            foreach ($online_chars as $one_char) {
                                echo '<tr><th scope="row">' . $antiXss->xss_clean($one_char['name']) . '</th><td><img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/race/' . $antiXss->xss_clean($one_char["race"]) . '-' . $antiXss->xss_clean($one_char["gender"]) . '.gif\'></td><td><img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/class/' . $antiXss->xss_clean($one_char["class"]) . '.gif\'></td><td>' . $antiXss->xss_clean($one_char['level']) . '</td></tr>';
                            }
                            echo '</table>';
                        }
                        echo "<hr>";
                    }
                ?>
            </div>
        </div>
        <?php } ?>
        <?php if ($showTopPlayers) { ?>
        <div class="section-title" data-aos="fade-up" data-aos-delay="100">
            <h2><?php elang('top_players'); ?></h2>
        </div>
        <div class="row">
            <div class="col-lg-12 text-center" style="margin-top: -30px;">
                <?php
                    $i = 1;
                    foreach (get_config('realmlists') as $onerealm_key => $onerealm) {
                        echo "<h6 style='color: #005cbf;font-weight: bold;'>{$onerealm['realmname']}</h6><hr>";
                        $data2show = status::get_top_playtime($onerealm['realmid']);
                        echo "<button type=\"button\" class=\"btn btn-info\" data-toggle=\"modal\"  data-aos=\"fade-up\" data-aos-delay=\"100\"data-target=\"#modal-id$i\">" . lang('play_time') . "</button><div class=\"modal\" id=\"modal-id$i\"><div class=\"modal-dialog modal-lg\"><div class=\"modal-content\">
                                            <div class=\"modal-header\"><h4 class=\"modal-title\">" . lang('top_players') . " - " . lang('play_time') . "</h4><button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button></div><div class=\"modal-body\">";

                        if (!is_array($data2show)) {
                            echo "<span style='color: #0d99e5;'>" . lang('online_players_msg2') . "</span>";
                        } else {
                            echo '<table class="table table-striped table-responsive-sm"><thead><tr><th scope="col">' . lang('rank') . '</th><th scope="col">' . lang('name') . '</th><th scope="col">' . lang('race') . '</th> <th scope="col">' . lang('class') . '</th><th scope="col">' . lang('level') . '</th><th scope="col">' . lang('play_time') . '</th></tr></thead><tbody>';
                            $m = 1;
                            foreach ($data2show as $one_char) {
                                if (empty($one_char['name'])) {
                                    continue;
                                }
                                echo '<tr><td>' . $m++ . '<th scope="row">' . $antiXss->xss_clean($one_char['name']) . '</th><td><img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/race/' . $antiXss->xss_clean($one_char["race"]) . '-' . $antiXss->xss_clean($one_char["gender"]) . '.gif\'></td><td><img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/class/' . $antiXss->xss_clean($one_char["class"]) . '.gif\'></td><td>' . $antiXss->xss_clean($one_char['level']) . '</td><td>' . $antiXss->xss_clean(get_human_time_from_sec($one_char['totaltime'])) . '</td></tr>';
                            }
                            echo '</table>';
                        }
                        echo "</div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">Close</button></div></div></div></div>";
                        $i++;

                        $data2show = status::get_top_gold($onerealm['realmid']);
                        echo "<button type=\"button\" class=\"btn btn-info\" data-toggle=\"modal\"  data-aos=\"fade-up\" data-aos-delay=\"100\"data-target=\"#modal-id$i\">" . lang('gold') . "</button><div class=\"modal\" id=\"modal-id$i\"><div class=\"modal-dialog modal-lg\"><div class=\"modal-content\">
                                            <div class=\"modal-header\"><h4 class=\"modal-title\">" . lang('top_players') . " - " . lang('gold') . "</h4><button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button></div><div class=\"modal-body\">";
                        if (!is_array($data2show)) {
                            echo "<span style='color: #0d99e5;'>" . lang('online_players_msg2') . "</span>";
                        } else {
                            echo '<table class="table table-striped table-responsive-sm"><thead><tr><th scope="col">' . lang('rank') . '</th><th scope="col">' . lang('name') . '</th><th scope="col">' . lang('level') . '</th> <th scope="col">' . lang('play_time') . '</th><th scope="col">' . lang('gold') . '</th></tr></thead><tbody>';
                            $m = 1;
                            foreach ($data2show as $one_char) {
                                if (empty($one_char['name'])) {
                                    continue;
                                }
                                echo '<tr><td>' . $m++ . '<th scope="row">' . $antiXss->xss_clean($one_char['name']) . '</th><td>' . $antiXss->xss_clean($one_char["level"]) . '</td><td>' . $antiXss->xss_clean(get_human_time_from_sec($one_char['totaltime'])) . '</td><td>' . $antiXss->xss_clean(substr($one_char["money"], 0, -4)) . '<img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/goldcoin.png\'></td></tr>';
                            }
                            echo '</table>';
                        }
                        echo "</div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">Close</button></div></div></div></div>";
                        $i++;

                        $data2show = status::get_top_killers($onerealm['realmid']);
                        echo "<button type=\"button\" class=\"btn btn-info\" data-toggle=\"modal\"  data-aos=\"fade-up\" data-aos-delay=\"100\"data-target=\"#modal-id$i\">" . lang('killers') . "</button><div class=\"modal\" id=\"modal-id$i\"><div class=\"modal-dialog modal-lg\"><div class=\"modal-content\">
                                            <div class=\"modal-header\"><h4 class=\"modal-title\">" . lang('top_players') . " - " . lang('killers') . "</h4><button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button></div><div class=\"modal-body\">";
                        if (!is_array($data2show)) {
                            echo "<span style='color: #0d99e5;'>" . lang('online_players_msg2') . "</span>";
                        } else {
                            echo '<table class="table table-striped  table-responsive-sm"><thead><tr><th scope="col">' . lang('rank') . '</th><th scope="col">' . lang('name') . '</th><th scope="col">' . lang('race') . '</th> <th scope="col">' . lang('class') . '</th><th scope="col">' . lang('level') . '</th><th scope="col">' . lang('kills') . '</th></tr></thead><tbody>';
                            $m = 1;
                            foreach ($data2show as $one_char) {
                                if (empty($one_char['name'])) {
                                    continue;
                                }
                                echo '<tr><td>' . $m++ . '<th scope="row">' . $antiXss->xss_clean($one_char['name']) . '</th><td><img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/race/' . $antiXss->xss_clean($one_char["race"]) . '-' . $antiXss->xss_clean($one_char["gender"]) . '.gif\'></td><td><img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/class/' . $antiXss->xss_clean($one_char["class"]) . '.gif\'></td><td>' . $antiXss->xss_clean($one_char['level']) . '</td><td>' . $antiXss->xss_clean($one_char['totalKills']) . '</td></tr>';
                            }
                            echo '</table>';
                        }
                        echo "</div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">" . lang('close') . "</button></div></div></div></div>";
                        $i++;

                        $data2show = status::get_top_honorpoints($onerealm['realmid']);
                        echo "<button type=\"button\" class=\"btn btn-info\" data-toggle=\"modal\"  data-aos=\"fade-up\" data-aos-delay=\"100\"data-target=\"#modal-id$i\">" . lang('honor_points') . "</button><div class=\"modal\" id=\"modal-id$i\"><div class=\"modal-dialog modal-lg\"><div class=\"modal-content\">
                                            <div class=\"modal-header\"><h4 class=\"modal-title\">" . lang('top_players') . " - " . lang('honor_points') . "</h4><button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button></div><div class=\"modal-body\">";
                        if (!is_array($data2show)) {
                            echo "<span style='color: #0d99e5;'>" . lang('online_players_msg2') . "</span>";
                        } else {
                            echo '<table class="table table-striped table-responsive-sm"><thead><tr><th scope="col">' . lang('rank') . '</th><th scope="col">' . lang('name') . '</th><th scope="col">' . lang('race') . '</th> <th scope="col">' . lang('class') . '</th><th scope="col">' . lang('level') . '</th>';

                            if (get_config('expansion') >= 6) {
                                echo '<th scope="col">' . lang('honor_level') . '</th>';
                            }

                            echo '<th scope="col">' . lang('honor_points') . '</th></tr></thead><tbody>';
                            $m = 1;
                            foreach ($data2show as $one_char) {
                                if (empty($one_char['name'])) {
                                    continue;
                                }
                                echo '<tr><td>' . $m++ . '<th scope="row">' . $antiXss->xss_clean($one_char['name']) . '</th><td><img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/race/' . $antiXss->xss_clean($one_char["race"]) . '-' . $antiXss->xss_clean($one_char["gender"]) . '.gif\'></td><td><img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/class/' . $antiXss->xss_clean($one_char["class"]) . '.gif\'></td><td>' . $antiXss->xss_clean($one_char['level']) . '</td>';

                                if (get_config('expansion') >= 6) {
                                    echo '<td>' . $antiXss->xss_clean($one_char['honorLevel']) . '</td>';
                                    echo '<td>' . $antiXss->xss_clean($one_char['honor']) . '</td>';
                                } else {
                                    echo '<td>' . $antiXss->xss_clean($one_char['totalHonorPoints']) . '</td>';
                                }

                                echo '</tr>';
                            }
                            echo '</table>';
                        }
                        echo "</div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">" . lang('close') . "</button></div></div></div></div>";
                        $i++;

                        $data2show = status::get_top_arenapoints($onerealm['realmid']);
                        echo "<button type=\"button\" class=\"btn btn-info\" data-toggle=\"modal\"  data-aos=\"fade-up\" data-aos-delay=\"100\"data-target=\"#modal-id$i\">" . lang('arena_points') . "</button><div class=\"modal\" id=\"modal-id$i\"><div class=\"modal-dialog modal-lg\"><div class=\"modal-content\">
                                            <div class=\"modal-header\"><h4 class=\"modal-title\">" . lang('top_players') . " - " . lang('arena_points') . ":</h4><button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button></div><div class=\"modal-body\">";
                        if (!is_array($data2show)) {
                            echo "<span style='color: #0d99e5;'>" . lang('online_players_msg2') . "</span>";
                        } else {
                            echo '<table class="table table-striped table-responsive-sm"><thead><tr><th scope="col">' . lang('rank') . '</th><th scope="col">' . lang('name') . '</th><th scope="col">' . lang('race') . '</th> <th scope="col">' . lang('class') . '</th><th scope="col">' . lang('level') . '</th><th scope="col">' . lang('arena_points') . '</th></tr></thead><tbody>';
                            $m = 1;
                            foreach ($data2show as $one_char) {
                                if (empty($one_char['name'])) {
                                    continue;
                                }
                                echo '<tr><td>' . $m++ . '<th scope="row">' . $antiXss->xss_clean($one_char['name']) . '</th><td><img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/race/' . $antiXss->xss_clean($one_char["race"]) . '-' . $antiXss->xss_clean($one_char["gender"]) . '.gif\'></td><td><img src=\'' . get_config("baseurl") . '/template/' . $antiXss->xss_clean(get_config("template")) . '/images/class/' . $antiXss->xss_clean($one_char["class"]) . '.gif\'></td><td>' . $antiXss->xss_clean($one_char['level']) . '</td><td>' . $antiXss->xss_clean($one_char['arenaPoints']) . '</td></tr>';
                            }
                            echo '</table>';
                        }
                        echo "</div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">" . lang('close') . "</button></div></div></div></div>";
                        $i++;

                        $data2show = status::get_top_arenateams($onerealm['realmid']);
                        echo "<button type=\"button\" class=\"btn btn-info\" data-toggle=\"modal\"  data-aos=\"fade-up\" data-aos-delay=\"100\"data-target=\"#modal-id$i\">" . lang('arena_teams') . "</button><div class=\"modal\" id=\"modal-id$i\"><div class=\"modal-dialog modal-lg\"><div class=\"modal-content\">
                                            <div class=\"modal-header\"><h4 class=\"modal-title\">" . lang('top_players') . " - " . lang('arena_teams') . "</h4><button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button></div><div class=\"modal-body\">";
                        if (!is_array($data2show)) {
                            echo "<span style='color: #0d99e5;'>" . lang('online_players_msg2') . "</span>";
                        } else {
                            echo '<table class="table table-striped table-responsive-sm"><thead><tr><th scope="col">' . lang('rank') . '</th><th scope="col">' . lang('name') . '</th><th scope="col">' . lang('rating') . '</th><th scope="col">' . lang('captain_name') . '</th></tr></thead><tbody>';
                            $m = 1;
                            foreach ($data2show as $one_char) {
                                $character_data = status::get_character_by_guid($onerealm['realmid'], $one_char['captainGuid']);

                                if (empty($character_data['name'])) {
                                    continue;
                                }

                                echo '<tr><td>' . $m++ . '<th scope="row">' . $antiXss->xss_clean($one_char['name']) . '</th><td>' . $antiXss->xss_clean($one_char['rating']) . '</td><td>' . (!empty($character_data["name"]) ? $antiXss->xss_clean($character_data['name']) : '-') . '</td></tr>';
                            }
                            echo '</table>';
                        }
                        echo "</div><div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">" . lang('close') . "</button></div></div></div></div>";
                        $i++;
                        echo "<hr>";
                    }
                ?>
            </div>
        </div>
        <?php } ?>
    </div>
</section>
<?php } ?>
<?php
require_once 'faq.php';
require_once 'contact.php';
require_once 'footer.php';
?>
