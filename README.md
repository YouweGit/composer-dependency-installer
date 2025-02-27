# composer-dependency-installer

PHP package for installing Composer's dependencies upstream.

## Usage
To install a package in upstream software requiring this package, instantiate a new instance and call the install() function.

## Usage reference
```php
<?php

declare(strict_types=1);

namespace Acme\Foo;

class Bar
{
    public function __construct(
        private readonly DependencyInstaller $dependencyInstaller
    ) {}
    
    public function installUpstreamRepository(): void
    {
        $this->dependencyInstaller->installRepository(
            name: 'some/package',
            type: 'composer',
            url: 'mypackagedomain.dev'
        );
    }
    
    public function installUpstreamPackage(): void
    {
        $this->dependencyInstaller->installPackage(
            name: 'some/package',
            version: '^2.0.0',
            dev: true, // Whether it should be a dev dependency (e.g. require-dev). Optional, defaults to true
            updateDependencies: false, // Whether dependencies can be updated. Optional, defaults to true. When enabled, passes -W flag to composer
            allowOverrideVersion: true // Whether version can be updated with new version when package is altready installed upstream. Optional, defaults to true.
        )
    }
}
```