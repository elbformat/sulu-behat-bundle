<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * This extension helps putting the kernel into the correct "suluContext".
 *
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
final class SuluExtension implements Extension
{
    public const KERNEL_ID = 'fob_symfony.kernel';

    public function getConfigKey(): string
    {
        return 'sulu_behat';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function process(ContainerBuilder $container)
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('context')->defaultNull()->end()
            ->end();
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadKernel($container, $config);
    }

    private function loadKernel(ContainerBuilder $container, array $config): void
    {
        try {
            $def = $container->getDefinition(self::KERNEL_ID);
        } catch (ServiceNotFoundException $e) {
            throw new \RuntimeException('Could not find '.self::KERNEL_ID.'. Make sure you have the FriendsOfBehat\SymfonyExtension configured and placed *before* this extension.');
        }
        $def->setArgument('$suluContext', $config['context'] ?? null);
    }
}
