# sulu-behat-bundle

Adds Contexts and Helper to easily set-up behat tests in your sulu application.

## Installation

### Add the bundle via composer

```console
$ composer require elbformat/sulu-behat-bundle
```

### Activate bundle

Add

```php
Elbformat\SuluBehatBundle\ElbformatSuluBehatBundle::class => ['test' => true],
```

to config/bundles.php

### Configure behat Extensions

Add the extension to your `behat.yml`. With the `context` parameter you can decide if the sulu kernel for this suite is
running in `website` or `admin` (default) context. It's recommended to use tags to sort features into suites.

Also make sure the [symfony extension](https://github.com/FriendsOfBehat/SymfonyExtension) is enabled and configured.

**NOTE**: The `FriendsOfBehat\SymfonyExtension` must be placed *before* the `SuluExtension`.

```yml
default:
  suites:
    default:
      filters:
        tags: '~@admin'
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

### Configure database

Make sure you have a database configured for the test environment.
It's recommended to have an extra database to not accidentally delete production contents.
After configuration you should initialise it once, before running any test against it.

```sh
bin/console -e test sulu:build prod
```

## Usage

There are several contexts you can use, depending on your need. See the according doc for examples.

| Context                                   | Optional | Purpose                                                                                    |
|-------------------------------------------|----------|--------------------------------------------------------------------------------------------|
| [BrowserContext](doc/BrowserContext.md)   | No       | Navigate through the page.                                                                 |        
| [SuluPageContext](doc/SuluPageContext.md) | No       | Create sulu pages and blocks.                                                              |
| [FormContext](doc/FormContext.md)         | No       | Fill and submit web forms. Based on BrowserContext.                                        |
| [DateContext](doc/DateContext.md)         | No       | Manipulate the "current" date the tests runs in.                                           |
| [SuluMediaContext](doc/MediaContext.md)   | No       | Create sulu media.                                                                         |
| SuluArticleContext                        | Yes      | Creating articles with the [SuluArticleBundle](https://github.com/sulu/SuluArticleBundle). |        
| SuluFormContext                           | Yes      | Creating sulu forms with the [SuluFormBundle](https://github.com/sulu/SuluFormBundle).     |
| WiP: SuluCommunityContext                 | Yes      | TODO: interaction with [SuluCommunityBundle](https://github.com/sulu/SuluCommunityBundle)  |

You need to enable the required context in your suites like this:

```yml
...
default:
  suites:
    default:
      contexts:
        Elbformat\SuluBehatBundle\Context\BrowserContext
        Elbformat\SuluBehatBundle\Context\PageContext
```

## Example

Out of the box you can produce simple, but effective tests like this.

```gherkin
Feature: Custom page

  Scenario: Page renders correctly
    Given there is a "default" page
      | title | Test Page  |
      | url   | /test-page |
    And the page contains a "test" module in "mainContent"
    When I go to "/test-page"
    Then the response status code should be 200
    And I should see a title tag
        """
        Test Page
        """
    And I should see an a tag
      | href | /imprint |
```