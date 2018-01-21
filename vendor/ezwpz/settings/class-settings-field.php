<?php
namespace EZWPZ_Settings;

if (!\class_exists('EZWPZ_Settings\Field')) {
  class Field {
    /**
     * Slug of settings page where field is shown.
     * @var string
     */
    protected $page;

    /**
     * Slug of settings section where field is shown.
     * @var string
     */
    protected $section;

    /**
     * Slug to identify field.
     * @var string
     */
    protected $id;

    /**
     * Field title.
     * @var string
     */
    protected $title = '';

    /**
     * Extra arguments used when outputting the field.
     * @var array
     */
    protected $args = [];

    /**
     * Field constructor.
     * @param string $page
     * @param string $section
     * @param string $id
     * @param array $args
     */
    public function __construct($page, $section, $id, $args = []) {
      $args = \apply_filters("ezwpz_settings_field-{$page}", $args);
      $args = \apply_filters("ezwpz_settings_field-{$page}-{$section}", $args);
      $args = \apply_filters("ezwpz_settings_field-{$page}-{$section}-{$id}", $args);
      if (isset($args['title'])) {
        $this->title = $args['title'];
        unset($args['title']);
      }
      $args['ezwpz'] = ['field' => $id, 'section' => $section, 'page' => $page];

      $this->page = $page;
      $this->section = $section;
      $this->id = $id;
      $this->args = $args;

      \add_action('admin_init', [$this, 'add_field']);
    }

    /**
     * Add field to section.
     */
    public function add_field() {
      \add_settings_field($this->id, $this->title, [$this, 'render_controls'], $this->page, $this->section, $this->args);
    }

    /**
     * Callback to render the controls if there are any.
     * @param $args
     */
    public function render_controls($args) {
      if (isset($args['ezwpz_controls']) && \is_array($args['ezwpz_controls'])) {
        \ob_start();
        foreach ($args['ezwpz_controls'] as $control) {
          if (is_callable($control))
            \call_user_func($control);
        }
        $field_contents = \ob_get_clean();

        if (\count($args['ezwpz_controls']) > 1) {
          ?>
          <fieldset>
            <legend class="screen-reader-text"><?php echo $this->title; ?></legend>
            <?php echo $field_contents; ?>
          </fieldset>
          <?php
        } else {
          echo $field_contents;
        }
      }
    }
  }
}
