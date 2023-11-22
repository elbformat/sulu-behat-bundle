# sulu-behat-bundle

Adds Contexts and Helper to easily set-up behat tests in your sulu application.
It makes use of the [symfony-behat-bundle](https://github.com/elbformat/symfony-behat-bundle).

## Installation

1. Add the bundle via composer
```console
composer require elbformat/sulu-behat-bundle
```

2. Activate bundles in `config/bundles.php`
```php
Elbformat\SymfonyBehatBundle\ElbformatSymfonyBehatBundle::class => ['test' => true],
Elbformat\SuluBehatBundle\ElbformatSuluBehatBundle::class => ['test' => true],
```

3. Configure behat Extensions

Add the extension to your `behat.yml`. With the `context` parameter you can decide if the sulu kernel for this profile is
running in `website` or `admin` (default) context. It's recommended to use tags to sort features into suites.
Also make sure the [symfony extension](https://github.com/FriendsOfBehat/SymfonyExtension) is enabled and configured.

**NOTE**: The `FriendsOfBehat\SymfonyExtension` must be placed *before* the `SuluExtension`.

You can then add Contexts as you like/need.

```yml
default:
  suites:
    default:
      filters:
        tags: '~@admin'
      contexts:
        - Elbformat\SymfonyBehatBundle\Context\CommandContext:
        - Elbformat\SymfonyBehatBundle\Context\LoggingContext:
        - Elbformat\SuluBehatBundle\Context\BrowserContext:
        - Elbformat\SuluBehatBundle\Context\DateContext:
        - Elbformat\SuluBehatBundle\Context\SuluPageContext:
        - Elbformat\SuluBehatBundle\Context\SuluSnippetContext:
        - Elbformat\SuluBehatBundle\Context\SuluMediaContext:
        # Only enable, when you have the according bundle installed
        #- Elbformat\SuluBehatBundle\Context\SuluArticleContext:
        #- Elbformat\SuluBehatBundle\Context\SuluFormContext:
  extensions:
    FriendsOfBehat\SymfonyExtension: ~
    Elbformat\SuluBehatBundle\SuluExtension:
      context: website
admin:
  suites:
    default:
      filters:
        tags: '@admin'
  extensions:
    FriendsOfBehat\SymfonyExtension:
      bootstrap: 'tests/bootstrap.php'
    Elbformat\SuluBehatBundle\SuluExtension:
      context: admin
```

## Run tests
Make sure you have a database configured for the test environment.
It's recommended to have an extra database configured for tests in `.env.test`, to not accidentally delete real contents.
After configuration you should initialise it once, before running any test against it.

```shell
bin/console -e test sulu:build prod
```

You can then run the tests in default oder admin profile.
```shell
vendor/bin/behat
vendor/bin/behat --profile admin
```

## Examples
First you should take a look at the [symfony examples](https://github.com/elbformat/symfony-behat-bundle/blob/main/doc/examples.md). More sulu specific examples can be found in [features/ folder](https://github.com/elbformat/sulu-behat-bundle/features).

## Recommended bundles
There are contexts, that can ony be enabled when the according bundles are installed.
* SuluArticleContext requires [SuluArticleBundle](https://github.com/sulu/SuluArticleBundle)
* SuluFormContext requires [SuluFormBundle](https://github.com/sulu/SuluFormBundle)

## What's next?
Possible enhancements for the next release could be
* SuluCommunityContext for SuluCommunityBundle
* More examples with more content-types