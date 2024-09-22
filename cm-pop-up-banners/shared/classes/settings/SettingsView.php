<?php

namespace com\cminds\popupfly;

class SettingsView {

    const TYPE_BOOL = 'bool';
    const TYPE_INT = 'int';
    const TYPE_STRING = 'string';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_RICH_TEXT = 'rich_text';
    const TYPE_RADIO = 'radio';
    const TYPE_SELECT = 'select';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_MULTICHECKBOX = 'multicheckbox';
    const TYPE_CUSTOM = 'custom';

    public static function renderField($optionKey, $config) {

        switch ($config['type']) {
            case self::TYPE_BOOL:
                return self::renderBool($optionKey,$config);
            case self::TYPE_INT:
                return self::renderInputNumber($optionKey,$config);
            case self::TYPE_TEXTAREA:
                return self::renderTextarea($optionKey,$config);
            case self::TYPE_RICH_TEXT:
                return self::renderRichText($optionKey,$config);
            case self::TYPE_RADIO:
                return '<div class="multiline">' . self::renderRadio($optionKey, $config) . '</div>';
            case self::TYPE_SELECT:
                return self::renderSelect($optionKey, $config);
            case self::TYPE_MULTISELECT:
                return self::renderMultiSelect($optionKey, $config);
            case self::TYPE_MULTICHECKBOX:
                return self::renderMultiCheckbox($optionKey, $config);
            case self::TYPE_CUSTOM:
                return self::renderCustomInput($optionKey, $config);
            case self::TYPE_STRING:
                return self::renderInputText($optionKey,$config);
            default:
                return self::renderInputText($optionKey, $config);
        }
    }

    protected static function renderCustomInput($optionKey, $config) {
        return do_action($optionKey, $config);
    }

    protected static function renderBool($optionKey, $config) {
        $config['options'] = array(1 => 'On', 0 => 'Off');
        return self::renderRadio($optionKey, $config);
    }

    protected static function renderInputNumber($name) {
        $currentValue = $options['value'];
        return sprintf('<input type="number" name="%s" value="%s" />', esc_attr($name), esc_attr($currentValue));
    }

    protected static function renderTextarea($name) {
                $currentValue = $options['value'];
        return sprintf('<textarea name="%s" cols="60" rows="5">%s</textarea>', esc_attr($name), esc_html($currentValue));
    }

    protected static function renderRichText($name) {
        $currentValue = $options['value'];
        ob_start();
        wp_editor($currentValue, $name, array(
            'textarea_name' => $name,
            'textarea_rows' => 10,
        ));
        return ob_get_clean();
    }

    protected static function renderRadio($name, $config) {
        $currentValue = $config['value'];
        $result = '';
        $fieldName = esc_attr($name);
        foreach ($config['options'] as $value => $text) {
            $fieldId = esc_attr($name . '_' . $value);
            $result .= sprintf('<label><input type="radio" name="%s" id="%s" value="%s"%s /> %s</label>',
                    $fieldName, $fieldId, esc_attr($value),
                    ( $currentValue == $value ? ' checked="checked"' : ''), esc_html($text)
            );
        }
        return $result;
    }

    protected static function renderSelect($name, $options) {
        return sprintf('<div><select name="%s">%s</select></div>', esc_attr($name), self::renderSelectOptions($name, $options));
    }

    protected static function renderSelectOptions($name, $config) {
        $currentValue = $config['value'];
        $result = '';
        foreach ($config['options'] as $value => $text) {
            $result .= sprintf('<option value="%s"%s>%s</option>',
                    esc_attr($value),
                    ( self::isSelected($value, $currentValue) ? ' selected="selected"' : ''),
                    esc_html($text)
            );
        }
        return $result;
    }

    protected static function isSelected($option, $value) {
        if (is_array($value)) {
            return in_array($option, $value);
        } else {
            return ((string) $option == (string) $value);
        }
    }

    protected static function renderMultiSelect($name, $options) {
        return sprintf('<div><select name="%s[]" multiple="multiple">%s</select>',
                esc_attr($name), self::renderSelectOptions($name, $options));
    }

    protected static function renderMultiCheckbox($name, $config) {
        $result = '';
        $currentValue = $config['value'];
        if(!is_array($config['options'])){
            $config['options'] = call_user_func($config['options']);
        }
        foreach ($config['options'] as $value => $label) {
            $result .= self::renderMultiCheckboxItem($name, $value, $label, $currentValue);
        }
        return '<div>' . $result . '</div>';
    }

    protected static function renderMultiCheckboxItem($name, $value, $label, $current_value) {
        if(!is_array($current_value)){
            $current_value = [$current_value];
        }
        return sprintf('<div><label><input type="checkbox" name="%s[]" value="%s"%s /> %s</label></div>',
                esc_attr($name),
                esc_attr($value),
                (in_array($value, $current_value) ? ' checked="checked"' : ''),
                esc_html($label)
        );
    }

    protected static function renderInputText($name,$options) {
        $value = $options['value'];
        return sprintf('<input type="text" name="%s" value="%s" />', esc_attr($name), esc_attr($value));
    }

}
