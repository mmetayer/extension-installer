<?php

namespace Akeneo\Extensions;

use Composer\Package\PackageInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Recipe
{
    /** @var PackageInterface */
    private $package;

    /** @var iterable */
    private $manifest;

    /**
     * Recipe constructor
     * @param PackageInterface $package
     * @param iterable $manifest
     */
    public function __construct(PackageInterface $package, $manifest)
    {
        $this->package = $package;
        $this->manifest = $manifest;
    }

    /**
     * @return PackageInterface
     */
    public function getPackage(): PackageInterface
    {
        return $this->package;
    }

    /**
     * @return iterable
     */
    public function getManifest(): iterable
    {
        return $this->manifest;
    }
}
