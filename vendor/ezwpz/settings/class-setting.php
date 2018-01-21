<?php
namespace EZWPZ_Settings;

if (!class_exists('EZWPZ_Settings\Setting')) {
  class Setting {
    /**
     * Whitelisted option key name.
     * @var string
     */
    protected $page;

    /**
     * Option id.
     * @var string
     */
    protected $id;

    /**
     * Data to describe the setting when registered.
     * @var array
     */
    protected $args;

    /**
     * EZWPZ_Setting constructor.
     * @param string $page
     * @param string $id
     * @param array $args
     */
    public function __construct($page, $id, $args = []) {
      if (isset($args['sanitize_callback']))
        unset($args['sanitize_callback']);

      if (isset($args['default']))
        unset($args['default']);

      $this->id = $id;
      $this->page = $page;
      $this->args = $args;

      \add_action('admin_init', [$this, 'register_setting']);
    }

    /**
     * Register setting.
     */
    public function register_setting() {
      \register_setting($this->page, $this->id, $this->args);
    }
  }
}
