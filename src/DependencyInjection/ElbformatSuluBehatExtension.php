<?php

declare(strict_types=1);

namespace Elbformat\SuluBehatBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ElbformatSuluBehatExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Load only, when bundle is installed
        if (class_exists('Sulu\\Bundle\\FormBundle\\SuluFormBundle')) {
            $loader->load('sulu_form.yml');
        }
    }
}
