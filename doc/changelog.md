## Version

### v2.0 (Sulu 3)
Completely reworked Page, Article and Snippet Contexts, as sulu now uses it's own content structure.
This Contexts may not be feature-complete, but should provide a solid base for future enhancements.

Under the hood we now use a more flexible way to fetch arguments (as tables, yaml or json).
This is achieved by using elbformat/symfony-behat-bundle:^1.6. See the [changelog](https://github.com/elbformat/symfony-behat-bundle/blob/main/doc/changelog.md#v160) for more details.

## Features
* Better support for webspaces (`_webspaceKey`)
* Better support for locales (`locale`)

## Improvements
* Build now uses github actions and matrix builds (php versions, with/without form bundle)
* 

## BC Breaks
* Removed `Given there is a(n) (:template) page as child of :parentId` in favor of `_parentId` or `_parentSlug`.

Before
```gherkin
Given there is a "default-page" page
| url | /default-page |
And there is a(n) default page as child of 1000
| url | /child |
```

After
```gherkin
Given there is a "default-page" page
| url | /default-page |
And there is a(n) default page as child of 1000
| _parentSlug | /default-page       |
| url         | /default-page/child |
```
 