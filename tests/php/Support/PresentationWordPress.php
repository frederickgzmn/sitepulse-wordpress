<?php
/** WordPress presentation boundaries used by real template-rendering tests. */
function checked($checked, $current = true, $display = true) {
    $attribute = (string) $checked === (string) $current ? ' checked="checked"' : '';
    if ($display) { echo $attribute; } return $attribute;
}
function selected($selected, $current = true, $display = true) {
    $attribute = (string) $selected === (string) $current ? ' selected="selected"' : '';
    if ($display) { echo $attribute; } return $attribute;
}
function _n($single, $plural, $number, $domain = null) { return (int) $number === 1 ? $single : $plural; }
function get_permalink($post = 0) { return home_url('/?p=' . (int) (is_object($post) ? $post->ID : $post)); }
function get_the_title($post = 0) { return Sitepulse_Test_WP::$posts[(int) ($post ?: get_the_ID())]['post_title'] ?? 'Fixture page'; }
function wp_timezone() { return new DateTimeZone('UTC'); }
function wp_date($format, $timestamp = null, $timezone = null) {
    return (new DateTimeImmutable('@' . ($timestamp ?? time())))->setTimezone($timezone ?: wp_timezone())->format($format);
}
function date_i18n($format, $timestamp = false, $gmt = false) { return wp_date($format, $timestamp === false ? current_time('timestamp') : $timestamp); }
function human_time_diff($from, $to = 0) {
    $seconds = abs(($to ?: time()) - $from);
    if ($seconds < 3600) { $value = max(1, (int) round($seconds / 60)); return $value . ' ' . ($value === 1 ? 'min' : 'mins'); }
    if ($seconds < 86400) { $value = (int) round($seconds / 3600); return $value . ' ' . ($value === 1 ? 'hour' : 'hours'); }
    $value = (int) round($seconds / 86400); return $value . ' ' . ($value === 1 ? 'day' : 'days');
}
function is_ssl() { return (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443'); }
function submit_button($text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null) {
    $html = '<input type="submit" name="' . esc_attr($name) . '" value="' . esc_attr($text ?? 'Save Changes') . '">';
    echo $wrap ? '<p class="submit">' . $html . '</p>' : $html;
}
function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $display = true) {
    $html = '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr(wp_create_nonce($action)) . '">';
    if ($display) { echo $html; } return $html;
}
function wp_trim_words($text, $num_words = 55, $more = null) {
    $words = preg_split('/\s+/', trim(strip_tags($text)));
    return implode(' ', array_slice($words, 0, $num_words)) . (count($words) > $num_words ? ($more ?? '…') : '');
}
function wp_http_validate_url($url) {
    $parts = parse_url($url);
    return $parts && isset($parts['host']) && in_array($parts['scheme'] ?? '', array('https', 'http'), true) && !isset($parts['user']) && !isset($parts['pass']) ? $url : false;
}
function get_locale() { return 'en_US'; }
function load_textdomain($domain, $mofile, $locale = null) { Sitepulse_Test_WP::$textdomains[] = array($domain, $mofile); return true; }
function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
    Sitepulse_Test_WP::$mail[] = compact('to', 'subject', 'message', 'headers', 'attachments'); return Sitepulse_Test_WP::$mail_result;
}

function wp_strip_all_tags($text, $remove_breaks = false) { $text = strip_tags($text); return $remove_breaks ? trim(preg_replace("/\\s+/", " ", $text)) : trim($text); }
function number_format_i18n($number, $decimals = 0) { return number_format($number, $decimals); }
function remove_all_actions($hook, $priority = false) {
    if ($priority === false) { unset($GLOBALS['wp_filter'][$hook]); }
    elseif (isset($GLOBALS['wp_filter'][$hook])) { unset($GLOBALS['wp_filter'][$hook]->callbacks[$priority]); }
    return true;
}
function add_settings_section($id, $title, $callback, $page, $args = array()) { $GLOBALS['sitepulse_test_sections'][$page][$id] = $callback; }
function do_settings_sections($page) { foreach ($GLOBALS['sitepulse_test_sections'][$page] ?? array() as $callback) { $callback(); } }
