<?php
/**
 * 主题辅助函数
 *
 */

namespace Wenprise\Customizer;

class Helpers
{

    /**
     * Allowed html
     *
     * @param string $allowed_elements
     *
     * @return array
     */
    public static function allowed_html($allowed_elements = '')
    {

        // bail early if parameter is empty
        if (empty($allowed_elements)) {
            return [];
        }

        if (is_string($allowed_elements)) {
            $allowed_els = explode(',', $allowed_elements);
        }

        $allowed_html = [];

        $allowed_tags = wp_kses_allowed_html('post');

        foreach ((array)$allowed_elements as $el) {
            $el = trim($el);
            if (array_key_exists($el, $allowed_tags)) {
                $allowed_html[ $el ] = $allowed_tags[ $el ];
            }
        }

        return $allowed_html;
    }
}