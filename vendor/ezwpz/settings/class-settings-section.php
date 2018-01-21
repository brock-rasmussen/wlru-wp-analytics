<?php
namespace EZWPZ_Settings;

if (!\class_exists('EZWPZ_Settings\Section')) {
  class Section {
    /**
     * Slug of settings page where section is shown.
     * @var string
     */
    protected $page;

    /**
     * Slug to identify section.
     * @var string
     */
    protected $id;

    /**
     * Section heading.
     * @var string
     */
    protected $title = '';

    /**
     * Section description.
     * @var string
     */
    protected $description = '';

    /**
     * Section constructor.
     * @param string $page
     * @param string $id
     * @param array $args
     */
    public function __construct($page, $id, $args = []) {
      $args = \apply_filters("ezwpz_settings_section-{$page}", $args);
      $args = \apply_filters("ezwpz_settings_section-{$page}-{$id}", $args);

      $keys = \array_keys(\get_object_vars($this));
      foreach ($keys as $key) {
        if (isset($args[$key])) {
          $this->$key = $args[$key];
        }
      }

      $this->page = $page;
      $this->id = $id;

      \add_action('admin_init', [$this, 'add_section']);
    }

    /**
     * Add section to page.
     */
    public function add_section() {
      \add_settings_section($this->id, $this->title, [$this, 'render_description'], $this->page);

      if (!empty($this->description) && \is_string($this->description)) {
        global $wp_settings_sections;
        $wp_settings_sections[$this->page][$this->id]['ezwpz_description'] = $this->description;
      }
    }

    /**
     * Callback to render the section description if there is one.
     * @param $section
     */
    public function render_description($section) {
      if (isset($section['ezwpz_description'])) {
        echo \apply_filters('the_content', $section['ezwpz_description']);
      }
    }
  }
}
