Feature: Run sulu commands
  @admin
  Scenario: Run command in admin context
    When I run command "debug:router"
    Then the command has a return value of 0
    And the command outputs "sulu_snippet.get_snippets"

  Scenario: Run command in website context
    When I run command "debug:router"
    Then the command has a return value of 0
    And the command outputs "test_website"