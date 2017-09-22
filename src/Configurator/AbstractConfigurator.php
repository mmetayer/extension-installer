<?php

namespace Akeneo\Extensions\Configurator;

use Akeneo\Extensions\Options;
use Akeneo\Extensions\Recipe;
use Composer\Composer;
use Composer\IO\IOInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractConfigurator
{
    /** @var Composer */
    protected $composer;

    /** @var IOInterface */
    protected $io;

    /** @var Options */
    protected $options;

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
    }

    /**
     * @param Recipe $recipe
     * @param $config
     */
    abstract public function configure(Recipe $recipe, $config): void;

    /**
     * @param Recipe $recipe
     * @param $config
     */
    abstract public function unconfigure(Recipe $recipe, $config): void;

    /**
     * @param string|string[] $messages
     */
    protected function write($messages): void
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        foreach ($messages as $i => $message) {
            $messages[$i] = '    ' . $message;
        }
        $this->io->writeError($messages, true, IOInterface::VERBOSE);
    }
}
