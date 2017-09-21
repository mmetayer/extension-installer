<?php

namespace Akeneo\Extensions;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Options
{
    /** @var array */
    private $options;

    /**
     * Options constructor
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param string $name
     * @return null|string
     */
    public function get(string $name): ?string
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @param string $target
     * @return string
     */
    public function expandTargetDir(string $target): string
    {
        return preg_replace_callback('{%(.+?)%}', function ($matches) {
            $option = str_replace('_', '-', strtolower($matches[1]));
            if (!isset($this->options[$option])) {
                return $matches[0];
            }

            return rtrim($this->options[$option], '/');
        }, $target);
    }
}
