<?php
/**
 * Created by Amin.MasterkinG
 * Website : MasterkinG32.CoM
 * Email : lichwow_masterking@yahoo.com
 * Date: 04/02/2020 - 6:55 PM
 */
?>
<section id="contact" class="contact section-bg">
        <div class="container">
            <div class="section-title">
                <h2><?php elang('contact'); ?></h2>
                <p><?php echo $antiXss->xss_clean(lang_or('support_summary', 'Need help with access, tokens, or a stuck character? Ask an administrator in game or through the current community support channel.')); ?></p>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="info d-flex flex-column justify-content-center" data-aos="fade-right">
                        <div class="address">
                            <i class="icofont-user"></i>
                            <h4><?php echo $antiXss->xss_clean(lang_or('account_support', 'Account help')); ?>:</h4>
                            <p><?php echo $antiXss->xss_clean(lang_or('account_support_text', 'Use the registration tools here for account creation, password changes, and password recovery. Invite codes are required for new accounts.')); ?></p>
                        </div>
                        <div class="email">
                            <i class="icofont-ticket"></i>
                            <h4><?php echo $antiXss->xss_clean(lang_or('store_support', 'Grim Token shop')); ?>:</h4>
                            <p><?php echo $antiXss->xss_clean(lang_or('store_support_text', 'Log in to the player portal, make sure every character on the account is offline, then choose a service and target character.')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0" data-aos="fade-left" data-aos-delay="100">
                    <div class="info d-flex flex-column justify-content-center">
                        <div class="address">
                            <i class="icofont-game-controller"></i>
                            <h4><?php elang('server_address'); ?>:</h4>
                            <p><code><?php echo $antiXss->xss_clean(strtoupper(get_config('realmlist'))); ?></code></p>
                        </div>
                        <div class="email">
                            <i class="icofont-coins"></i>
                            <h4><?php echo $antiXss->xss_clean(lang_or('grim_tokens', 'Grim Tokens')); ?>:</h4>
                            <p><?php echo $antiXss->xss_clean(lang_or('faq_tokens_answer', 'Grim Tokens are physical items on your characters. The portal totals every stack across the account.')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
