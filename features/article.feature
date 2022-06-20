Feature: Create articles with SuluArticleBundle

  Scenario: Create and render an article with a module in it
    Given there is a default article "test"
      | title   | Test Article       |
      | article | <p>Lorem Ipsum</p> |
    And the article contains a text module in block
      | headline | Article Module |
      | text     | Lorem Ipsum    |
    When I go to "/articles/test-article"
    Then the page shows up
    And I see "Test Article"
    And I see "Article Module"
    And I see "Lorem Ipsum"
