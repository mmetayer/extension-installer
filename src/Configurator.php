<?php

namespace Akeneo\Extensions;

use Akeneo\Extensions\Configurator\AbstractConfigurator;
use Composer\Composer;
use Composer\IO\IOInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configurator
{
    /** @var Composer */
    private $composer;

    /** @var IOInterface */
    private $io;

    /** @var Options */
    private $options;

    /** @var array */
    private $configurators;

    /** @var AbstractConfigurator[] */
    private $cache;

    /**
     * @param Composer $composer
     * @param IOInterface $io
     * @param Options $options
     */
    public function __construct(Composer $composer, IOInterface $io, Options $options)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->options = $options;
        // ordered list of configurators
        $this->configurators = [
            'bundles' => Configurator\BundlesConfigurator::class,
            'copy-files' => Configurator\CopyFilesConfigurator::class,
        ];
    }

    /**
     * @param Recipe $recipe
     */
    public function install(Recipe $recipe): void
    {
        $manifest = $recipe->getManifest();
        foreach (array_keys($this->configurators) as $key) {
            if (isset($manifest[$key])) {
                $this->get($key)->configure($recipe, $manifest[$key]);
            }
        }
    }

    /**
     * @param Recipe $recipe
     */
    public function uninstall(Recipe $recipe): void
    {
        $manifest = $recipe->getManifest();
        foreach (array_keys($this->configurators) as $key) {
            if (isset($manifest[$key])) {
                $this->get($key)->unconfigure($recipe, $manifest[$key]);
            }
        }
    }

    /**
     * @param $key
     * @return AbstractConfigurator
     */
    private function get($key): AbstractConfigurator
    {
        if (!isset($this->configurators[$key])) {
            throw new \InvalidArgumentException(sprintf('Unknown configurator "%s".', $key));
        }

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $class = $this->configurators[$key];

        return $this->cache[$key] = new $class($this->composer, $this->io, $this->options);
    }
}
