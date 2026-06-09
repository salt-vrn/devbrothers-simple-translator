<?php
/**
 * Plugin Name: DevBrothers Simple Translator
 * Plugin URI: https://devbrothers.ru/plugins/simple-translator
 * Description: Simple and free language switcher based on Google Translate. Shortcodes, widgets, customizable styles.
 * Version: 1.0.1
 * Author: DevBrothers
 * Author URI: https://devbrothers.ru
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: devbrothers-simple-translator
 * Requires at least: 5.8
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Requires Plugins: devbrothers-admin-panel
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DEVBSITR_VERSION', '1.0.0');
define('DEVBSITR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DEVBSITR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DEVBSITR_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('DEVBSITR_PREFIX', 'devbsitr');

class DEVBSITR_Plugin {

    /** @var DEVBSITR_Plugin */
    private static $instance = null;

    /** @var DEVBSITR_Widget */
    public $widget;

    /** @var DEVBSITR_Settings */
    public $settings;

    /**
     * Флаг, что ресурсы переводчика были поставлены в очередь.
     * @var bool
     */
    private $assets_enqueued = false;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
        $this->init_components();
    }

    private function load_dependencies() {
        require_once DEVBSITR_PLUGIN_DIR . 'includes/class-widget.php';
        require_once DEVBSITR_PLUGIN_DIR . 'includes/class-settings.php';
    }

    private function init_hooks() {
        add_action('devbrothers_ready', [$this, 'register_in_devbrothers']);

        add_action('wp_enqueue_scripts', [$this, 'register_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        add_shortcode('devbsitr_translator', [$this, 'render_shortcode']);
        add_shortcode('dbst_translator', [$this, 'render_shortcode']);

        add_action('wp_footer', [$this, 'render_floating_widget']);
        add_action('wp_footer', [$this, 'output_google_translate_container'], 1);
    }

    private function init_components() {
        $this->widget   = new DEVBSITR_Widget();
        $this->settings = new DEVBSITR_Settings();
    }

    public function register_in_devbrothers() {
        if (!function_exists('devbrothers_register_plugin')) {
            return;
        }

        devbrothers_register_plugin([
            'id'          => 'simple-translator',
            'name'        => __('Simple Translator', 'devbrothers-simple-translator'),
            'name_ru'     => __('Переключатель языков', 'devbrothers-simple-translator'),
            'description' => __('Переключатель языков на базе Google Translate', 'devbrothers-simple-translator'),
            'version'     => DEVBSITR_VERSION,
            'icon'        => 'dashicons-translation',
            'settings_callback' => [$this->settings, 'render_settings_page'],
            'categories'  => [
                [
                    'id'   => 'general',
                    'name' => __('Основные настройки', 'devbrothers-simple-translator'),
                    'icon' => 'dashicons-admin-generic',
                ],
                [
                    'id'   => 'appearance',
                    'name' => __('Внешний вид', 'devbrothers-simple-translator'),
                    'icon' => 'dashicons-admin-appearance',
                ],
                [
                    'id'   => 'positioning',
                    'name' => __('Позиционирование', 'devbrothers-simple-translator'),
                    'icon' => 'dashicons-admin-site',
                ],
                [
                    'id'   => 'usage',
                    'name' => __('Использование', 'devbrothers-simple-translator'),
                    'icon' => 'dashicons-editor-code',
                ],
            ],
        ]);
    }

    /**
     * Регистрирует ресурсы фронтенда без загрузки.
     * Если floating-виджет включён, ресурсы загружаются сразу на всех страницах.
     */
    public function register_frontend_assets() {
        wp_register_style(
            'devbsitr-frontend',
            DEVBSITR_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            DEVBSITR_VERSION
        );

        wp_register_script(
            'devbsitr-frontend',
            DEVBSITR_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            DEVBSITR_VERSION,
            true
        );

        wp_register_script(
            'google-translate-element',
            'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit',
            [],
            null,
            true
        );

        $settings = $this->settings->get_settings();
        if ($settings['enable_floating']) {
            $this->enqueue_translator_assets();
        }
    }

    /**
     * Загружает CSS/JS переводчика + Google Translate.
     * Безопасно вызывать многократно — повторные вызовы игнорируются.
     */
    public function enqueue_translator_assets() {
        if ($this->assets_enqueued) {
            return;
        }
        $this->assets_enqueued = true;

        wp_enqueue_style('devbsitr-frontend');
        wp_enqueue_script('devbsitr-frontend');

        $settings = $this->settings->get_settings();

        wp_localize_script('devbsitr-frontend', 'devbsitrSettings', [
            'default_language' => $settings['default_language'],
            'languages'        => $settings['languages'],
            'widget_style'     => $settings['widget_style'],
        ]);

        wp_add_inline_script('devbsitr-frontend', $this->get_translate_function(), 'before');

        $gt_init = sprintf(
            "function googleTranslateElementInit(){if(typeof google!=='undefined'&&google.translate&&google.translate.TranslateElement){new google.translate.TranslateElement({pageLanguage:%s,includedLanguages:%s},'google_translate_element');}}",
            wp_json_encode($settings['default_language']),
            wp_json_encode(implode(',', $settings['languages']))
        );

        wp_add_inline_script('google-translate-element', $gt_init, 'before');
        wp_enqueue_script('google-translate-element');
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'devbrothers') === false) {
            return;
        }

        wp_enqueue_style(
            'devbsitr-admin',
            DEVBSITR_PLUGIN_URL . 'assets/css/admin.css',
            ['devbrothers-admin'],
            DEVBSITR_VERSION
        );
    }

    public function render_shortcode($atts) {
        $this->enqueue_translator_assets();

        $atts = shortcode_atts([
            'style'     => '',
            'languages' => '',
        ], $atts);

        return $this->widget->render($atts);
    }

    public function render_floating_widget() {
        $settings = $this->settings->get_settings();

        if ($settings['enable_floating']) {
            $this->widget->render_floating();
        }
    }

    /**
     * Выводит скрытый контейнер Google Translate, только если ресурсы были загружены.
     */
    public function output_google_translate_container() {
        if (!$this->assets_enqueued) {
            return;
        }
        echo '<div id="google_translate_element"></div>';
    }

    private function get_translate_function() {
        return "
        var devbsitrRetryCount = 0;
        var devbsitrMaxRetries = 10;

        function devbsitrTranslate(lang_pair) {
            if (!lang_pair) return;

            var lang = lang_pair.split('|')[1];
            var defaultLang = lang_pair.split('|')[0];

            var teCombo = null;
            var selects = document.querySelectorAll('select');

            for (var i = 0; i < selects.length; i++) {
                var className = selects[i].className || '';
                if (className.indexOf('goog-te-combo') !== -1) {
                    teCombo = selects[i];
                    break;
                }
            }

            if (!teCombo && devbsitrRetryCount < devbsitrMaxRetries) {
                devbsitrRetryCount++;
                setTimeout(function() {
                    devbsitrTranslate(lang_pair);
                }, 1000);
                return;
            }

            if (!teCombo) return;

            devbsitrRetryCount = 0;

            if (lang === defaultLang) {
                var cookies = document.cookie.split(';');
                for (var i = 0; i < cookies.length; i++) {
                    var cookie = cookies[i].trim();
                    if (cookie.indexOf('googtrans') === 0) {
                        var cookieName = cookie.split('=')[0];
                        document.cookie = cookieName + '=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                        document.cookie = cookieName + '=; path=/; domain=' + location.hostname + '; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                        document.cookie = cookieName + '=; path=/; domain=.' + location.hostname + '; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                    }
                }

                try {
                    localStorage.removeItem('devbsitr_selected_language');
                } catch(e) {}

                window.location.href = window.location.pathname + window.location.search;
                return;
            }

            teCombo.value = lang;

            if (typeof Event === 'function') {
                var event = new Event('change', { bubbles: true });
                teCombo.dispatchEvent(event);
                teCombo.dispatchEvent(event);
            } else {
                var event = document.createEvent('HTMLEvents');
                event.initEvent('change', true, true);
                teCombo.dispatchEvent(event);
                teCombo.dispatchEvent(event);
            }

            if (lang !== defaultLang) {
                try {
                    localStorage.setItem('devbsitr_selected_language', lang);
                } catch(e) {}
            }
        }
        ";
    }
}

function devbsitr_plugin() {
    return DEVBSITR_Plugin::get_instance();
}

/**
 * @param array $args
 */
function devbsitr_translator($args = []) {
    devbsitr_plugin()->enqueue_translator_assets();
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo devbsitr_plugin()->widget->render($args);
}

/**
 * @deprecated Используйте devbsitr_translator()
 * @param array $args
 */
function dbst_translator($args = []) {
    devbsitr_translator($args);
}

add_action('plugins_loaded', 'devbsitr_plugin', 10);
