<?php
/**
 * Класс виджета переводчика
 *
 * @package DevBrothers_Simple_Translator
 */

if (!defined('ABSPATH')) {
    exit;
}

class DEVBSITR_Widget {

    /**
     * @var array
     */
    private $available_languages = [
        'ru'    => 'Русский',
        'en'    => 'English',
        'de'    => 'Deutsch',
        'fr'    => 'Français',
        'es'    => 'Español',
        'it'    => 'Italiano',
        'pt'    => 'Português',
        'zh-CN' => '中文',
        'ja'    => '日本語',
        'ko'    => '한국어',
        'ar'    => 'العربية',
    ];

    private function get_settings() {
        $defaults = [
            'default_language'  => 'ru',
            'languages'         => ['en', 'de', 'fr', 'es'],
            'widget_style'      => 'dropdown',
            'enable_floating'   => false,
            'floating_position' => 'bottom-right',
        ];

        $settings = get_option('devbsitr_settings', $defaults);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * @param array $args
     * @return string
     */
    public function render($args = []) {
        $settings = $this->get_settings();

        if (!empty($args['style'])) {
            $settings['widget_style'] = $args['style'];
        }

        if (!empty($args['languages'])) {
            $settings['languages'] = array_map('trim', explode(',', $args['languages']));
        }

        ob_start();

        echo '<div class="devbsitr-widget devbsitr-widget-' . esc_attr($settings['widget_style']) . '">';

        switch ($settings['widget_style']) {
            case 'flags':
                $this->render_flags($settings);
                break;
            case 'flags_dropdown':
                $this->render_flags_dropdown($settings);
                break;
            case 'text_links':
                $this->render_text_links($settings);
                break;
            case 'buttons':
                $this->render_buttons($settings);
                break;
            default:
                $this->render_dropdown($settings);
        }

        echo '</div>';

        return ob_get_clean();
    }

    private function render_dropdown($settings) {
        ?>
        <select class="devbsitr-select" onchange="devbsitrTranslate(this.value)">
            <option value=""><?php esc_html_e('Выберите язык', 'devbrothers-simple-translator'); ?></option>
            <option value="<?php echo esc_attr($settings['default_language']); ?>|<?php echo esc_attr($settings['default_language']); ?>">
                <?php echo esc_html($this->get_language_name($settings['default_language'])); ?>
            </option>
            <?php foreach ($settings['languages'] as $lang) : ?>
                <option value="<?php echo esc_attr($settings['default_language']); ?>|<?php echo esc_attr($lang); ?>">
                    <?php echo esc_html($this->get_language_name($lang)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private function render_flags($settings) {
        ?>
        <div class="devbsitr-flags">
            <?php
            $default_flag = $this->get_flag_path($settings['default_language']);
            if ($default_flag) :
                ?>
                <a href="#" class="devbsitr-flag" data-lang="<?php echo esc_attr($settings['default_language']); ?>"
                   onclick="devbsitrTranslate('<?php echo esc_js($settings['default_language']); ?>|<?php echo esc_js($settings['default_language']); ?>'); return false;">
                    <img src="<?php echo esc_url($default_flag); ?>"
                         alt="<?php echo esc_attr($this->get_language_name($settings['default_language'])); ?>"
                         title="<?php echo esc_attr($this->get_language_name($settings['default_language'])); ?>">
                </a>
            <?php endif; ?>

            <?php foreach ($settings['languages'] as $lang) : ?>
                <?php
                $flag_path = $this->get_flag_path($lang);
                if ($flag_path) :
                    ?>
                    <a href="#" class="devbsitr-flag" data-lang="<?php echo esc_attr($lang); ?>"
                       onclick="devbsitrTranslate('<?php echo esc_js($settings['default_language']); ?>|<?php echo esc_js($lang); ?>'); return false;">
                        <img src="<?php echo esc_url($flag_path); ?>"
                             alt="<?php echo esc_attr($this->get_language_name($lang)); ?>"
                             title="<?php echo esc_attr($this->get_language_name($lang)); ?>">
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function render_flags_dropdown($settings) {
        ?>
        <div class="devbsitr-flags-dropdown">
            <select class="devbsitr-select-with-flags" onchange="devbsitrTranslate(this.value)">
                <option value="">
                    🌐 <?php esc_html_e('Язык', 'devbrothers-simple-translator'); ?>
                </option>
                <option value="<?php echo esc_attr($settings['default_language']); ?>|<?php echo esc_attr($settings['default_language']); ?>">
                    <?php echo esc_html($this->get_flag_emoji($settings['default_language'])); ?>
                    <?php echo esc_html($this->get_language_name($settings['default_language'])); ?>
                </option>
                <?php foreach ($settings['languages'] as $lang) : ?>
                    <option value="<?php echo esc_attr($settings['default_language']); ?>|<?php echo esc_attr($lang); ?>">
                        <?php echo esc_html($this->get_flag_emoji($lang)); ?>
                        <?php echo esc_html($this->get_language_name($lang)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    private function render_text_links($settings) {
        ?>
        <div class="devbsitr-text-links">
            <a href="#" onclick="devbsitrTranslate('<?php echo esc_js($settings['default_language']); ?>|<?php echo esc_js($settings['default_language']); ?>'); return false;">
                <?php echo esc_html(strtoupper($settings['default_language'])); ?>
            </a>
            <?php foreach ($settings['languages'] as $lang) : ?>
                <span class="devbsitr-separator">|</span>
                <a href="#" onclick="devbsitrTranslate('<?php echo esc_js($settings['default_language']); ?>|<?php echo esc_js($lang); ?>'); return false;">
                    <?php echo esc_html(strtoupper($lang)); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function render_buttons($settings) {
        ?>
        <div class="devbsitr-buttons">
            <button type="button" class="devbsitr-button"
                    onclick="devbsitrTranslate('<?php echo esc_js($settings['default_language']); ?>|<?php echo esc_js($settings['default_language']); ?>'); return false;">
                <?php echo esc_html($this->get_language_name($settings['default_language'])); ?>
            </button>
            <?php foreach ($settings['languages'] as $lang) : ?>
                <button type="button" class="devbsitr-button"
                        onclick="devbsitrTranslate('<?php echo esc_js($settings['default_language']); ?>|<?php echo esc_js($lang); ?>'); return false;">
                    <?php echo esc_html($this->get_language_name($lang)); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public function render_floating() {
        $settings = $this->get_settings();
        ?>
        <div class="devbsitr-floating devbsitr-floating-<?php echo esc_attr($settings['floating_position']); ?>">
            <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render() escapes internally
            echo $this->render([]);
            ?>
        </div>
        <?php
    }

    /**
     * @param string $code
     * @return string
     */
    private function get_language_name($code) {
        return isset($this->available_languages[$code])
            ? $this->available_languages[$code]
            : $code;
    }

    /**
     * @param string $code
     * @return string|null
     */
    private function get_flag_path($code) {
        $flag_map = [
            'ru'    => 'russia.svg',
            'en'    => 'england.svg',
            'de'    => 'germany.svg',
            'fr'    => 'france.svg',
            'es'    => 'spain.svg',
            'it'    => 'italy.svg',
            'pt'    => 'portugal.svg',
            'zh-CN' => 'china.svg',
            'ja'    => 'japan.svg',
            'ko'    => 'south_korea.svg',
            'ar'    => 'saudi_arabia.svg',
        ];

        $filename = isset($flag_map[$code]) ? $flag_map[$code] : null;

        if ($filename && file_exists(DEVBSITR_PLUGIN_DIR . 'assets/flags/' . $filename)) {
            return DEVBSITR_PLUGIN_URL . 'assets/flags/' . $filename;
        }

        return null;
    }

    /**
     * @param string $code
     * @return string
     */
    private function get_flag_emoji($code) {
        $flags = [
            'ru'    => '🇷🇺',
            'en'    => '🇬🇧',
            'de'    => '🇩🇪',
            'fr'    => '🇫🇷',
            'es'    => '🇪🇸',
            'it'    => '🇮🇹',
            'pt'    => '🇵🇹',
            'zh-CN' => '🇨🇳',
            'ja'    => '🇯🇵',
            'ko'    => '🇰🇷',
            'ar'    => '🇸🇦',
        ];

        return isset($flags[$code]) ? $flags[$code] : '🌐';
    }
}
