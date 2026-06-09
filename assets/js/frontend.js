/**
 * DevBrothers Simple Translator Frontend Scripts
 *
 * @package DevBrothers_Simple_Translator
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        try {
            var savedLang = localStorage.getItem('devbsitr_selected_language');
            var defaultLang = window.devbsitrSettings ? window.devbsitrSettings.default_language : 'ru';

            if (savedLang && savedLang !== defaultLang) {
                setTimeout(function() {
                    if (typeof devbsitrTranslate === 'function') {
                        devbsitrTranslate(defaultLang + '|' + savedLang);
                    }
                }, 2000);
            }
        } catch(e) {}
    });

})();
