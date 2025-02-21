<?php

/**
 * Copyright Youwe. All rights reserved.
 * https://www.youweagency.com
 */

declare(strict_types=1);

namespace Youwe\Composer\DependencyInstaller\Tests;

use AllowDynamicProperties;
use Composer\Json\JsonFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\MockObject\Exception;
use Seld\JsonLint\ParsingException;
use Youwe\Composer\DependencyInstaller\DependencyInstaller;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(DependencyInstaller::class)]
#[CoversMethod(DependencyInstaller::class, '__construct')]
#[CoversMethod(DependencyInstaller::class, 'installPackage')]
#[CoversMethod(DependencyInstaller::class, 'installRepository')]
#[AllowDynamicProperties]
class DependencyInstallerTest extends TestCase
{
    /** @var string */
    private static string $directory = __DIR__ . DIRECTORY_SEPARATOR . 'tmp';

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        mkdir(static::$directory);
        chdir(static::$directory);
        file_put_contents('composer.json', '{}');

        $this->dependencyInstaller = new DependencyInstaller(
            'composer.json',
            $this->createMock(OutputInterface::class)
        );
    }

    protected function tearDown(): void
    {
        if (is_dir(static::$directory)) {
            static::rrmdir(static::$directory);
        }
    }

    /**
     * @param string $src
     *
     * @return void
     */
    private static function rrmdir(string $src)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $full = $src . '/' . $file;
                if (is_dir($full)) {
                    static::rrmdir($full);
                } else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }


    /**
     * @return void
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            DependencyInstaller::class,
            $this->dependencyInstaller
        );
    }

    /**
     * @param string $name
     * @param string $type
     * @param string $url
     *
     * @return void
     * @throws ParsingException
     */
    #[Depends('testConstructor')]
    #[DataProvider('repositoryProvider')]
    public function testInstallRepository(
        string $name,
        string $type,
        string $url,
    ) {
        $this->dependencyInstaller->installRepository($name, $type, $url);

        $jsonFile   = new JsonFile('composer.json');
        $definition = $jsonFile->read();

        $this->assertArrayHasKey('repositories', $definition);
        $this->assertArrayHasKey($name, $definition['repositories']);
        $this->assertArrayHasKey('type', $definition['repositories'][$name]);
        $this->assertArrayHasKey('url', $definition['repositories'][$name]);
        $this->assertEquals($type, $definition['repositories'][$name]['type']);
        $this->assertEquals($url, $definition['repositories'][$name]['url']);
    }

    /**
     * @return array
     */
    public static function repositoryProvider(): array
    {
        return [
            ['mediact', 'composer', 'https://composer.mediact.nl']
        ];
    }

    /**
     * @param string $name
     * @param string $version
     * @param bool $dev
     * @param bool $updateDependencies
     * @param bool $allowOverrideVersion
     *
     * @return void
     * @throws ParsingException
     */
    #[DataProvider('packageProvider')]
    #[Depends('testConstructor')]
    #[Depends('testInstallRepository')]
    public function testInstallPackage(
        string $name,
        string $version,
        bool $dev,
        bool $updateDependencies,
        bool $allowOverrideVersion
    ) {
        $this->dependencyInstaller->installPackage($name, $version, $dev, $updateDependencies, $allowOverrideVersion);

        $jsonFile   = new JsonFile('composer.json');
        $definition = $jsonFile->read();

        $node = $dev ? 'require-dev' : 'require';

        $this->assertArrayHasKey($node, $definition);
        $this->assertArrayHasKey($name, $definition[$node]);
        $this->assertEquals($version, $definition[$node][$name]);
    }

    /**
     * @return array
     */
    public static function packageProvider(): array
    {
        return [
            ['psr/log', '@stable', true, false, true],
            ['psr/log', '@stable', false, false, false]
        ];
    }
}
