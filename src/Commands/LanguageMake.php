<?php

namespace Bestmomo\ArtisanLanguage\Commands;

class LanguageMake extends LanguageBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'language:make 
                            {locale : Locale} 
                            {--force : Replace existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new json language file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $locale = $this->argument('locale');

        // Doesn't create file if it exists and no --force option is set
        if ($this->filesystem->exists($this->getPath($locale)) && !$this->option('force')) {
            $this->info('File already exists. Use --force option to replace it');
            return;
        }

        // Create file
        $this->updateFile($this->getStringsEmpty(), $locale);
        $this->info('File successfully created');
    }
}
