<?php
/*
Plugin Name: Analytics by Wallaroo Media
Description: Easily add Google Analytics, Facebook Pixel, and Pinterest Analytics to your site.
Author: Brock Rasmussen, Wallaroo Media
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
add_action( 'admin_menu', 'wlru_analytics_admin_menu' );
function wlru_analytics_admin_menu() {
  add_options_page(
    'Analytics by Wallaroo Media',
    'Analytics',
    'manage_options',
    'wlru-analytics',
    'wlru_analytics_page'
  );
}

function wlru_analytics_page() { ?>
  <div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form method="post" action="options.php">
      <?php
      settings_fields( 'wlru_analytics_page' );
      do_settings_sections( 'wlru-analytics' );
      submit_button();
      ?>
    </form>
  </div>
<?php }

add_action( 'admin_init', 'wlru_analytics_settings' );
function wlru_analytics_settings() {
  add_settings_section(
    'wlru_analytics_ga_section',
    'Google Analytics',
    'wlru_analytics_ga_section',
    'wlru-analytics'
  );
  add_settings_field(
    'wlru_analytics_ga',
    'Universal Tracking ID',
    'wlru_analytics_ga',
    'wlru-analytics',
    'wlru_analytics_ga_section'
  );
  register_setting( 'wlru_analytics_page', 'wlru_analytics_ga' );

  add_settings_section(
    'wlru_analytics_gtm_section',
    'Google Tag Manager',
    'wlru_analytics_gtm_section',
    'wlru-analytics'
  );
  add_settings_field(
    'wlru_analytics_gtm',
    'Container ID',
    'wlru_analytics_gtm',
    'wlru-analytics',
    'wlru_analytics_gtm_section'
  );
  register_setting( 'wlru_analytics_page', 'wlru_analytics_gtm' );

  add_settings_section(
    'wlru_analytics_fbp_section',
    'Facebook Pixel',
    'wlru_analytics_fbp_section',
    'wlru-analytics'
  );
  add_settings_field(
    'wlru_analytics_fbp',
    'Pixel ID Number',
    'wlru_analytics_fbp',
    'wlru-analytics',
    'wlru_analytics_fbp_section'
  );
  register_setting( 'wlru_analytics_page', 'wlru_analytics_fbp' );

  add_settings_section(
    'wlru_analytics_pa_section',
    'Pinterest Analytics',
    'wlru_analytics_pa_section',
    'wlru-analytics'
  );
  add_settings_field(
    'wlru_analytics_pa',
    'Site Verification Code',
    'wlru_analytics_pa',
    'wlru-analytics',
    'wlru_analytics_pa_section'
  );
  register_setting( 'wlru_analytics_page', 'wlru_analytics_pa' );
}

function wlru_analytics_ga_section() {
  echo '<p>Enter your Google Analytics Tracking ID below (e.g. "UA-00000000-0"). Need help? Click <a href="https://support.google.com/analytics/answer/1032385?hl=en">here</a>.</p>';
}
function wlru_analytics_ga() {
  $setting = esc_attr( get_option( 'wlru_analytics_ga' ) );
  echo "<input type='text' name='wlru_analytics_ga' pattern='(UA|YT|MO)-\d+-\d+' value='$setting'>";
}

function wlru_analytics_gtm_section() {
  echo '<p>Enter your Google Tag Manager container ID (e.g. "GTM-0000"). Need help? Click <a href="https://developers.google.com/tag-manager/quickstart">here</a>.</p>';
}
function wlru_analytics_gtm() {
  $setting = esc_attr( get_option( 'wlru_analytics_gtm' ) );
  echo "<input type='text' name='wlru_analytics_gtm' pattern='(GTM)-.{4,}' value='$setting'>";
}

function wlru_analytics_fbp_section() {
  echo '<p>Enter your Facebook Pixel ID number. Need help? Click <a href="https://www.facebook.com/business/help/952192354843755">here</a>.</p>';
}
function wlru_analytics_fbp() {
  $setting = esc_attr( get_option( 'wlru_analytics_fbp' ) );
  echo "<input type='number' name='wlru_analytics_fbp' pattern='[0-9]' value='$setting'>";
}

function wlru_analytics_pa_section() {
  echo '<p>Enter your Pinterest domain verification code below (e.g. "431a77c83b1a923e74dbe6d4b4ff1bfe"). Need help? Click <a href="https://business.pinterest.com/en/confirm-your-website">here</a>.</p>';
}
function wlru_analytics_pa() {
  $setting = esc_attr( get_option( 'wlru_analytics_pa' ) );
  echo "<input type='text' name='wlru_analytics_pa' value='$setting'>";
}

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
