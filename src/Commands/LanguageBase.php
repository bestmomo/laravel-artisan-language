<?php

namespace Bestmomo\ArtisanLanguage\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\Translator;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Support\Collection;

abstract class LanguageBase extends Command
{
    /**
     * The translator instance.
     *
     * @var Translator
     */
    protected $translator;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Create a new command instance.
     *
     * @param Translator $translator
     * @param Filesystem $filesystem
     */
    public function __construct(Translator $translator, Filesystem $filesystem)
    {
        parent::__construct();

        $this->translator = $translator;
        $this->filesystem = $filesystem;
    }

    /**
     * Get strings collection
     *
     * @return Collection
     */
    protected function getStrings()
    {
        return collect(config('artisan-language.scan_paths', [
            app_path(),
            resource_path('views'),
            resource_path('js'),
        ]))
            ->map(function (string $path) {
                return $this->filesystem->allFiles($path);
            })
            ->collapse()
            ->map(function (SplFileInfo $item) {
                preg_match_all(
                    config ('artisan-language.scan_pattern',
                      '/(@lang|__|\$t|\$tc)\s*(\(\s*[\'"])([^$]*)([\'"]+\s*(,[^\)]*)*\))/U'),
                    $item->getContents(),
                    $out,
                    PREG_PATTERN_ORDER);
                return $out[3];
            })
            ->collapse()
            ->unique()
            ->filter(function ($value) {
                return !$this->translator->has($value);
            })
            ->sort(function ($a, $b) {
                return strtolower($a) > strtolower($b);
            });
    }

    /**
     * Get strings with empty value
     *
     * @return Collection
     */
    protected function getStringsEmpty()
    {
        return $this->getStrings()
            ->mapWithKeys(function ($item) {
                return [$item => ''];
            });
    }

    /**
     * Get locale strings
     *
     * @param  string $locale
     * @return Collection | boolean
     */
    protected function getLocaleStrings($locale)
    {
        $path = $this->getPath($locale);

        if (!$this->filesystem->exists($path)) {
            $this->info('File doesn\'t exist for this locale');
            return false;
        }

        return collect(json_decode(file_get_contents($path), true));
    }

    /**
     * Display strings
     *
     * @param Collection $strings
     */
    protected function display(Collection $strings)
    {
        $strings->each(function ($value) {
            echo $value . PHP_EOL;
        });
    }

    /**
     * Get path for locale file
     *
     * @param  string $locale
     * @return string
     */
    protected function getPath($locale)
    {
        return config('artisan-language.lang_path', base_path('lang')) . '/' . $locale . '.json';
    }

    /**
     * Update or create File
     *
     * @param Collection $strings
     * @param string $locale
     */
    protected function updateFile(Collection $strings, $locale)
    {
        $this->filesystem->put($this->getPath($locale), $strings->toJSON(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
