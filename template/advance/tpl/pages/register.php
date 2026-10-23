<section id="register" class="registration-section">
    <div class="container">
        <div class="registration-layout">
            <div class="registration-story">
                <span class="eyebrow">Your next chapter</span>
                <h2>There’s a place<br>for you here.</h2>
                <p>Old friends. New characters. One more adventure.<br>Make Grim Guzzler your home in Azeroth.</p>
                <img class="registration-logo" src="<?php echo $escape($themeBase); ?>/assets/img/grimguzzler/logo.webp" alt="Grim Guzzler" width="1324" height="1188">
                <p class="registration-hint"><?php echo !$signupAllowed ? 'Account creation is currently closed. Existing players can still access their accounts.' : ($registrationRequiresInvite ? 'An invite is your seat at the table. Have your code ready when you create your account.' : 'Your adventure starts with a free game account.'); ?></p>
                <a href="<?php echo $escape(gg_url('support')); ?>#why-us" class="text-link">Read the realm rules <span aria-hidden="true">↗</span></a>
            </div>
            <div class="registration-form-panel">
                <div class="panel-heading"><span class="eyebrow">Join the realm</span><h3><?php elang('create_account'); ?></h3></div>
                <div class="registration-form-body">
                    <?php if ($showRegistrationMessages) {
                        error_msg();
                        success_msg();
                    } ?>
                    <?php if ($signupAllowed) { ?>
                    <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php?page=register#register" method="post" class="registration-form">
                        <?php echo csrf_field(); ?>
                        <div class="input-group">
                            <label for="account-field-1"><?php elang('email'); ?></label>
                            <input id="account-field-1" type="email" class="form-control" required placeholder="<?php elang('email'); ?>" name="email" autocomplete="email">
                        </div>
                        <?php if (!get_config('battlenet_support')) { ?>
                        <div class="input-group">
                            <label for="account-field-2"><?php elang('username'); ?></label>
                            <input id="account-field-2" type="text" class="form-control" pattern="[A-Za-z0-9_-]{2,16}" required placeholder="<?php elang('username'); ?>" name="username" autocomplete="username">
                        </div>
                        <?php } ?>
                        <div class="input-group">
                            <label for="account-field-3"><?php elang('password'); ?></label>
                            <input id="account-field-3" type="password" class="form-control" minlength="4" maxlength="<?php echo $antiXss->xss_clean((string) $passwordMaxLength); ?>" required placeholder="<?php elang('password'); ?>" name="password" autocomplete="new-password">
                        </div>
                        <div class="input-group">
                            <label for="account-field-4"><?php elang('retype_password'); ?></label>
                            <input id="account-field-4" type="password" class="form-control" minlength="4" maxlength="<?php echo $antiXss->xss_clean((string) $passwordMaxLength); ?>" required placeholder="<?php elang('retype_password'); ?>" name="repassword" autocomplete="new-password">
                        </div>
                        <?php if ($registrationRequiresInvite) { ?>
                        <div class="input-group field-wide">
                            <label for="account-field-5"><?php echo $antiXss->xss_clean(lang_or('invite_code', 'Invite Code')); ?></label>
                            <input id="account-field-5" type="text" class="form-control" required placeholder="<?php echo $antiXss->xss_clean(lang_or('invite_code', 'Invite Code')); ?>" name="invite_code">
                        </div>
                        <?php } ?>
                        <div class="captcha-container"><?php echo GetCaptchaHTML(); ?></div>
                        <input name="submit" type="hidden" value="register">
                        <div class="text-center" style="margin-top: 10px;">
                            <button type="submit" class="btn btn-primary btn-block">Create your account <span aria-hidden="true">→</span></button>
                            <p class="form-footnote">By joining, you agree to the <a href="<?php echo $escape(gg_url('support')); ?>#why-us">realm rules</a>.</p>
                        </div>
                    </form>
                    <?php } else { ?>
                    <div class="portal-panel registration-closed-panel">
                        <h3><?php echo $antiXss->xss_clean(lang_or('registration_closed_title', 'Registration Closed')); ?></h3>
                        <p><?php echo $antiXss->xss_clean(lang_or('registration_closed', 'Registration is currently closed.')); ?></p>
                    </div>
                    <?php } ?>
                </div>
                <div class="account-tools">
                    <span class="account-tools-title">Already an adventurer?</span>
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
                <div class="text-center" style="margin-top: 5px;">
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#e2fa-modal">
                        <?php elang('two_factor_authentication'); ?>
                    </button>
                </div>
                <div class="modal" id="e2fa-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="e2fa-modal-title">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="e2fa-modal-title"><?php elang('two_factor_authentication'); ?></h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php?page=register#register" method="post">
                                    <?php echo csrf_field(); ?>
                                    <div>
                                        <ul>
                                            <li><?php elang('two_factor_authentication_tip1'); ?> <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Google Store</a> - <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank">Apple Store</a></li>
                                        </ul>
                                    </div>
                                    <div class="input-group">
                                        <label for="account-field-6"><?php elang('email'); ?></label>
                                        <input id="account-field-6" type="email" class="form-control" placeholder="<?php elang('email'); ?>" name="email">
                                    </div>
                                    <?php if (empty(get_config('battlenet_support'))) { ?>
                                    <div class="input-group">
                                        <label for="account-field-7"><?php elang('username'); ?></label>
                                        <input id="account-field-7" type="text" class="form-control" placeholder="<?php elang('username'); ?>" name="username">
                                    </div>
                                    <?php } ?>
                                    <div class="captcha-container"><?php echo GetCaptchaHTML(); ?></div>
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
                <div class="text-center" style="margin-top: 5px;">
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#vote-modal">
                        <?php elang('vote_for_us'); ?>
                    </button>
                </div>
                <div class="modal" id="vote-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="vote-modal-title">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="vote-modal-title"><?php elang('vote'); ?></h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php?page=register#register" method="post" target="_blank">
                                    <?php echo csrf_field(); ?>
                                    <?php if (get_config('battlenet_support')) { ?>
                                    <div class="input-group">
                                        <label for="account-field-8"><?php elang('email'); ?></label>
                                        <input id="account-field-8" type="email" class="form-control" placeholder="<?php elang('email'); ?>" name="account">
                                    </div>
                                    <?php } else { ?>
                                    <div class="input-group">
                                        <label for="account-field-9"><?php elang('username'); ?></label>
                                        <input id="account-field-9" type="text" class="form-control" placeholder="<?php elang('username'); ?>" name="account">
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
                <div class="modal" id="restorepassword-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="restorepassword-modal-title">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="restorepassword-modal-title"><?php elang('restore_password'); ?></h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php?page=register#register" method="post">
                                    <?php echo csrf_field(); ?>
                                    <?php if (get_config('battlenet_support')) { ?>
                                    <div class="input-group">
                                        <label for="account-field-10"><?php elang('email'); ?></label>
                                        <input id="account-field-10" type="email" class="form-control" placeholder="<?php elang('email'); ?>" name="email">
                                    </div>
                                    <?php } else { ?>
                                    <div class="input-group">
                                        <label for="account-field-11"><?php elang('username'); ?></label>
                                        <input id="account-field-11" type="text" class="form-control" placeholder="<?php elang('username'); ?>" name="username">
                                    </div>
                                    <?php } ?>
                                    <div class="captcha-container"><?php echo GetCaptchaHTML(); ?></div>
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
                <div class="modal" id="changepassword-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="changepassword-modal-title">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="changepassword-modal-title"><?php elang('change_password'); ?></h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form action="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/index.php?page=register#register" method="post">
                                    <?php echo csrf_field(); ?>
                                    <?php if (get_config('battlenet_support')) { ?>
                                    <div class="input-group">
                                        <label for="account-field-12"><?php elang('email'); ?></label>
                                        <input id="account-field-12" type="email" class="form-control" placeholder="<?php elang('email'); ?>" name="email">
                                    </div>
                                    <?php } else { ?>
                                    <div class="input-group">
                                        <label for="account-field-13"><?php elang('username'); ?></label>
                                        <input id="account-field-13" type="text" class="form-control" placeholder="<?php elang('username'); ?>" name="username">
                                    </div>
                                    <?php } ?>
                                    <div class="input-group">
                                        <label for="account-field-14"><?php elang('old_password'); ?></label>
                                        <input id="account-field-14" type="password" class="form-control" placeholder="<?php elang('old_password'); ?>" name="old_password">
                                    </div>
                                    <div class="input-group">
                                        <label for="account-field-15"><?php elang('password'); ?></label>
                                        <input id="account-field-15" type="password" class="form-control" placeholder="<?php elang('password'); ?>" name="password">
                                    </div>
                                    <div class="input-group">
                                        <label for="account-field-16"><?php elang('retype_password'); ?></label>
                                        <input id="account-field-16" type="password" class="form-control" placeholder="<?php elang('retype_password'); ?>" name="repassword">
                                    </div>
                                    <div class="captcha-container"><?php echo GetCaptchaHTML(); ?></div>
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
        </div>
    </div>
</section>
