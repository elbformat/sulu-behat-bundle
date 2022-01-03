# sulu-behat-bundle

Adds Contexts and Helper to easily set-up behat tests in your sulu application.

## Installation

```console
$ composer require elbformat/sulu-behat-bundle
```

## Enable Extension

Add the extension to your `behat.yml`. With the `context` parameter you can decide if the sulu kernel for this suite is
running in `website` or `admin` (default) context.

```yml
...
default:
    extensions:
        Elbformat\SuluBehatBundle\SuluExtension:
            context: website
```

## Contexts

There are several contexts you can use, depending on your need

| Context          | Abstract? | Purpose                                                                                    |
|------------------|-----------|--------------------------------------------------------------------------------------------|
| ArticleContext   | No        | Creating articles with the [SuluArticleBundle](https://github.com/sulu/SuluArticleBundle). |        
| BrowserContext   | No        | Navigate through the page.                                                                 |        
| DatabaseContext  | Yes       | Abstraction for database-related contexts.                                                 |
| DateContext      | No        | Manipulate the "current" date the tests runs in.                                           |
| FormContext      | No        | Fill and submit web forms. Based on BrowserContext.                                        |
| PhpCrContext     | Yes       | Abstraction for phpcr-related contexts.                                                    |                                                                                            |
| SuluContext      | No        | Create pages and blocks.                                                                   |
| SuluFormContext  | No        | Creating sulu forms with the [SuluFormBundle](https://github.com/sulu/SuluFormBundle).     |
| SuluMediaContext | No        | Create sulu media.                                                                         |

Add them with fqn syntax like.
```yml
...
default:
    suites:
        default:
            contexts:
                Elbformat\SuluBehatBundle\ArticleContext:
```
