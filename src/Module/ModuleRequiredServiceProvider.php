<?php

namespace Noking50\Modules\Required;

use Illuminate\Support\ServiceProvider;

class ModuleRequiredServiceProvider extends ServiceProvider {

    public function boot() {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'module_required');
        $this->publishes([
            __DIR__ . '/../config/language.php' => config_path('language.php'),
            __DIR__ . '/../lang' => resource_path('lang/vendor/module_required'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(
                __DIR__ . '/../config/language.php', 'language'
        );
    }

}
