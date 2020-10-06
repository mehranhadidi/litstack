<?php

namespace Ignite\Translation;

use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register litstack translator.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('lit.translator', function () {
            $translator = new Translator();

            $translator->addPath(base_path('vendor/litstack/lang/src'));

            return $translator;
        });
    }
}
