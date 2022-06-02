# BrowserContext

This contexts helps you to navigate through the page like a browser does.
Internally we directly dispatch the symfony kernel to reduce the overhead and increase performance.
As no real browser is involved, you can only check the DOM and not javascript functionality.

## Examples

```gherkin
@sulu
Feature:

  Scenario:
    When I go to "/"
    Then the response status code should be 302
    When I follow the redirect
    Then the response status code should be 200
    And I should see "Hello World"
    And I should not see "Goodbye World"
    And I should see a div tag
      | class | test |
    And I should not see a span tag
    """
    Impressum
    ist hier nicht
    """
    And I should not see an a tag "Impressum"
  | href | /imprint |
```

Ajax Request

```gherkin
@sulu
Feature:

  Scenario:
    Given I am logged in as admin
    When I send a POST request to "/admin/search"

```