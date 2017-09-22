<?php

namespace Akeneo\Extensions\Configurator;

use Akeneo\Extensions\Recipe;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BundlesConfigurator extends AbstractConfigurator
{
    /**
     * @param Recipe $recipe
     * @param iterable $bundles
     */
    public function configure(Recipe $recipe, $bundles): void
    {
        $this->write('Enabling the extension as a Symfony bundle');
        $file = $this->getConfFile();
        $registered = $this->load($file);
        $classes = $this->parse($bundles, $registered);

        if (isset($classes[$fwb = 'Symfony\Bundle\FrameworkBundle\FrameworkBundle'])) {
            foreach ($classes[$fwb] as $env) {
                $registered[$fwb][$env] = true;
            }
            unset($classes[$fwb]);
        }
        foreach ($classes as $class => $envs) {
            foreach ($envs as $env) {
                $registered[$class][$env] = true;
            }
        }

        $this->dump($file, $registered);
    }

    /**
     * @param Recipe $recipe
     * @param iterable $bundles
     */
    public function unconfigure(Recipe $recipe, $bundles): void
    {
        $this->write('Disabling the Symfony bundle');
        $file = $this->getConfFile();
        if (!file_exists($file)) {
            return;
        }

        $registered = $this->load($file);
        foreach (array_keys($this->parse($bundles, [])) as $class) {
            unset($registered[$class]);
        }
        $this->dump($file, $registered);
    }

    /**
     * @param iterable $manifest
     * @param iterable $registered
     * @return iterable
     */
    private function parse(iterable $manifest, iterable $registered): iterable
    {
        $bundles = [];
        foreach ($manifest as $class => $envs) {
            if (!isset($registered[$class])) {
                $bundles[ltrim($class, '\\')] = $envs;
            }
        }

        return $bundles;
    }

    /**
     * @param string $file
     * @return iterable
     */
    private function load(string $file): iterable
    {
        $bundles = file_exists($file) ? (require $file) : [];
        if (!is_array($bundles)) {
            $bundles = [];
        }

        return $bundles;
    }

    /**
     * @param string $file
     * @param iterable $bundles
     */
    private function dump(string $file, iterable $bundles): void
    {
        $contents = "<?php\n\nreturn [\n";
        foreach ($bundles as $class => $envs) {
            $contents .= "    '$class' => [";
            foreach (array_keys($envs) as $env) {
                $contents .= "'$env' => true, ";
            }
            $contents = substr($contents, 0, -2) . "],\n";
        }
        $contents .= "];\n";

        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }

        file_put_contents($file, $contents);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file);
        }
    }

    /**
     * @return string
     */
    private function getConfFile(): string
    {
        return getcwd() . '/app/bundles.php';
    }
}
