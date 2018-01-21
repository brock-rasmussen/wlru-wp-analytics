<?php
namespace EZWPZ_Settings;

if (!\class_exists('EZWPZ_Settings\Control')) {
  class Control {
    /**
     * Slug of settings page where control is shown.
     * @var string
     */
    public $page;

    /**
     * Slug of settings section where control is shown.
     * @var string
     */
    public $section;

    /**
     * Slug of settings field where control is shown.
     * @var string
     */
    public $field;

    /**
     * Slug to identify control.
     * @var string
     */
    public $id;

    /**
     * Slug of setting the control is attached to.
     * @var string
     */
    public $setting;

    /**
     * Control type.
     * @var string
     */
    public $type = 'text';

    /**
     * Control Label.
     * @var string
     */
    public $label = '';

    /**
     * Control description.
     * @var mixed
     */
    public $description = '';

    /**
     * Default value.
     * @var string
     */
    public $default = '';

    /**
     * Array of choices ($value => $label).
     * @var array
     */
    public $choices = [];

    /**
     * Additional input attributes ($attribute => $value).
     * @var array
     */
    public $input_attrs = [];

    /**
     * Callback to sanitize data.
     * @var string
     */
    public $sanitize_callback;

    /**
     * Control constructor.
     * @param string $page
     * @param string $section
     * @param string $field
     * @param string $id
     * @param array $args
     */
    public function __construct($page, $section, $field, $id, $args) {
      if (!isset($args['type']))
        $args['type'] = 'text';

      if ($args['type'] === 'textarea') {
        $args['input_attrs']['class'][] = 'large-text';
        $args['input_attrs']['rows'] = 10;
        $args['input_attrs']['cols'] = 50;
      }

      if (in_array($args['type'], ['email', 'text', 'url']))
        $args['input_attrs']['class'][] = 'regular-text';

      if ($args['type'] === 'color')
        $args['input_attrs']['class'][] = 'color-picker';

      if (!empty($this->choices) && in_array($args['type'], ['email', 'number', 'tel', 'text', 'url']))
        $args['input_attrs']['list'] = "{$this->id}-list";

      $args = \apply_filters("ezwpz_settings_control-{$page}", $args);
      $args = \apply_filters("ezwpz_settings_control-{$page}-{$section}", $args);
      $args = \apply_filters("ezwpz_settings_control-{$page}-{$section}-{$field}", $args);
      $args = \apply_filters("ezwpz_settings_control-{$page}-{$section}-{$field}-{$id}", $args);
      $keys = \array_keys(\get_object_vars($this));
      foreach ($keys as $key) {
        if (isset($args[$key])) {
          $this->$key = $args[$key];
        }
      }

      $this->page = $page;
      $this->section = $section;
      $this->field = $field;
      $this->id = $id;

      if (!isset($this->sanitize_callback)) {
        switch ($this->type) {
          case 'color':
            $this->sanitize_callback = 'sanitize_hex_color';
            break;
          case 'email':
            $this->sanitize_callback = 'sanitize_email';
            break;
          case 'editor':
            $this->sanitize_callback = 'wp_filter_post_kses';
            break;
          case 'number':
            $this->sanitize_callback = 'intval';
            break;
          case 'textarea':
            $this->sanitize_callback = 'sanitize_textarea_field';
            break;
          case 'url':
            $this->sanitize_callback = 'esc_url_raw';
            break;
          default:
            $this->sanitize_callback = 'sanitize_text_field';
            break;
        }
      }

      if (isset($this->setting) && isset($this->sanitize_callback))
        \add_filter("sanitize_option_{$this->setting}", [$this, 'sanitize']);

      if (isset($this->setting) && !empty($this->default))
        \add_filter("default_option_{$this->setting}", [$this, 'set_default']);

      \add_action('admin_init', [$this, 'add_control'], 10);
    }

    /**
     * Add control to the field.
     */
    public function add_control() {
      global $wp_registered_settings, $wp_settings_fields;
      $wp_registered_settings[$this->setting]['ezwpz_controls'][] = $this->id;
      $wp_settings_fields[$this->page][$this->section][$this->field]['args']['ezwpz_controls'][$this->id] = [$this, 'render'];
    }

    /**
     * If only one control is attached to the $setting, set default to $default.
     * If more than one control is attached to the $setting, add $id => $default pair to array.
     * @param $default
     * @return string|array
     */
    public function set_default($default) {
      if ($this->is_single())
        $default = $this->default;
      else
        $default[$this->id] = $this->default;

      return $default;
    }

    /**
     * Sanitize the submitted data.
     * @param $data
     * @return string|array
     */
    public function sanitize($data) {
      if ($this->is_single())
        $data = \call_user_func($this->sanitize_callback, $data);
      else
        $data[$this->id] = \call_user_func($this->sanitize_callback, $data[$this->id]);

      return $data;
    }

    /**
     * Render the control.
     */
    public function render() {
      $name = $this->is_single() ? $this->setting : "{$this->setting}[{$this->id}]";
      $type = $this->type === 'color' ? 'text' : $this->type;

      if (!empty($this->description))
        $this->input_attrs['aria-describedby'] = "{$this->id}-description";

      switch ($this->type) {
        case 'checkbox':
          ?>
          <label for="<?php echo esc_attr($this->id); ?>">
            <input id="<?php echo esc_attr($this->id); ?>" name="<?php echo esc_attr($name); ?>" type="checkbox" value="<?php echo esc_attr($this->value()); ?>"<?php \checked($this->value()); ?>>
            <?php echo esc_html($this->label); ?>
          </label>
          <?php
          $this->description();
          break;

        case 'dropdown-categories':
          $dropdown_categories_args = [
            'class' => isset($this->input_attrs['class']) ? join(' ', $this->input_attrs['class']) : '',
            'show_option_none' => __('&mdash; Select &mdash;'),
            'option_none_value' => 0
          ];
          $dropdown_categories_args = \apply_filters("ezwpz_settings_dropdown_categories_control-{$this->page}", $dropdown_categories_args);
          $dropdown_categories_args = \apply_filters("ezwpz_settings_dropdown_categories_control-{$this->page}-{$this->section}", $dropdown_categories_args);
          $dropdown_categories_args = \apply_filters("ezwpz_settings_dropdown_categories_control-{$this->page}-{$this->section}-{$this->field}", $dropdown_categories_args);
          $dropdown_categories_args = \apply_filters("ezwpz_settings_dropdown_categories_control-{$this->page}-{$this->section}-{$this->field}-{$this->id}", $dropdown_categories_args);
          $dropdown_categories_args = array_merge($dropdown_categories_args, [
            'id' => $this->id,
            'name' => $name,
            'selected' => $this->value(),
          ]);
          wp_dropdown_categories($dropdown_categories_args);
          break;

        case 'dropdown-pages':
          $dropdown_pages_args = [
            'class' => isset($this->input_attrs['class']) ? join(' ', $this->input_attrs['class']) : '',
            'show_option_none' => __('&mdash; Select &mdash;'),
            'option_none_value' => 0
          ];
          $dropdown_pages_args = \apply_filters("ezwpz_settings_dropdown_pages_control-{$this->page}", $dropdown_pages_args);
          $dropdown_pages_args = \apply_filters("ezwpz_settings_dropdown_pages_control-{$this->page}-{$this->section}", $dropdown_pages_args);
          $dropdown_pages_args = \apply_filters("ezwpz_settings_dropdown_pages_control-{$this->page}-{$this->section}-{$this->field}", $dropdown_pages_args);
          $dropdown_pages_args = \apply_filters("ezwpz_settings_dropdown_pages_control-{$this->page}-{$this->section}-{$this->field}-{$this->id}", $dropdown_pages_args);
          $dropdown_pages_args = array_merge($dropdown_pages_args, [
            'id' => $this->id,
            'name' => $name,
            'selected' => $this->value(),
          ]);
          wp_dropdown_pages($dropdown_pages_args);
          break;

        case 'radio':
          if (empty($this->choices))
            return;
          ?>
          <fieldset>
            <?php if (!empty($this->label)) : ?>
              <legend<?php if ($this->is_single()) : ?> class="screen-reader-text"<?php endif; ?>><?php echo esc_html($this->label); ?></legend>
            <?php endif; ?>
            <?php foreach ($this->choices as $value => $label) : ?>
              <p>
                <label for="<?php echo esc_attr($this->id); ?>-<?php echo esc_attr($value); ?>">
                  <input id="<?php echo esc_attr($this->id); ?>-<?php echo esc_attr($value); ?>" name="<?php echo esc_attr($name); ?>" type="radio" value="<?php echo esc_attr($value); ?>"<?php \checked($this->value()); ?>>
                  <?php echo esc_html($label); ?>
                </label>
              </p>
            <?php endforeach; ?>
            <?php $this->description(); ?>
          </fieldset>
          <?php
          break;

        case 'richtext':
          $richtext_args = \apply_filters("ezwpz_settings_richtext_control-{$this->page}", []);
          $richtext_args = \apply_filters("ezwpz_settings_richtext_control-{$this->page}-{$this->section}", $richtext_args);
          $richtext_args = \apply_filters("ezwpz_settings_richtext_control-{$this->page}-{$this->section}-{$this->field}", $richtext_args);
          $richtext_args = \apply_filters("ezwpz_settings_richtext_control-{$this->page}-{$this->section}-{$this->field}-{$this->id}", $richtext_args);
          $richtext_args['textarea_name'] = $name;
          wp_editor($this->value(), $this->id, $richtext_args);
          $this->description();
          break;

        case 'select':
          if (empty($this->choices))
            return;
          ?>
          <?php if (!empty($this->label)) : ?>
            <label for="<?php echo esc_attr($this->id); ?>"><?php echo esc_html($this->label); ?></label>
          <?php endif; ?>
          <select id="<?php echo esc_attr($this->id); ?>" name="<?php echo esc_attr($name); ?>"<?php $this->input_attrs(); ?>>
            <?php foreach ($this->choices as $value => $label) : ?>
              <option value="<?php echo esc_attr($value); ?>"<?php selected($this->value(), $value); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
          </select>
          <?php
          $this->description();
          break;

        case 'textarea':
          ?>
          <textarea id="<?php echo esc_attr($this->id); ?>" name="<?php echo esc_attr($name); ?>"<?php $this->input_attrs(); ?>><?php echo esc_textarea($this->value()); ?></textarea>
          <?php
          $this->description();
          break;

        default:
          if (!$this->is_single()) : ?>
            <label for="<?php echo esc_attr($this->id); ?>"><?php echo esc_html($this->label); ?></label>
          <?php endif; ?>
          <input id="<?php echo esc_attr($this->id); ?>" name="<?php echo esc_attr($name); ?>" type="<?php echo esc_attr($type); ?>" value="<?php echo esc_attr($this->value()); ?>"<?php $this->input_attrs(); ?>>
          <?php
          $this->description();
          break;
      }
    }

    /**
     * Get stored value. If not set, return $default.
     * @return string|array|false
     */
    public function value() {
      if (!isset($this->setting))
        return $this->default;

      $value = \get_option($this->setting, null);

      if (isset($value) && !$this->is_single())
        $value = isset($value[$this->id]) ? $value[$this->id] : $this->default;

      if (!isset($value))
        $value = $this->default;

      return $value;
    }

    /**
     * Render the custom attributes for the control's input element.
     */
    public function input_attrs() {
      foreach ($this->input_attrs as $attr => $value) {
        if (\is_array($value))
          $value = \join(' ', $value);
        \printf(' %s="%s"', $attr, \esc_attr($value));
      }
    }

    /**
     * Render the description paragraph.
     */
    public function description() {
      if (empty($this->description))
        return;
      ?>
      <p id="<?php echo esc_attr($this->id); ?>-description" class="description"><?php echo $this->description; ?></p>
      <?php
    }

    /**
     * Check if the attached setting has more than one control.
     * @return bool
     */
    public function is_single() {
      if (!isset($this->setting))
        return true;

      global $wp_registered_settings;
      return \count($wp_registered_settings[$this->setting]['ezwpz_controls']) === 1;
    }
  }
}
