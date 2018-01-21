<?php
/*
Plugin Name: Analytics by Wallaroo Media
Description: Easily add Google Analytics, Facebook Pixel, and Pinterest Analytics to your site.
Author: Brock Rasmussen, Wallaroo Media
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

require_once 'ezwpz/ezwpz-settings.php';

// add page and setting
EZWPZ_Settings::register_setting('wlru_analytics', 'wlru_analytics');
EZWPZ_Settings::add_page('wlru_analytics', [
  'page_title' => 'WLRU Analytics',
  'menu_title' => 'WLRU Analytics',
  'parent_slug' => 'options-general.php'
]);

// google analytics
EZWPZ_Settings::add_section('wlru_analytics', 'wlru_analytics_ga', [
  'title' => 'Google Analytics',
  'description' => 'Enter your Google Analytics Tracking ID below (e.g. UA-00000000-0). Need help? Click <a target="_blank" href="https://support.google.com/analytics/answer/1032385?hl=en">here</a>.'
]);
EZWPZ_Settings::add_field('wlru_analytics', 'wlru_analytics_ga', 'wlru_analytics_ga', [
  'title' => 'Universal Tracking ID',
  'label_for' => 'wlru_analytics_ga',
]);
EZWPZ_Settings::add_control('wlru_analytics', 'wlru_analytics_ga', 'wlru_analytics_ga', 'wlru_analytics_ga', [
  'setting' => 'wlru_analytics',
]);

// google tag manager
EZWPZ_Settings::add_section('wlru_analytics', 'wlru_analytics_gtm', [
  'title' => 'Google Tag Manager',
  'description' => 'Enter your Google Tag Manager container ID (e.g. GTM-0000). Need help? Click <a target="_blank" href="https://developers.google.com/tag-manager/quickstart">here</a>.'
]);
EZWPZ_Settings::add_field('wlru_analytics', 'wlru_analytics_gtm', 'wlru_analytics_gtm', [
  'title' => 'Container ID',
  'label_for' => 'wlru_analytics_gtm',
]);
EZWPZ_Settings::add_control('wlru_analytics', 'wlru_analytics_gtm', 'wlru_analytics_gtm', 'wlru_analytics_gtm', [
  'setting' => 'wlru_analytics'
]);

// facebook pixel
EZWPZ_Settings::add_section('wlru_analytics', 'wlru_analytics_fbp', [
  'title' => 'Facebook Pixel',
  'description' => 'Enter your Facebook Pixel ID number. Need help? Click <a target="_blank" href="https://www.facebook.com/business/help/952192354843755">here</a>.'
]);
EZWPZ_Settings::add_field('wlru_analytics', 'wlru_analytics_fbp', 'wlru_analytics_fbp', [
  'title' => 'Pixel ID Number',
  'label_for' => 'wlru_analytics_fbp',
]);
EZWPZ_Settings::add_control('wlru_analytics', 'wlru_analytics_fbp', 'wlru_analytics_fbp', 'wlru_analytics_fbp', [
  'setting' => 'wlru_analytics'
]);

// pinterest analytics
EZWPZ_Settings::add_section('wlru_analytics', 'wlru_analytics_pa', [
  'title' => 'Pinterest Analytics',
  'description' => 'Enter your Pinterest domain verification code below (e.g. 431a77c83b1a923e74dbe6d4b4ff1bfe). Need help? Click <a target="_blank" href="https://business.pinterest.com/en/confirm-your-website">here</a>.'
]);
EZWPZ_Settings::add_field('wlru_analytics', 'wlru_analytics_pa', 'wlru_analytics_pa', [
  'title' => 'Site Verification Code',
  'label_for' => 'wlru_analytics_pa',
]);
EZWPZ_Settings::add_control('wlru_analytics', 'wlru_analytics_pa', 'wlru_analytics_pa', 'wlru_analytics_pa', [
  'setting' => 'wlru_analytics'
]);

add_action( 'wp_head', 'wlru_analytics_wp_head' );
function wlru_analytics_wp_head() {
$wlru_analytics_ga = get_option( 'wlru_analytics_ga' );
if ( $wlru_analytics_ga ) :?>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
ga('create', '<?php echo $wlru_analytics_ga; ?>', 'auto');
ga('send', 'pageview');
</script>
<?php endif;

$wlru_analytics_gtm = get_option( 'wlru_analytics_gtm' );
if ($wlru_analytics_gtm ) : ?>
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?php echo $wlru_analytics_gtm; ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo $wlru_analytics_gtm; ?>');</script>
<?php endif;

$wlru_analytics_fbp = get_option( 'wlru_analytics_fbp' );
if ( $wlru_analytics_fbp ) :?>
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo $wlru_analytics_fbp; ?>');
fbq('track', "PageView");
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo $wlru_analytics_fbp; ?>&ev=PageView&noscript=1" /></noscript>
<?php endif;

$wlru_analytics_pa = get_option( 'wlru_analytics_pa' );
if ( $wlru_analytics_pa && is_front_page() ) : ?>
<meta name="p:domain_verify" content="<?php echo $wlru_analytics_pa; ?>"/>
<?php endif;
}
