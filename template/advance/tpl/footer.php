<?php
/**
 * Created by Amin.MasterkinG
 * Website : MasterkinG32.CoM
 * Email : lichwow_masterking@yahoo.com
 * Date: 04/02/2020 - 6:55 PM
 */
?>
</main>
<footer id="footer">
    <div class="footer-top">
        <div class="container">
            <div class="credits" data-aos="fade-up" data-aos-delay="100">
                Grim Guzzler - <?php echo $antiXss->xss_clean(get_config('game_version')); ?>
            </div>
        </div>
    </div>
    <div class="container footer-bottom clearfix">
        <div class="credits">
            <?php echo "Load " . (new SebastianBergmann\Timer\ResourceUsageFormatter)->resourceUsageSinceStartOfRequest(); ?>
        </div>
    </div>
</footer>
<a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>
<script src="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/vendor/jquery.easing/jquery.easing.min.js"></script>
<script src="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/vendor/php-email-form/validate.js"></script>
<script src="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/vendor/jquery-sticky/jquery.sticky.js"></script>
<script src="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/vendor/venobox/venobox.min.js"></script>
<script src="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
<script src="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/vendor/owl.carousel/owl.carousel.min.js"></script>
<script src="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/vendor/aos/aos.js"></script>
<script src="<?php echo $antiXss->xss_clean(get_config("baseurl")); ?>/template/<?php echo $antiXss->xss_clean(get_config("template")); ?>/assets/js/main.js"></script>
</body>
</html>
