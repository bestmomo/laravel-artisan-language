<?php

namespace Bestmomo\ArtisanLanguage\Commands;

class LanguageStrings extends LanguageBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:strings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all default language strings';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line(PHP_EOL);
        $this->display($this->getStrings());
    }
}
