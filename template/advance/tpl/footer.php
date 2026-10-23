</main>
<footer id="footer" class="site-footer">
    <div class="container footer-main">
        <a href="<?php echo $escape(gg_url()); ?>" class="brand"><img class="brand-logo" src="<?php echo $escape($themeBase); ?>/assets/img/grimguzzler/logo.webp" alt="Grim Guzzler" width="1324" height="1188"><span class="brand-name">Good company.<small>EPIC ADVENTURES.</small></span></a>
        <nav aria-label="Footer navigation"><a href="<?php echo $escape(gg_url('play')); ?>">How to play</a><a href="<?php echo $escape(gg_url('status')); ?>">Realm status</a><a href="<?php echo $escape(gg_url('support')); ?>">Rules & FAQ</a><?php if (store::is_enabled()) { ?><a href="<?php echo $escape(gg_url('account')); ?>">Player account</a><?php } ?></nav>
        <?php if (!empty(get_config('supported_langs'))) { ?><button class="btn language-toggle" type="button" data-toggle="modal" data-target="#lang-modal"><?php elang('change_lang_head'); ?></button><?php } ?>
    </div>
    <div class="container footer-bottom"><p>© <?php echo date('Y'); ?> Grim Guzzler <span>·</span> A home in Azeroth.</p><p>A community-run realm. World of Warcraft is a trademark of Blizzard Entertainment.</p></div>
</footer>
<?php if (!empty(get_config('supported_langs'))) { ?>
<div class="modal" id="lang-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="lang-title">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h3 class="modal-title" id="lang-title"><?php elang('change_lang_head'); ?></h3><button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button></div>
        <div class="modal-body"><form action="" method="post"><?php echo csrf_field(); ?><div class="form-group"><label for="langchange"><?php elang('change_lang_form_head'); ?></label><select class="form-control" id="langchange" name="langchange"><?php foreach (get_config('supported_langs') as $value => $label) { ?><option value="<?php echo $escape($value); ?>"><?php echo $escape($label); ?></option><?php } ?></select></div><input name="langchangever" type="hidden" value="langchanger"><button type="submit" class="btn btn-primary"><?php elang('change_lang_sub'); ?></button></form></div>
    </div></div>
</div>
<?php } ?>
<script src="<?php echo $escape($themeBase); ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?php echo $escape($themeBase); ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $escape($themeBase); ?>/assets/js/main.js?v=20261023-2"></script>
</body>
</html>
