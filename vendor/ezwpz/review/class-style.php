<?php
namespace EZWPZ\Admin;

class Style {
  /**
   * Unique name of stylesheet.
   * @var string
   */
  protected $handle;

  /**
   * Full URL or path relative to the WordPress root directory.
   * @var string
   */
  protected $src = '';

  /**
   * Array of registered stylesheet handles this stylesheet depends on.
   * @var array
   */
  protected $deps = [];

  /**
   * Stylesheet version number.
   * @var string|bool|null
   */
  protected $ver = false;

  /**
   * The media for this stylesheet.
   * @var string
   */
  protected $media = 'all';

  /**
   * $hook_suffix of page(s) to enqueue the stylesheet on.
   * @var array|string
   */
  protected $page;

  /**
   * Style constructor.
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
      \wp_enqueue_style($this->handle, $this->src, $this->deps, $this->ver, $this->media);
    }
  }
}
