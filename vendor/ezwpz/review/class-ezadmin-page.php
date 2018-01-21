<?php
require_once 'settings/class-ezadmin-settings-section.php';

class EZAdmin_Page {
  /**
   * Process settings file to generate sections and settings.
   */
  public function add_settings() {
    $menu_slug = $this->menu_slug;
    $single = $this->single;

    if ( $single ) {
      $register_args = apply_filters( "{$menu_slug}_register_setting", [ 'sanitize_callback' => [ $this, 'sanitize_single' ] ] );

      register_setting( $menu_slug, $menu_slug, $register_args );
    }

    foreach ( $this->settings_schema as $section ) :
      if ( in_array( $section['id'], $this->sections ) )
        wp_die( sprintf( 'Duplicate section ID. Section IDs must be unique. ID <code>%s</code> already exists.', $section['id'] ), 'Duplicate section ID' );

      $this->sections[] = $section['id'];

      $section_callback = apply_filters( "{$menu_slug}_add_settings_section_callback", [ $this, 'section_callback' ] );
      $section_callback = apply_filters( "{$menu_slug}_add_settings_section_callback-{$section['id']}", $section_callback );

      add_settings_section( $section['id'], $section['label'], $section_callback, $menu_slug );

      foreach ( $section['settings'] as $setting ) :
        if ( in_array( $setting['id'], $this->settings ) )
          wp_die( sprintf( 'Duplicate setting ID. Setting IDs must be unique. ID <code>%s</code> already exists.', $setting['id'] ), 'Duplicate setting ID' );

        $id = $setting['id'];
        $type = $setting['type'];

        $this->settings[] = $id;
        $this->settings_types[] = $type;

        if ( ! $single ) {
          $register_args = [];

          if ( isset( $setting['default'] ) )
            $register_args['default'] = $setting['default'];

          $sanitize_callback = apply_filters( "{$menu_slug}_sanitize_callback", [ $this, 'sanitize' ] );
          $sanitize_callback = apply_filters( "{$menu_slug}_sanitize_callback-{$type}", $sanitize_callback );
          $sanitize_callback = apply_filters( "{$menu_slug}_sanitize_callback-{$id}", $sanitize_callback );

          $register_args['sanitize_callback'] = $sanitize_callback;
          $register_args = apply_filters( "{$menu_slug}_register_setting", $register_args );
          $register_args = apply_filters( "{$menu_slug}_register_setting-{$type}", $register_args );
          $register_args = apply_filters( "{$menu_slug}_register_setting-{$id}", $register_args );

          register_setting( $menu_slug, $id, $register_args );
        }

        $field_args = [];

        if ( in_array( $type, [ 'color', 'email', 'image_picker', 'link_list', 'page', 'range', 'select', 'text', 'textarea', 'url' ] ) )
          $field_args = [ 'label_for' => $id ];

        $field_args = apply_filters( "{$menu_slug}_add_settings_field", $field_args );
        $field_args = apply_filters( "{$menu_slug}_add_settings_field-{$type}", $field_args );
        $field_args = apply_filters( "{$menu_slug}_add_settings_field-{$id}", $field_args );

        $field_args['setting'] = $setting;

        $settings_callback = apply_filters( "{$menu_slug}_add_settings_field_callback", [ $this, 'field_callback' ] );
        $settings_callback = apply_filters( "{$menu_slug}_add_settings_field_callback-{$type}", $settings_callback );
        $settings_callback = apply_filters( "{$menu_slug}_add_settings_field_callback-{$id}", $settings_callback );

        add_settings_field( $id, $setting['label'], $settings_callback, $menu_slug, $section['id'], $field_args );
      endforeach;
    endforeach;
  }

