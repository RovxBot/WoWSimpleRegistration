<?php
/**
 * Created by Amin.MasterkinG
 * Website : MasterkinG32.CoM
 * Email : lichwow_masterking@yahoo.com
 * Date: 04/02/2020 - 6:55 PM
 */
?>
<section id="faq" class="faq">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2><?php elang('frequently_questions'); ?></h2>
        </div>
        <ul class="faq-list">
            <li data-aos="fade-up">
                <a data-toggle="collapse" class="collapsed" href="#faq1"><?php echo $antiXss->xss_clean(lang_or('faq_invite_question', 'Why do I need an invite code?')); ?> <i
                            class="bx bx-chevron-down icon-show"></i><i class="bx bx-x icon-close"></i></a>
                <div id="faq1" class="collapse" data-parent=".faq-list">
                    <p>
                        <?php echo $antiXss->xss_clean(lang_or('faq_invite_answer', 'Grim Guzzler uses shared invite codes so account creation stays controlled while the realm is being tuned.')); ?>
                    </p>
                </div>
            </li>
            <li data-aos="fade-up" data-aos-delay="100">
                <a data-toggle="collapse" href="#faq2" class="collapsed"><?php echo $antiXss->xss_clean(lang_or('faq_tokens_question', 'Where are Grim Tokens stored?')); ?> <i
                            class="bx bx-chevron-down icon-show"></i><i
                            class="bx bx-x icon-close"></i></a>
                <div id="faq2" class="collapse" data-parent=".faq-list">
                    <p>
                        <?php echo $antiXss->xss_clean(lang_or('faq_tokens_answer', 'Grim Tokens are physical items on your characters. The portal totals every stack across the account.')); ?>
                    </p>
                </div>
            </li>
            <li data-aos="fade-up" data-aos-delay="200">
                <a data-toggle="collapse" href="#faq3" class="collapsed"><?php echo $antiXss->xss_clean(lang_or('faq_store_question', 'Why is checkout disabled?')); ?> <i
                            class="bx bx-chevron-down icon-show"></i><i
                            class="bx bx-x icon-close"></i></a>
                <div id="faq3" class="collapse" data-parent=".faq-list">
                    <p>
                        <?php echo $antiXss->xss_clean(lang_or('faq_store_answer', 'All characters on the account must be offline, and the account must have enough Grim Tokens for the selected service.')); ?>
                    </p>
                </div>
            </li>
            <li data-aos="fade-up" data-aos-delay="300">
                <a data-toggle="collapse" href="#faq4" class="collapsed"><?php echo $antiXss->xss_clean(lang_or('faq_connect_question', 'Which client should I use?')); ?> <i
                            class="bx bx-chevron-down icon-show"></i><i
                            class="bx bx-x icon-close"></i></a>
                <div id="faq4" class="collapse" data-parent=".faq-list">
                    <p>
                        <?php echo $antiXss->xss_clean(lang_or('faq_connect_answer', 'Use the supported Wrath client and set your realmlist to the Grim Guzzler host.')); ?>
                    </p>
                </div>
            </li>
        </ul>
    </div>
</section>
