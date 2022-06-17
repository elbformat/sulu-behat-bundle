<?php

namespace Elbformat\SuluBehatBundle\DependencyInjection;

use Elbformat\SuluBehatBundle\Context\SuluArticleContext;
use Elbformat\SuluBehatBundle\Context\SuluFormContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Hannes Giesenow <hannes.giesenow@elbformat.de>
 */
class ElbformatSuluBehatExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Load only, when bundle is installed
        if (class_exists('Sulu\\Bundle\\ArticleBundle\\SuluArticleBundle')) {
            $context = new Definition(SuluArticleContext::class);
            $context->setAutoconfigured(true);
            $context->setAutowired(true);
            $context->setArgument('$esManager', new Reference('es.manager.default'));
            $container->setDefinition(SuluArticleContext::class, $context);
        }
        // Load only, when bundle is installed
        if (class_exists('Sulu\\Bundle\\FormBundle\\SuluFormBundle')) {
            $context = new Definition(SuluFormContext::class);
            $context->setAutoconfigured(true);
            $context->setAutowired(true);
            $context->setArgument('$formManager', new Reference('sulu_form.manager.form'));
            $container->setDefinition(SuluFormContext::class, $context);
        }
    }
}
