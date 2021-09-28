<?php

declare(strict_types=1);

namespace Tests;

use Helldar\LaravelLangPublisher\Concerns\Has;
use Helldar\LaravelLangPublisher\Concerns\Paths;
use Helldar\LaravelLangPublisher\Constants\Config;
use Helldar\LaravelLangPublisher\Constants\Locales;
use Helldar\LaravelLangPublisher\Constants\Locales as LocalesList;
use Helldar\LaravelLangPublisher\ServiceProvider;
use Helldar\Support\Facades\Helpers\Filesystem\Directory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Tests\Providers\HttpStatusesProvider;

abstract class TestCase extends BaseTestCase
{
    use Has;
    use Paths;

    protected $default = Locales::ENGLISH;

    protected $fallback = Locales::GERMAN;

    protected $locale = Locales::ALBANIAN;

    protected $locales = [
        LocalesList::BULGARIAN,
        LocalesList::DANISH,
        LocalesList::GALICIAN,
        LocalesList::ICELANDIC,
    ];

    protected $inline = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reinstallLocales();
    }

    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $app['config'];

        $config->set('app.locale', $this->default);
        $config->set('app.fallback_locale', $this->fallback);

        $config->set(Config::PUBLIC_KEY . '.inline', $this->inline);

        $config->set(Config::PRIVATE_KEY . '.path.base', realpath(__DIR__ . '/../vendor'));

        $config->set(Config::PUBLIC_KEY . '.excludes', [
            'http-statuses' => ['unknownError', '0', '100', '101', '102'],
        ]);

        $config->set(Config::PUBLIC_KEY . '.plugins', [
            HttpStatusesProvider::class,
        ]);
    }

    protected function copyFixtures(): void
    {
        $filename = 'http-statuses.php';

        $from = realpath(__DIR__ . '/fixtures/' . $filename);

        File::copy($from, $this->resourcesPath($this->default . '/' . $filename));
    }

    protected function refreshLocales(): void
    {
        app('translator')->setLoaded([]);
    }

    protected function reinstallLocales(): void
    {
        $this->deleteLocales();
        $this->installLocales();
    }

    protected function deleteLocales(): void
    {
        $path = $this->resourcesPath();

        Directory::ensureDelete($path);
    }

    protected function installLocales(): void
    {
        Artisan::call('lang:add', [
            'locales' => [$this->default, $this->fallback],
        ]);
    }
}