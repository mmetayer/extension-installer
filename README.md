# Akeneo PIM Extension Installer

Composer plugin for Akeneo PIM extensions and connectors, largely inspired by [Symfony Flex][1]

## Warning
As symfony Flex now handles private repos or even recipes downloads from custom URIs (see [https://github.com/symfony/flex/blob/73436bc5c0e3b63f5655552d580b48ad0e17543f/src/Downloader.php#L46-L49], this plugin has lost most of its added value

### Installation

```
   $ php composer.phar config repositories.extensionInstaller '{"type": "vcs", "url": "git@github.com:mmetayer/extension-installer.git", "branch": "dev-master"}'
   $ php composer.phar require "mmetayer/extension-installer" "dev-master"
```

### Usage

The plugin will try to configure every bundle of type "akeneo-extension" installed by composer.
In order to do that, the extension must have a file named **manifest.json** at its root folder,
where the configuration steps are described.

Currently, it only allows two configurators: 
* ***bundles***: registers the bundle inside the Kernel
* ***copy-files*** copy files from the extension to the main application (routing, services...)

A full example is available [here][2]

[1]: https://github.com/symfony/flex
[2]: https://github.com/mmetayer/FakeBundle

# TODO
- [ ] Add configurators
- [x] Handle uninstallation
