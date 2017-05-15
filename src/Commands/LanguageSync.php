<?php

namespace Bestmomo\ArtisanLanguage\Commands;

class LanguageSync extends LanguageBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:sync 
                            {locale : Locale} 
                            {--nomissing : Skip missing strings}
                            {--nofurther : Skip further strings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise differences for the locale';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Locale
        $locale = $this->argument('locale');

        // Locale strings
        $localeStrings = $this->getLocaleStrings($locale);

        // Synchronisation
        if ($localeStrings) {

            // Get strings with empty value
            $strings = $this->getStringsEmpty();

            // Missing strings
            if (!$this->option('nomissing')) {
                $missing = $strings->diffKeys($localeStrings);
                if($missing->isNotEmpty()) {
                    $merged = $missing->merge($localeStrings)->toArray();
                    ksort($merged, SORT_NATURAL | SORT_FLAG_CASE);
                    $this->updateFile(collect($merged), $locale);
                    $localeStrings = $this->getLocaleStrings($locale);
                    $this->info('File successfully synchronised for missing strings');
                } else {
                    $this->info('No missing strings for this locale.');
                }
            }

            // Further strings
            if (!$this->option('nofurther')) {
                $further = $localeStrings->diffKeys($strings);
                if($further->isNotEmpty()) {
                    $result = $localeStrings->except($further->keys()->all());
                    $this->updateFile($result, $locale);
                    $this->info('File successfully synchronised for further strings');
                } else {
                    $this->info('No further strings for this locale.');
                }
            }
        }
    }
}
