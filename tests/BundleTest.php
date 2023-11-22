<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\Tests;

use Elbformat\SuluBehatBundle\ElbformatSuluBehatBundle;
use Elbformat\SymfonyBehatBundle\DependencyInjection\DynamicServicesPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
class BundleTest extends TestCase
{
    public function testBuild(): void
    {
        $bundle = new ElbformatSuluBehatBundle();
        $container = $this->createMock(ContainerBuilder::class);
        //        $container->expects($this->once())->method('addCompilerPass')->with($this->isInstanceOf(DynamicServicesPass::class))->willReturn(null);
        $bundle->build($container);
    }
}
