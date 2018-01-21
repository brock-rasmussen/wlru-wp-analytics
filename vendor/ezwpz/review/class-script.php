<?php
namespace EZWPZ\Admin;

class Script {
  /**
   * Unique name of script.
   * @var string
   */
  protected $handle;

  /**
   * Full URL or path relative to the WordPress root directory.
   * @var string
   */
  protected $src = '';

  /**
   * Array of registered script handles this script depends on.
   * @var array
   */
  protected $deps = [];

  /**
   * Script version number.
   * @var string|bool|null
   */
  protected $ver = false;

  /**
   * Whether to enqueue the script before </body> instead of in the <head>.
   * @var bool
   */
  protected $in_footer = false;

  /**
   * $hook_suffix of page(s) to enqueue the script on.
   * @var array|string
   */
  protected $page;

  /**
   * Script constructor.
   * @param string $handle
   * @param array $args
   * @param array|string $page
   */
  public function __construct($handle, $args = [], $page = []) {
    if (!\is_array($args)) {
      $this->src = $args;
    } else {
      $keys = \array_keys(\get_object_vars($this));
      foreach ($keys as $key) {
        if (isset($args[$key])) {
          $this->$key = $args[$key];
        }
      }
    }

    $this->handle = $handle;
    $this->page = $page;

    \add_action('admin_enqueue_scripts', [$this, 'enqueue_script']);
  }

  /**
   * If no $page, enqueue script for all admin pages, otherwise enqueue script for specified page(s).
   * @param $hook
   */
  public function enqueue_script($hook) {
    if (empty($this->page) || ((\is_array($this->page) && \in_array($hook, $this->page)) || $hook === $this->page)) {
      \wp_enqueue_script($this->handle, $this->src, $this->deps, $this->ver, $this->in_footer);
    }
  }
}
