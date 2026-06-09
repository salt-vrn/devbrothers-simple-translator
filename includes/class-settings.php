<?php
/**
 * Класс настроек плагина
 *
 * @package DevBrothers_Simple_Translator
 */

if (!defined('ABSPATH')) {
    exit;
}

class DEVBSITR_Settings {

    /**
     * @var string
     */
    private $option_name = 'devbsitr_settings';

    /**
     * @var array|null
     */
    private $cached_settings = null;

    /**
     * @var array
     */
    private $available_languages = [
        'en'    => 'English (Английский)',
        'de'    => 'Deutsch (Немецкий)',
        'fr'    => 'Français (Французский)',
        'es'    => 'Español (Испанский)',
        'it'    => 'Italiano (Итальянский)',
        'pt'    => 'Português (Португальский)',
        'zh-CN' => '中文 (Китайский упрощенный)',
        'ja'    => '日本語 (Японский)',
        'ko'    => '한국어 (Корейский)',
        'ar'    => 'العربية (Арабский)',
    ];

    /**
     * @param array $input
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = [];

        if (isset($input['default_language'])) {
            $sanitized['default_language'] = sanitize_text_field($input['default_language']);
        }

        if (isset($input['languages']) && is_array($input['languages'])) {
            $sanitized['languages'] = array_map('sanitize_text_field', $input['languages']);
        }

        if (isset($input['widget_style'])) {
            $sanitized['widget_style'] = sanitize_text_field($input['widget_style']);
        }

        if (isset($input['enable_floating'])) {
            $sanitized['enable_floating'] = (int) $input['enable_floating'];
        }

        if (isset($input['floating_position'])) {
            $sanitized['floating_position'] = sanitize_text_field($input['floating_position']);
        }

        return $sanitized;
    }

    /**
     * @return array
     */
    public function get_settings() {
        if ($this->cached_settings !== null) {
            return $this->cached_settings;
        }

        $defaults = [
            'default_language'  => 'ru',
            'languages'         => ['en', 'de', 'fr', 'es'],
            'widget_style'      => 'dropdown',
            'enable_floating'   => false,
            'floating_position' => 'bottom-right',
        ];

        $settings              = get_option($this->option_name, $defaults);
        $this->cached_settings = wp_parse_args($settings, $defaults);

        return $this->cached_settings;
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Недостаточно прав', 'devbrothers-simple-translator'));
        }

        $settings_saved = false;

