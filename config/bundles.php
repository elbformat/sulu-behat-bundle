<?php
# @todo minimize dependencies to let tests run.
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Sulu\Bundle\CoreBundle\SuluCoreBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle::class => ['all' => true],
    HandcraftedInTheAlps\RestRoutingBundle\RestRoutingBundle::class => ['all' => true],
    JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true],
    Massive\Bundle\SearchBundle\MassiveSearchBundle::class => ['all' => true],
    Sulu\Bundle\AdminBundle\SuluAdminBundle::class => ['all' => true],
    Sulu\Bundle\SearchBundle\SuluSearchBundle::class => ['all' => true],
    Sulu\Bundle\PersistenceBundle\SuluPersistenceBundle::class => ['all' => true],
    Sulu\Bundle\ContactBundle\SuluContactBundle::class => ['all' => true],
    Sulu\Bundle\MediaBundle\SuluMediaBundle::class => ['all' => true],
    Sulu\Bundle\SecurityBundle\SuluSecurityBundle::class => ['all' => true],
    Sulu\Bundle\CategoryBundle\SuluCategoryBundle::class => ['all' => true],
    Sulu\Bundle\SnippetBundle\SuluSnippetBundle::class => ['all' => true],
    Sulu\Bundle\PageBundle\SuluPageBundle::class => ['all' => true],
    Sulu\Bundle\TagBundle\SuluTagBundle::class => ['all' => true],
    Sulu\Bundle\WebsiteBundle\SuluWebsiteBundle::class => ['all' => true],
    Sulu\Bundle\LocationBundle\SuluLocationBundle::class => ['all' => true],
    Sulu\Bundle\HttpCacheBundle\SuluHttpCacheBundle::class => ['all' => true],
    Sulu\Bundle\DocumentManagerBundle\SuluDocumentManagerBundle::class => ['all' => true],
    Sulu\Bundle\HashBundle\SuluHashBundle::class => ['all' => true],
    Sulu\Bundle\CustomUrlBundle\SuluCustomUrlBundle::class => ['all' => true],
    Sulu\Bundle\RouteBundle\SuluRouteBundle::class => ['all' => true],
    Sulu\Bundle\MarkupBundle\SuluMarkupBundle::class => ['all' => true],
    DTL\Bundle\PhpcrMigrations\PhpcrMigrationsBundle::class => ['all' => true],
    Massive\Bundle\BuildBundle\MassiveBuildBundle::class => ['all' => true],
    Sulu\Bundle\TestBundle\SuluTestBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Sulu\Bundle\PreviewBundle\SuluPreviewBundle::class => ['all' => true],
    Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle::class => ['all' => true],
    Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],
    Sulu\Bundle\ActivityBundle\SuluActivityBundle::class => ['all' => true],
    Sulu\Bundle\TrashBundle\SuluTrashBundle::class => ['all' => true],
    FOS\RestBundle\FOSRestBundle::class => ['all' => true],
    FOS\JsRoutingBundle\FOSJsRoutingBundle::class => ['all' => true],
    FOS\HttpCacheBundle\FOSHttpCacheBundle::class => ['all' => true],
    # Article Bundle
    ONGR\ElasticsearchBundle\ONGRElasticsearchBundle::class => ['all' => true],
    Sulu\Bundle\ArticleBundle\SuluArticleBundle::class => ['all' => true],
    # Form Bundle
    Sulu\Bundle\FormBundle\SuluFormBundle::class => ['all' => true],
    # Behat
    FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle::class => ['test' => true],
    Elbformat\SymfonyBehatBundle\ElbformatSymfonyBehatBundle::class => ['test' => true],
    Elbformat\SuluBehatBundle\ElbformatSuluBehatBundle::class => ['test' => true],
];
