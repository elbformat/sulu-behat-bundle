@sulu
Feature: Test homepage
  Scenario: Default home page
    When I go to "/"
    Then the response status code should be 200
    And I should see "Hello World"
    And I should not see "Goodbye World"
    And I should see a div tag
      | class | content |
    And I should not see a span tag
    """
    Impressum
    ist hier nicht
    """
    And I should not see an a tag "Impressum"
      | href | /imprint |

  @todo
  Scenario: Redirect
