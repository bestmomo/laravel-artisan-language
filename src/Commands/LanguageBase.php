<?php

namespace Bestmomo\ArtisanLanguage\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Support\Collection;

abstract class LanguageBase extends Command
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    /**
     * Retrieves all translation keys from PHP language files.
     * Scans through files in the specified language directory and extracts translation keys.
     *
     * @param string $path Path to the language directory
     * @return array Array of translation keys
     */   
    protected function getPhpTranslationKeys()
    {
        $path = base_path("lang/{$this->argument('locale')}");
        $keys = [];

        if (!$this->filesystem->isDirectory($path)) {
            return $keys;
        }

        foreach ($this->filesystem->files($path) as $file) {
            if ($file->getExtension() === 'php') {
                $fileName = $file->getFilenameWithoutExtension();
                $fileKeys = include $file->getPathname();
                $flattenedKeys = $this->flattenKeys($fileKeys, $fileName);
                $keys = array_merge($keys, $flattenedKeys);
            }
        }

        return $keys;
    }

    /**
     * Flattens a multi-dimensional translation array into a single dimension.
     * Converts a structure like ['section' => ['key' => 'value']] to ['section.key' => 'value'].
     *
     * @param array $array Multi-dimensional array to flatten
     * @param string $prefix Prefix to add to keys (used for recursion)
     * @return array Flattened array
     */
    protected function flattenKeys(array $array, $prefix = ''): array
    {
        $keys = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;
            if (is_array($value)) {
                $keys = array_merge($keys, $this->flattenKeys($value, $newKey));
            } else {
                $keys[] = $newKey;
            }
        }
        return $keys;
    }

    /**
     * Get strings collection
     *
     * @return Collection
     */
    protected function getStrings()
    {
        $strings = collect(config('artisan-language.scan_paths', [
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
                return $out[3] ?? [];
        })
        ->collapse()
        ->unique()
        ->sort(function ($a, $b) {
            return strtolower($a) > strtolower($b);
        })
        ->values();

        $phpKeys = $this->getPhpTranslationKeys();

        $filtered = $strings->filter(function ($value) use ($phpKeys) {
            return !in_array($value, $phpKeys);
        });
    
        return $filtered;
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
            $this->info("Fichier JSON introuvable pour la locale $locale : $path");
            return collect();
        }
    
        if (!is_readable($path)) {
            $this->error("Fichier JSON non lisible pour $locale : $path");
            return collect();
        }
    
        $content = file_get_contents($path); 
        $data = json_decode($content, true);
      
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Erreur de décodage JSON pour $locale : " . json_last_error_msg());
            return collect();
        }

        return collect($data);
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
