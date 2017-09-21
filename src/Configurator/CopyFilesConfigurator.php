<?php

namespace Akeneo\Extensions\Configurator;

use Akeneo\Extensions\Recipe;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CopyFilesConfigurator extends AbstractConfigurator
{
    /**
     * @param Recipe $recipe
     * @param iterable $config
     */
    public function configure(Recipe $recipe, $config): void
    {
        $this->write('Setting configuration and copying files');
        $packageDir = $this->composer->getInstallationManager()->getInstallPath($recipe->getPackage());
        $this->copyFiles($config, $packageDir, getcwd());
    }

    /**
     * @param iterable $manifest
     * @param string $from
     * @param string $to
     */
    private function copyFiles(iterable $manifest, string $from, string $to): void
    {
        foreach ($manifest as $source => $target) {
            $target = $this->options->expandTargetDir($target);

            if ('/' === $source[-1]) {
                $this->copyDir($from . '/' . $source, $to . '/' . $target);
            } else {
                if (!is_dir(dirname($to . '/' . $target))) {
                    mkdir(dirname($to . '/' . $target), 0777, true);
                }

                if (!file_exists($to . '/' . $target)) {
                    $this->copyFile($from . '/' . $source, $to . '/' . $target);
                }
            }
        }
    }

    /**
     * @param string $source
     * @param string $target
     */
    private function copyFile(string $source, string $target): void
    {
        if (file_exists($target)) {
            return;
        }
        copy($source, $target);
        @chmod($target, fileperms($target) | (fileperms($source) & 0111));
    }

    /**
     * @param string $source
     * @param string $target
     */
    private function copyDir(string $source, string $target): void
    {
        if (!is_dir($target)) {
            mkdir($target, 0777, true);
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                if (!is_dir($new = $target . '/' . $iterator->getSubPathName())) {
                    mkdir($new);
                }
            } elseif (!file_exists($target . '/' . $iterator->getSubPathName())) {
                $this->copyFile($item, $target . '/' . $iterator->getSubPathName());
            }
        }
    }
}