        if (isset($_POST['devbsitr_save_settings']) &&
            isset($_POST['_wpnonce']) &&
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'devbsitr_settings_nonce')) {
            $this->save_settings_internal();
            $this->cached_settings = null;
            $settings_saved = true;
        }

        $settings = $this->get_settings();

        if ($settings_saved) {
            echo '<div class="notice notice-success"><p>' .
                 esc_html__('Настройки сохранены!', 'devbrothers-simple-translator') .
                 '</p></div>';
        }

        ?>
        <form method="post" action="">
            <?php wp_nonce_field('devbsitr_settings_nonce'); ?>

            <!-- Категория: Основные настройки -->
            <div id="general" class="devbrothers-settings-category">
                <h2>
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e('Основные настройки', 'devbrothers-simple-translator'); ?>
                </h2>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Основной язык сайта', 'devbrothers-simple-translator'); ?></th>
                        <td>
                            <select name="devbsitr_default_language">
                                <option value="ru" <?php selected($settings['default_language'], 'ru'); ?>>🇷🇺 Русский</option>
                                <option value="en" <?php selected($settings['default_language'], 'en'); ?>>🇬🇧 English</option>
                            </select>
                            <p class="description"><?php esc_html_e('Язык по умолчанию, на котором написан ваш сайт', 'devbrothers-simple-translator'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Доступные языки для перевода', 'devbrothers-simple-translator'); ?></th>
                        <td>
                            <fieldset>
                                <?php foreach ($this->available_languages as $code => $name) : ?>
                                    <?php $checked = in_array($code, $settings['languages'], true); ?>
                                    <label class="devbsitr-checkbox-label">
                                        <input type="checkbox"
                                               name="devbsitr_languages[]"
                                               value="<?php echo esc_attr($code); ?>"
                                               <?php checked($checked); ?> />
                                        <?php echo esc_html($name); ?>
                                    </label>
                                <?php endforeach; ?>
                            </fieldset>
                            <p class="description"><?php esc_html_e('Выберите языки, на которые пользователи смогут переключиться', 'devbrothers-simple-translator'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Категория: Внешний вид -->
            <div id="appearance" class="devbrothers-settings-category">
                <h2>
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <?php esc_html_e('Внешний вид виджета', 'devbrothers-simple-translator'); ?>
                </h2>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Стиль виджета', 'devbrothers-simple-translator'); ?></th>
                        <td>
                            <fieldset>
                                <?php
                                $styles = [
                                    'dropdown'       => [__('Dropdown', 'devbrothers-simple-translator'), __('выпадающий список', 'devbrothers-simple-translator')],
                                    'flags'          => [__('Flags', 'devbrothers-simple-translator'), __('флаги стран', 'devbrothers-simple-translator')],
                                    'flags_dropdown' => [__('Flags + Dropdown', 'devbrothers-simple-translator'), __('флаги с выпадающим списком', 'devbrothers-simple-translator')],
                                    'text_links'     => [__('Text Links', 'devbrothers-simple-translator'), __('текстовые ссылки (RU | EN | DE)', 'devbrothers-simple-translator')],
                                    'buttons'        => [__('Buttons', 'devbrothers-simple-translator'), __('кнопки с названиями языков', 'devbrothers-simple-translator')],
                                ];

                                foreach ($styles as $value => $labels) :
                                    ?>
                                    <label class="devbsitr-radio-label">
                                        <input type="radio"
                                               name="devbsitr_widget_style"
                                               value="<?php echo esc_attr($value); ?>"
                                               <?php checked($settings['widget_style'], $value); ?> />
                                        <strong><?php echo esc_html($labels[0]); ?></strong>
                                        <span class="description"> - <?php echo esc_html($labels[1]); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Категория: Позиционирование -->
            <div id="positioning" class="devbrothers-settings-category">
                <h2>
                    <span class="dashicons dashicons-admin-site"></span>
                    <?php esc_html_e('Позиционирование', 'devbrothers-simple-translator'); ?>
                </h2>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Floating виджет', 'devbrothers-simple-translator'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="devbsitr_enable_floating"
                                       value="1"
                                       <?php checked($settings['enable_floating'], 1); ?> />
                                <?php esc_html_e('Показывать фиксированный виджет на всех страницах', 'devbrothers-simple-translator'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Виджет будет зафиксирован в углу экрана и виден на всех страницах', 'devbrothers-simple-translator'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Позиция floating виджета', 'devbrothers-simple-translator'); ?></th>
                        <td>
                            <select name="devbsitr_floating_position">
                                <option value="top-left" <?php selected($settings['floating_position'], 'top-left'); ?>><?php esc_html_e('Сверху слева', 'devbrothers-simple-translator'); ?></option>
                                <option value="top-right" <?php selected($settings['floating_position'], 'top-right'); ?>><?php esc_html_e('Сверху справа', 'devbrothers-simple-translator'); ?></option>
                                <option value="bottom-left" <?php selected($settings['floating_position'], 'bottom-left'); ?>><?php esc_html_e('Снизу слева', 'devbrothers-simple-translator'); ?></option>
                                <option value="bottom-right" <?php selected($settings['floating_position'], 'bottom-right'); ?>><?php esc_html_e('Снизу справа', 'devbrothers-simple-translator'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Категория: Использование -->
            <div id="usage" class="devbrothers-settings-category">
                <h2>
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e('Как использовать', 'devbrothers-simple-translator'); ?>
                </h2>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Шорткод', 'devbrothers-simple-translator'); ?></th>
                        <td>
                            <code class="devbsitr-code-inline">[devbsitr_translator]</code>
                            <p class="description"><?php esc_html_e('Вставьте этот шорткод в любое место: в текст страницы, виджет или шаблон', 'devbrothers-simple-translator'); ?></p>

                            <h4><?php esc_html_e('Параметры шорткода:', 'devbrothers-simple-translator'); ?></h4>
                            <ul class="devbsitr-params-list">
                                <li>
                                    <code>[devbsitr_translator style="dropdown"]</code> -
                                    <?php esc_html_e('переопределить стиль', 'devbrothers-simple-translator'); ?>
                                </li>
                                <li>
                                    <code>[devbsitr_translator languages="en,de,fr"]</code> -
                                    <?php esc_html_e('переопределить языки', 'devbrothers-simple-translator'); ?>
                                </li>
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('PHP код', 'devbrothers-simple-translator'); ?></th>
                        <td>
                            <code class="devbsitr-code-block">&lt;?php devbsitr_translator(); ?&gt;</code>
                            <p class="description"><?php esc_html_e('Вставьте в любой PHP файл темы (header.php, footer.php и т.д.)', 'devbrothers-simple-translator'); ?></p>

                            <h4><?php esc_html_e('С параметрами:', 'devbrothers-simple-translator'); ?></h4>
                            <code class="devbsitr-code-block">&lt;?php devbsitr_translator(['style' => 'flags']); ?&gt;</code>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Пример вывода', 'devbrothers-simple-translator'); ?></th>
                        <td>
                            <div class="devbsitr-widget-preview">
                                <?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render() escapes internally
                                echo devbsitr_plugin()->widget->render();
                                ?>
                            </div>
                            <p class="description"><?php esc_html_e('Это живой пример виджета с текущими настройками', 'devbrothers-simple-translator'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <p class="submit">
                <button type="submit" name="devbsitr_save_settings" class="button button-primary">
                    <span class="dashicons dashicons-yes"></span>
                    <?php esc_html_e('Сохранить настройки', 'devbrothers-simple-translator'); ?>
                </button>
            </p>
        </form>
        <?php
    }

    private function save_settings_internal() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce проверяется в render_settings_page
        $default_language = isset($_POST['devbsitr_default_language'])
            ? sanitize_text_field(wp_unslash($_POST['devbsitr_default_language']))
            : 'ru';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $languages = isset($_POST['devbsitr_languages'])
            ? array_map('sanitize_text_field', wp_unslash($_POST['devbsitr_languages']))
            : ['en'];
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $widget_style = isset($_POST['devbsitr_widget_style'])
            ? sanitize_text_field(wp_unslash($_POST['devbsitr_widget_style']))
            : 'dropdown';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $enable_floating = isset($_POST['devbsitr_enable_floating']) ? 1 : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $floating_position = isset($_POST['devbsitr_floating_position'])
            ? sanitize_text_field(wp_unslash($_POST['devbsitr_floating_position']))
            : 'bottom-right';

        $settings = [
            'default_language'  => $default_language,
            'languages'         => $languages,
            'widget_style'      => $widget_style,
            'enable_floating'   => $enable_floating,
            'floating_position' => $floating_position,
        ];

        update_option($this->option_name, $settings);
    }
}
