<?php
namespace EZWPZ;

if (!class_exists('Page')) {
  class Page {
    /**
     * Slug name to refer to this menu by.
     * @var string
     */
    protected $id;

    /**
     * Text to be displayed in the title tags.
     * @var string
     */
    protected $page_title = '';

    /**
     * Text used in the menu.
     * @var string
     */
    protected $menu_title = '';

    /**
     * Capability required for this menu to be displayed to the user.
     * @var string
     */
    protected $capability = 'manage_options';

    /**
     * Menu icon. 1) base64-encoded SVG using a data URI, 2) dashicons helper class name, 3) 'none' to add icon via CSS.
     * @var string
     */
    protected $icon_url = '';

    /**
     * Menu position.
     * @var int|null
     */
    protected $position = null;

    /**
     * Parent menu item slug name.
     * @var string
     */
    protected $parent_slug = '';

    /**
     * Page constructor.
     * @param string $id
     * @param array $args
     */
    public function __construct($id, $args = []) {
      $args = \apply_filters("ezwpz_page-{$id}", $args);

      $keys = \array_keys(\get_object_vars($this));
      foreach ($keys as $key) {
        if (isset($args[$key])) {
          $this->$key = $args[$key];
        }
      }

      $this->id = $id;

      \add_action('admin_menu', [$this, 'add_page']);
    }

    /**
     * Add page to wp menu.
     */
    public function add_page() {
      if (!empty($this->parent_slug)) {
        \add_submenu_page($this->parent_slug, $this->page_title, $this->menu_title, $this->capability, $this->id, [$this, 'render']);
      } else {
        \add_menu_page($this->page_title, $this->menu_title, $this->capability, $this->id, [$this, 'render'], $this->icon_url, $this->position);
      }
    }

    /**
     * Callback function to render the page.
     */
    public function render() {
      die(\__('function EZWPZ\Page::render() must be over-ridden in a sub-class.', 'ezwpz'));
    }
  }
}
