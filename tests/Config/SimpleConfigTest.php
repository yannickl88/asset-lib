<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Resolver\Config;

use Hostnet\Component\Resolver\Import\Nodejs\Executable;
use Hostnet\Component\Resolver\Plugin\PluginInterface;
use Hostnet\Component\Resolver\Report\ConsoleLoggingReporter;
use Hostnet\Component\Resolver\Report\Helper\FileSizeHelper;
use Hostnet\Component\Resolver\Report\NullReporter;
use Hostnet\Component\Resolver\Split\OneOnOneSplittingStrategy;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @covers \Hostnet\Component\Resolver\Config\SimpleConfig
 */
class SimpleConfigTest extends TestCase
{
    public function testGeneric(): void
    {
        $plugins = [$this->prophesize(PluginInterface::class)->reveal()];
        $nodejs  = new Executable('a', 'b');
        $config  = new SimpleConfig(
            true,
            __DIR__,
            ['phpunit'],
            ['foo'],
            ['bar'],
            'web',
            'phpunit',
            'src',
            'var',
            $plugins,
            $nodejs
        );

        self::assertEquals(true, $config->isDev());
        self::assertEquals(__DIR__, $config->getProjectRoot());
        self::assertEquals(['phpunit'], $config->getIncludePaths());
        self::assertEquals(['foo'], $config->getEntryPoints());
        self::assertEquals(['bar'], $config->getAssetFiles());
        self::assertEquals('web' . DIRECTORY_SEPARATOR . 'phpunit', $config->getOutputFolder());
        self::assertEquals('phpunit', $config->getOutputFolder(false));
        self::assertEquals('src', $config->getSourceRoot());
        self::assertEquals('var', $config->getCacheDir());
        self::assertSame($plugins, $config->getPlugins());
        self::assertSame($nodejs, $config->getNodeJsExecutable());
        self::assertInstanceOf(NullLogger::class, $config->getLogger());
        self::assertInstanceOf(NullReporter::class, $config->getReporter());

        $reporter = new ConsoleLoggingReporter($config, new NullOutput(), new FileSizeHelper());
        $old      = $config->replaceReporter($reporter);

        self::assertInstanceOf(NullReporter::class, $old);
        self::assertSame($reporter, $config->getReporter());

        $plugins = [$this->prophesize(PluginInterface::class)->reveal()];
        $nodejs  = new Executable('a', 'b');
        $config  = new SimpleConfig(
            true,
            __DIR__,
            ['phpunit'],
            ['foo'],
            ['bar'],
            'web',
            'phpunit',
            'src',
            'var',
            $plugins,
            $nodejs
        );

        self::assertEquals(true, $config->isDev());
        self::assertEquals(__DIR__, $config->getProjectRoot());
        self::assertEquals(['phpunit'], $config->getIncludePaths());
        self::assertEquals(['foo'], $config->getEntryPoints());
        self::assertEquals(['bar'], $config->getAssetFiles());
        self::assertEquals('web' . DIRECTORY_SEPARATOR . 'phpunit', $config->getOutputFolder());
        self::assertEquals('phpunit', $config->getOutputFolder(false));
        self::assertEquals('src', $config->getSourceRoot());
        self::assertEquals('var', $config->getCacheDir());
        self::assertSame($plugins, $config->getPlugins());
        self::assertSame($nodejs, $config->getNodeJsExecutable());
        self::assertInstanceOf(NullLogger::class, $config->getLogger());
        self::assertInstanceOf(OneOnOneSplittingStrategy::class, $config->getSplitStrategy());

        $logger         = new NullLogger();
        $split_strategy = new OneOnOneSplittingStrategy();
        $config         = new SimpleConfig(
            true,
            __DIR__,
            ['phpunit'],
            ['foo'],
            ['bar'],
            'web',
            'phpunit',
            'src',
            'var',
            $plugins,
            $nodejs,
            $logger,
            null,
            $split_strategy
        );

        self::assertSame($logger, $config->getLogger());
        self::assertSame($split_strategy, $config->getSplitStrategy());
    }
}
