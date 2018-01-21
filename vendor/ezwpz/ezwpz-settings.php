<?php
// CORE
require_once 'core/class-page.php';

// SETTINGS
require_once 'settings/class-setting.php';
require_once 'settings/class-settings-page.php';
require_once 'settings/class-settings-section.php';
require_once 'settings/class-settings-field.php';
require_once 'settings/class-settings-control.php';

class EZWPZ_Settings {
  /**
   * Easily add menu page.
   * @param string $id
   * @param array $args
   * @return \EZWPZ_Settings\Page
   */
  public static function add_page($id, $args = []) {
    return new \EZWPZ_Settings\Page($id, $args);
  }

  /**
   * Easily add a settings section.
   * @param string $page
   * @param string $id
   * @param array $args
   * @return \EZWPZ_Settings\Section
   */
  public static function add_section($page, $id, $args = []) {
    return new \EZWPZ_Settings\Section($page, $id, $args);
  }

  /**
   * Easily add a settings field.
   * @param string $page
   * @param string $section
   * @param string $id
   * @param array $args
   * @return \EZWPZ_Settings\Field
   */
  public static function add_field($page, $section, $id, $args = []) {
    return new \EZWPZ_Settings\Field($page, $section, $id, $args);
  }

  /**
   * Easily add a control to the field.
   * @param string $page
   * @param string $section
   * @param string $field
   * @param string $id
   * @param array $args
   * @return \EZWPZ_Settings\Control
   */
  public static function add_control($page, $section, $field, $id, $args = []) {
    return new \EZWPZ_Settings\Control($page, $section, $field, $id, $args);
  }

  /**
   * Easily add a setting.
   * @param string $page
   * @param string $id
   * @param array $args
   * @return \EZWPZ_Settings\Setting
   */
  public static function register_setting($page, $id, $args = []) {
    return new \EZWPZ_Settings\Setting($page, $id, $args);
  }
}
