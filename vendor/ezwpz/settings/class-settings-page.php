<?php
namespace EZWPZ_Settings;

use EZWPZ\Page as EZWPZ_Page;

if (!\class_exists('EZWPZ_Settings\Page')) {
  class Page extends EZWPZ_Page {
    /**
     * @inheritdoc
     */
    public function __construct($id, $args = []) {
      $args = \apply_filters("ezwpz_settings_page-{$id}", $args);
      parent::__construct($id, $args);
    }

    /**
     * @inheritdoc
     */
    public function render() {
      $page = $this->id;
      ?>
      <div class="wrap">
        <h1><?php echo \esc_html(\get_admin_page_title()); ?></h1>
        <form method="post" action="/wp-admin/options.php">
          <?php
          \settings_fields($page);
          \do_settings_sections($page);
          \submit_button();
          ?>
        </form>
      </div>
      <?php
    }
  }
}
