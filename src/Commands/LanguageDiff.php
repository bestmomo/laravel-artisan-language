<?php

namespace Bestmomo\ArtisanLanguage\Commands;

use Illuminate\Support\Collection;

class LanguageDiff extends LanguageBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:diff {locale : Locale}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show differences with locale';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get strings
        $strings = $this->getStrings();

        // Get locale strings
        $localeStrings = $this->getLocaleStrings($this->argument('locale'));

        // Differences
        if ($localeStrings) {
            $localeStrings = $localeStrings->keys();
            // Missing strings
            $this->diff($strings, $localeStrings, 'Missing');
            // Further strings
            $this->diff($localeStrings, $strings, 'Further');
        }
    }

    /**
     * Show differences
     *
     * @param Collection $first
     * @param Collection $last
     * @param string $type
     */
    protected function diff(Collection $first, Collection $last, $type)
    {
        $diff = $first->diff($last);
        $this->separation();

        if($diff->isNotEmpty()) {
            $this->info("$type strings for this locale");
            $this->separation();
            $this->display($diff);
            return;
        }

        $this->info("No $type strings for this locale");
        $this->separation();
    }

    /**
     * Display a line
     *
     * @return string
     */
    protected function separation()
    {
        return $this->info('-----------------------------------');
    }
}
