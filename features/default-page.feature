@sulu
Feature: Simple page with a block

  Scenario: No blocks
    Given there is a page
      | title | default-page  |
      | url   | /default-page |
    When I go to "/default-page"
    Then the response status code should be 200
    And I should see a h1 tag "default-page"
    And I should see a p tag "/default-page"
    And I should not see a h2 tag "Block Headline"
    And I should not see a p tag "Lorem Ipsum"

  Scenario: Text block
    Given there is a page
      | title | default-page  |
      | url   | /default-page |
    And the page contains a text module in block
      | headline | Block Headline |
      | text     | Lorem Ipsum    |
    When I go to "/default-page"
    Then the response status code should be 200
    And I should see a h1 tag "default-page"
    And I should see a p tag "/default-page"
    And I should see a h2 tag "Block Headline"
    And I should see a p tag "Lorem Ipsum"
