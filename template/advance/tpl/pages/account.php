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
<section id="player-portal" class="portal-section section-bg">
    <div class="container">
        <div class="section-title">
            <span class="eyebrow">A little character goes a long way</span>
            <h2><?php echo $antiXss->xss_clean(lang_or('player_portal', 'Player Portal')); ?></h2>
            <p><?php echo $portalLoggedIn ? 'Your characters, Grim Tokens, and character services — all in one place.' : $antiXss->xss_clean(lang_or('store_intro_text', 'Log in with your game account to access the Grim Token shop.')); ?></p>
        </div>
        <div class="row">
            <div class="col-lg-5">
                <div class="portal-panel">
                    <h3><?php echo $antiXss->xss_clean($portalLoggedIn ? lang_or('account', 'Your account') : lang_or('player_login', 'Player Login')); ?></h3>
                    <?php if (store::is_portal_action($pageAction)) {
                        error_msg();
                        success_msg();
                    } ?>
                    <?php if (!$portalLoggedIn) { ?>
                    <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php?page=account#player-portal" method="post">
                        <?php echo csrf_field(); ?>
                        <?php if (get_config('battlenet_support')) { ?>
                        <div class="input-group">
                            <label for="account-field-17"><?php elang('email'); ?></label>
                            <input id="account-field-17" type="email" class="form-control" required placeholder="<?php elang('email'); ?>" name="email" autocomplete="username">
                        </div>
                        <?php } else { ?>
                        <div class="input-group">
                            <label for="account-field-18"><?php elang('username'); ?></label>
                            <input id="account-field-18" type="text" class="form-control" required placeholder="<?php elang('username'); ?>" name="username" autocomplete="username">
                        </div>
                        <?php } ?>
                        <div class="input-group">
                            <label for="account-field-19"><?php elang('password'); ?></label>
                            <input id="account-field-19" type="password" class="form-control" required placeholder="<?php elang('password'); ?>" name="password" autocomplete="current-password">
                        </div>
                        <div class="input-group">
                            <label for="account-field-20">Authenticator code (if enabled)</label>
                            <input id="account-field-20" type="text" class="form-control" name="totp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code">
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
                    <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php?page=account#player-portal" method="post">
                        <?php echo csrf_field(); ?>
                        <input name="submit" type="hidden" value="<?php echo $antiXss->xss_clean(store::ACTION_LOGOUT); ?>">
                        <div class="text-center" style="margin-top: 18px;">
                            <input type="submit" class="btn btn-outline-secondary" value="<?php echo $antiXss->xss_clean(lang_or('logout', 'Logout')); ?>">
                        </div>
                    </form>
                    <?php } ?>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="portal-panel">
                    <h3><?php echo $antiXss->xss_clean(lang_or('grim_token_shop', 'Grim Token Shop')); ?></h3>
                    <?php if (!$portalLoggedIn) { ?>
                    <p class="portal-note"><?php echo $antiXss->xss_clean(lang_or('store_login_required', 'Log in with your player account to view your store balance and catalog.')); ?></p>
                    <div class="shop-intro">
                        <svg class="shop-emblem" aria-hidden="true"><use href="#icon-coins"/></svg>
                        <h4>A new look. The same legend.</h4>
                        <p>Spend the Grim Tokens in your characters’ bags on character services. Your account’s combined balance, available services, and order history appear here after you sign in.</p>
                        <a href="<?php echo $escape(gg_url('support')); ?>#faq" class="text-link">How Grim Tokens work <span aria-hidden="true">↗</span></a>
                    </div>
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
                                <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php?page=account#player-portal" method="post">
                                    <?php echo csrf_field(); ?>
                                    <?php if ($service['scope'] === 'character') { ?>
                                    <div class="input-group store-form-group">
                                        <label for="service-character-<?php echo $escape($service['id']); ?>"><?php echo $antiXss->xss_clean(lang_or('character', 'Character')); ?></label>
                                        <select id="service-character-<?php echo $escape($service['id']); ?>" class="form-control" name="target_character_guid" required>
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

<?php if (!store::is_enabled()) { ?>
<div class="container page-content"><div class="empty-state"><h2>The player portal is currently unavailable.</h2><p>You can still use the account creation and password tools.</p><a class="btn btn-primary" href="<?php echo $escape(gg_url('register')); ?>">Account tools</a></div></div>
<?php } ?>
