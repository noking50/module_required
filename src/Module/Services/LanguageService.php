<?php

namespace Noking50\Modules\Required\Services;

class LanguageService {

    public function __construct() {
    }

    public function getListFormChoose($choose_lang = []) {
        $form_choose_lang = [];
        $lang_default = config('app.fallback_locale');
        $lang_current = config('app.locale');
        foreach (config('language', []) as $k => $v) {
            $default = ($k == $lang_default);
            $current = ($k == $lang_current);
            $choose = in_array($k, $choose_lang) || $default || $current;
            $form_choose_lang[$k] = [
                'name' => $v['name'],
                'default' => $default,
                'choose' => $choose,
            ];
        }

        return $form_choose_lang;
    }

}
