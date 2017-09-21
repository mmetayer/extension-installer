<?php

namespace Akeneo\Extensions;

use Composer\Composer;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Installer implements PluginInterface, EventSubscriberInterface
{
    const AKENEO_EXTENSION_TYPE = 'akeneo-extension';

    /** @var  Composer */
    private $composer;

    /** @var  IOInterface */
    private $io;

    /** @var Options */
    private $options;

    /** @var Configurator */
    private $configurator;

    /** @var Recipe[] */
    private $recipes = [];

    /** @var  string */
    private $originalLockHash;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->options = $this->initOptions();
        $this->configurator = new Configurator($composer, $io, $this->options);
        $this->updateOriginalLockHash();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::PRE_INSTALL_CMD => 'prepare',
            ScriptEvents::PRE_UPDATE_CMD => 'prepare',
            PackageEvents::POST_PACKAGE_INSTALL => 'register',
            PackageEvents::POST_PACKAGE_UPDATE => 'register',
            ScriptEvents::POST_INSTALL_CMD => 'update',
            ScriptEvents::POST_UPDATE_CMD => 'update',
        ];
    }

    /**
     * @param Event $event
     */
    public function prepare(Event $event): void
    {
        if (!$this->originalLockHash) {
            $this->updateOriginalLockHash();
        }
    }

    /**
     * @param PackageEvent $event
     */
    public function register(PackageEvent $event): void
    {
        $operation = $event->getOperation();
        if ($operation instanceof UpdateOperation) {
            $package = $operation->getTargetPackage();
        } else {
            $package = $operation->getPackage();
        }

        if (self::AKENEO_EXTENSION_TYPE !== $package->getType()) {
            return;
        }

        $installPath = $this->composer->getInstallationManager()->getInstallPath($package);
        if (file_exists($installPath . '/manifest.json')) {
            $file = new JsonFile($installPath . '/manifest.json');
            $manifest = $file->read();
            $this->recipes[] = new Recipe($package, $manifest);
        }
    }

    /**
     * @param Event $event
     */
    public function update(Event $event): void
    {
        if ($this->originalLockHash === $this->composer->getLocker()->getLockData()['content-hash']) {
            return;
        }

        foreach ($this->recipes as $recipe) {
            $this->io->write(sprintf('  - Configuring %s', $recipe->getPackage()->getName()));
            $this->configurator->install($recipe);
        }
    }

    private function updateOriginalLockHash(): void
    {
        $locker = $this->composer->getLocker();
        if ($locker && $locker->isLocked()) {
            $this->originalLockHash = $locker->getLockData()['content-hash'];
        }
    }

    /**
     * @return Options
     */
    private function initOptions(): Options
    {
        $options = array_merge([
            'bin-dir' => 'bin',
            'config-dir' => 'app/config',
            'var-dir' => 'var',
            'public-dir' => 'public',
        ], $this->composer->getPackage()->getExtra());

        return new Options($options);
    }
}
