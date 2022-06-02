# PageContext
The PageContext is for creating sulu pages to check the output with the BrowserContext.
Make sure you tag your feature (or at least the scenario) with `@sulu` to reset the phpcr database befor running the test.

## Example
```gherkin
@sulu
Feature:
  Scenario:
    Given there is a default page
    Given the page contains a mod1 module in zone1
```