<section id="faq" class="faq-section">
    <div class="container faq-layout">
        <div><span class="eyebrow">Before you set off</span><h2>A few things<br>worth knowing.</h2><a href="#contact" class="text-link">Still need a hand? <span aria-hidden="true">↗</span></a></div>
        <div class="realm-accordions">
            <?php if ($registrationRequiresInvite) { ?><details><summary><?php echo $escape(lang_or('faq_invite_question', 'Why do I need an invite code?')); ?></summary><p><?php echo $escape(lang_or('faq_invite_answer', 'Grim Guzzler uses shared invite codes so account creation stays controlled while the realm is being tuned.')); ?></p></details><?php } ?>
            <details><summary><?php echo $escape(lang_or('faq_connect_question', 'Which client should I use?')); ?></summary><p>Use the Wrath of the Lich King <?php echo $escape(get_config('game_version')); ?> client. Set your realmlist to <code><?php echo $escape(get_config('realmlist')); ?></code>, launch Wow.exe, and log in with your <?php echo get_config('battlenet_support') ? 'account email address' : 'game account username'; ?>.</p></details>
            <?php if (store::is_enabled()) { ?>
            <details><summary><?php echo $escape(lang_or('faq_tokens_question', 'Where are Grim Tokens stored?')); ?></summary><p><?php echo $escape(lang_or('faq_tokens_answer', 'Grim Tokens are physical items on your characters. The portal totals every stack across the account.')); ?></p></details>
            <details><summary><?php echo $escape(lang_or('faq_store_question', 'Why is checkout disabled?')); ?></summary><p><?php echo $escape(lang_or('faq_store_answer', 'All characters on the account must be offline, and the account must have enough Grim Tokens for the selected service.')); ?></p></details>
            <?php } ?>
        </div>
    </div>
</section>
