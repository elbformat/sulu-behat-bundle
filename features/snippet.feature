Feature: Example/Test for SuluSnippetContext

  Scenario: Use snippet for footer
    Given there is a default snippet
      | title       | Random snippet        |
      | description | I am a random snippet |
    And the snippet is set as default for "default-area"
    And there is a "default" page
      | title | i am default |
      | url   | /default     |
    When I go to "/default"
    Then the page shows up
    And I see "I am a random snippet"
    And I see a h3 tag "Random snippet"