  /**
   * Field callback.
   *
   * @param $field_args array
   */
  public function field_callback( $field_args ) {
    if ( ! isset( $field_args['setting'] ) && ! isset( $field_args['setting']['type'] ) && ! isset( $field_args['setting']['id'] ) && ! isset( $field_args['setting']['label'] ) ) {
      echo 'A <code>type</code>, <code>id</code>, and <code>label</code> are required for each setting.';
      return;
    }

    $setting = $field_args['setting'];
    $type = $setting['type'];

    if ( $this->single ) {
      $option = get_option( $this->menu_slug );
      $value = isset( $option[$setting['id']] ) ? $option[$setting['id']] : '';
    } else {
      $value = get_option( $setting['id'] );
    }

    var_dump( get_option( $this->menu_slug ) );
    var_dump( $value );

    if ( $type === 'category' ) {
      $this->dropdown_categories( $setting, $value );
    } elseif ( in_array( $type, [ 'checkbox', 'radio' ] ) ) {
      $this->selection_list( $setting, $value );
    } elseif ( in_array( $type, [ 'color', 'date', 'datetime-local', 'email', 'month', 'number', 'range', 'text', 'time', 'week', 'url' ] ) ) {
      $this->input( $setting, $value );
    } elseif ( $type === 'link_list' ) {
      $this->dropdown_nav_menus( $setting, $value );
    } elseif ( $type === 'page' ) {
      $this->dropdown_pages( $setting, $value );
    } elseif ( $type === 'richtext' ) {
      $this->editor( $setting, $value );
    } elseif ( $type === 'select' ) {
      $this->dropdown( $setting, $value );
    } elseif ( $type === 'textarea' ) {
      $this->textarea( $setting, $value );
    }

    if ( $setting['info'] )
      $this->info( $setting );
  }

  /**
   * Generate a datalist.
   *
   * @param $setting array  JSON of current setting from the schema
   *
   * @return string
   */
  protected function datalist( $setting ) {
    if ( ! isset( $setting['options'] ) ) {
      echo '<code>select</code> fields require an <code>options</code> property.';
      return;
    }

    $id = $setting['id'];
    $menu_slug = $this->menu_slug;

    $attributes = "id='{$id}-list'";

    $options = '';
    foreach( $setting['options'] as $option ) {
      $options .= $this->option( $option['value'] );
    }

    $output = sprintf( '<datalist %s>%s</datalist>', $attributes, $options );
    $output = apply_filters( "{$menu_slug}_datalist", $output );
    $output = apply_filters( "{$menu_slug}_datalist-{$id}", $output );
    return $output;
  }

