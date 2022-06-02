# FormContext

This context uses the BrowserContext and adds helper for form filling.

## Examples

```gherkin
@sulu
Feature:
  Scenario:
    When I go to "/"
    Then the page must contain a form named "test"
    And the form must contain an input field
      | name     | test[textfield] |
      | required | true            |

```

```gherkin
@sulu
Feature:
  Scenario:
    When I go to "/"
    And  I use form "test"
    And I fill "Hello World" into "test[textfield]"
    And I select "test[radio]" radio button with value "option1"
    And I check "test[check]" checkbox
    And I select "world" from "test[hello]"
    When I submit the form
    Then the response status code should be 204

```
