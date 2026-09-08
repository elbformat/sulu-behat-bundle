<?php

$config = new PhpCsFixer\Config();
$config->setRules([
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'declare_strict_types' => true,
    'strict_comparison' => true,
    'modernize_types_casting' => true
]);
$config->setRiskyAllowed(true);
$config->setFinder(
    PhpCsFixer\Finder::create()
        ->in(__DIR__ . DIRECTORY_SEPARATOR . 'src')
        ->files()->name('*.php')
);

return $config;