  /**
   * Generate an option. If no label given, generates an option for a datalist.
   *
   * @param $value    string Value of option
   * @param $label    string Label
   * @param $selected string Selected value
   *
   * @return string
   */
  protected function option( $value , $label = null, $selected = null ) {
    if ( $label ) {
      return sprintf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $value, $selected, false ), $label );
    }
    return sprintf( '<option value="%s">', esc_attr( $value ) );
  }

  /**
   * Generate a description paragraph.
   *
   * @param $setting array JSON of current setting from the schema
   */
  protected function info( $setting ) {
    printf( '<p class="description">%s</p>', $setting['info'] );
  }

  /**
   * Generate a select dropdown.
   *
   * @param $setting array  JSON of curent setting from the schema
   * @param $value   string Current value
   */
  protected function dropdown( $setting, $value ) {
    if ( ! isset( $setting['options'] ) ) {
      echo '<code>select</code> fields require an <code>options</code> property.';
      return;
    }

    $id = $setting['id'];
    $menu_slug = $this->menu_slug;

    $attributes = "id='{$id}'";
    $attributes .= $this->single ? " name='{$this->menu_slug}[{$id}]'" : " name='{$id}'";

    $options = $this->option( '', '— Select —', $value );
    foreach ( $setting['options'] as $option ) {
      if ( ! isset( $option['value'] ) )
        return;

      $label = isset( $option['label'] ) ? $option['label'] : null;
      $options .= $this->option( $option['value'], $label, $value );
    }

    $output = sprintf( '<select %s>%s</select>', $attributes, $options );
    $output = apply_filters( "{$menu_slug}_dropdown", $output );
    $output = apply_filters( "{$menu_slug}_dropdown-{$id}", $output );
    echo $output;
  }

  /**
   * Generate a select dropdown of a taxonomy (categories by default).
   *
   * @param $setting array  JSON of current setting from the schema
   * @param $value   string Current value
   */
  protected function dropdown_categories( $setting, $value ) {
    $id = $setting['id'];
    $menu_slug = $this->menu_slug;

    $dropdown_args = apply_filters( "{$menu_slug}_dropdown_category", [
      'show_option_none' => '— Select —',
      'option_none_value' => '',
      'orderby' => 'name',
    ] );

    if ( isset( $setting['taxonomy'] ) )
      $dropdown_args['taxonomy'] = $setting['taxonomy'];

    $dropdown_args = apply_filters( "{$menu_slug}_dropdown_category-{$id}", $dropdown_args );

    // Don't allow the id, name, or selected to be overwritten in the filter.
    $dropdown_args = array_merge( $dropdown_args, [ 'id' => $id, 'name' => $id, 'selected' => $value ] );

    wp_dropdown_categories( $dropdown_args );
  }

  /**
   * Generate a select dropdown of nav menus.
   *
   * @param $setting array  JSON of current setting from the schema
   * @param $value   string Current value
   */
  protected function dropdown_nav_menus( $setting, $value ) {
    $menus = wp_get_nav_menus();

    $setting['options'] = [];
    foreach ( $menus as $menu ) {
      $option = [
        'value' => $menu->term_id,
        'label' => $menu->name,
      ];
      $setting['options'][] = $option;
    }

    $this->dropdown( $setting, $value );
    return;
  }

  /**
   * Generate a select dropdown of pages.
   *
   * @param $setting array  JSON of current setting from the schema
   * @param $value   string Current value
   */
  protected function dropdown_pages( $setting, $value ) {
    $id = $setting['id'];
    $menu_slug = $this->menu_slug;

    $dropdown_args = apply_filters( "{$menu_slug}_dropdown_category", [
      'show_option_none' => '— Select —',
      'option_none_value' => '',
    ] );
    $dropdown_args = apply_filters( "{$menu_slug}_dropdown_category-{$id}", $dropdown_args );

    // Don't allow the id, name, or selected to be overwritten in the filter.
    $dropdown_args = array_merge( $dropdown_args, [ 'id' => $id, 'name' => $id, 'selected' => $value ] );

    wp_dropdown_pages( $dropdown_args );
  }

  /**
   * Generate a wysiwyg editor.
   *
   * @param $setting array  JSON of current setting from the schema
   * @param $value   string Current value
   */
  protected function editor( $setting, $value ) {
    $menu_slug = $this->menu_slug;

    $editor_settings = apply_filters( "{$menu_slug}_editor", [] );
    $editor_settings = apply_filters( "{$menu_slug}_editor-{$setting['id']}", $editor_settings );

    // Don't allow the name to be overwritten in the filter.
    $editor_settings = array_merge( $editor_settings, [ 'textarea_name' => $setting['id'] ] );

    wp_editor( $value, $setting['id'], $editor_settings );
  }

  /**
   * Generate an image picker.
   *
   * @param $setting array  JSON of current setting from the schema
   * @param $value   string Input value
   */

  /**
   * Generate an input.
   *
   * @param $setting array  JSON of current setting from the schema
   * @param $value   string Input value
   * @param $checked string Value of checked input (radio and checkbox)
   *
   * @return string|void
   */
  protected function input( $setting, $value, $checked = null ) {
    $id = $setting['id'];
    $input_type = $setting['type'];
    $menu_slug = $this->menu_slug;
    $type = $setting['type'];

    $white_listed = [];
    $datalist = false;

    if ( in_array( $type, [ 'date', 'datetime-local', 'month', 'number', 'range', 'time', 'week' ] ) ) {
      array_push( $white_listed, 'max', 'min', 'step' );
    }
    if ( in_array( $type, [ 'email', 'number', 'text', 'url' ] ) ) {
      array_push( $white_listed, 'placeholder' );
    }
    if ( in_array( $type, [ 'email', 'text', 'url' ] ) ) {
      array_push( $white_listed, 'maxlength', 'minlength', 'pattern' );
    }

    // Attributes to be added to the input
    $attributes = '';
    if ( in_array( $type, [ 'checkbox', 'radio' ] ) ) {
      $attributes .= checked($value, $checked, false) . ' ';
    }
    if ( ! in_array( $type, [ 'checkbox', 'radio' ] ) ) {
      $attributes .= "id='{$id}'";

      if ( isset( $setting['options'] ) ) {
        $datalist = true;
        $attributes .= " list='{$id}-list'";
      }
    }
    if ( $type === 'checkbox' ) {
      $attributes .= $this->single ? " name='{$menu_slug}[{$id}][]'" : " name='{$id}[]'";
    } else {
      $attributes .= $this->single ? " name='{$menu_slug}[{$id}]'" : " name='{$id}'";
    }
    if ( $type === 'color' ) {
      $input_type = 'text';
      $attributes .= "class='color-picker' ";
    }
    if ( in_array( $type, [ 'email', 'text', 'url' ] ) ) {
      $attributes .= "class='regular-text' ";
    }

    $escaped_value = esc_attr( $value );
    $attributes .= "type='{$input_type}' value='{$escaped_value}'";
    foreach ( $white_listed as $attribute ) {
      if ( isset( $setting[$attribute] ) )
        $attributes .= " {$attribute}='{$setting[$attribute]}'";
    }

    $output = sprintf( '<input %s>', $attributes );
    $output = apply_filters( "{$menu_slug}_{$type}_input", $output );
    $output = apply_filters( "{$menu_slug}_{$type}_input-{$id}", $output );

    if ( in_array( $type, [ 'checkbox', 'radio' ] ) )
      return $output;

    echo $output;

    if ( $datalist ) {
      echo $this->datalist( $setting );
    }
  }

  /**
   * Generates a list of checkbox or radio inputs.
   *
   * @param $setting array  JSON of current setting from the schema
   * @param $value   string Current value
   */
  protected function selection_list( $setting, $value ) {
    if ( ! isset( $setting['options'] ) ) {
      echo '<code>checkbox</code> and <code>radio</code> fields require an <code>options</code> property.';
      return;
    }

    $id = $setting['id'];
    $menu_slug = $this->menu_slug;

    $options = '';
    foreach( $setting['options'] as $option ) {
      if ( ! isset( $option['value'] ) || ! isset( $option['label'] ) )
        return;

      $input = $this->input( $setting, $option['value'], $value );
      $options .= sprintf( '<p><label>%s %s</label>', $input, $option['label'] );
    }

    $output = sprintf( '<fieldset><legend class="screen-reader-text">%s</legend>%s</fieldset>', $setting['label'], $options );
    $output = apply_filters( "{$menu_slug}_input_list", $output );
    $output = apply_filters( "{$menu_slug}_input_list-{$id}", $output );
    echo $output;
  }

  /**
   * Generate a textarea.
   *
   * @param $setting array  JSON of current setting from the schema
   * @param $value   string Current value
   */
  protected function textarea( $setting, $value ) {
    $id = $setting['id'];
    $menu_slug = $this->menu_slug;

    $white_listed = [ 'placeholder', 'maxlength', 'minlength' ];

    $attributes = "class='large-text' id='{$id}' rows='10' cols='50'";
    $attributes .= $this->single ? " name='{$menu_slug}[{$id}]'" : " name='{$id}'";

    foreach( $white_listed as $attribute ) {
      if ( isset( $setting[$attribute] ) )
        $attributes .= " {$attribute}='{$setting[$attribute]}'";
    }

    $output = sprintf( '<textarea %s>%s</textarea>', $attributes, $value );

    $output = apply_filters( "{$menu_slug}_textarea", $output );
    $output = apply_filters( "{$menu_slug}_textarea-{$id}", $output );

    echo $output;
  }

  /**
   * @param $data
   */
  public function sanitize( $input ) {
    var_dump( $input );
    die;

    $i = array_search( $setting, $this->settings );
    $type = $this->settings_types[$i];

    if ( $type === 'color' ) {
      $sanitized = sanitize_hex_color( $value );
    } elseif ( in_array( $type, [ 'date', 'datetime-local', 'month', 'text', 'time', 'week', 'url' ] ) ) {
      $sanitized = sanitize_text_field( $value );
    } elseif ( $type === 'email' ) {
      $sanitized = sanitize_email( $value );
    } elseif ( $type === 'link_list' ) {
      $sanitized = is_nav_menu( $value ) ? $value : '';
    } elseif ( in_array( $type, [ 'number', 'range' ] ) ) {
      $sanitized = intval( $value );
    } elseif ( in_array( $type, [ 'richtext', 'textarea' ] ) ) {
      $sanitized = sanitize_textarea_field( $value );
    } elseif ( in_array( $type, [ 'category','checkbox','image_picker','page','radio','select' ] ) ) {
      $sanitized = '';
    } else {
      $sanitized = '';
    }

    return $sanitized;
  }

  /**
   * @param
   */
  public function sanitize_single( $input ) {
    var_dump( $input );
    die;

    $sanitized_values = [];

    foreach ( $settings as $setting => $value ) {
      $sanitized_values[$setting] = $this->sanitize( $setting, $value );
    }

    return $sanitized_values;
  }
}
