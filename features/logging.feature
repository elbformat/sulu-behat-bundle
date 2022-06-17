Feature: Test if logging works as expected
  Scenario: Warnings are logged
    When I go to "/"
    Then the page shows up
    And the main logfile contains an info entry "Matched route"
    And the main logfile doesn't contain any warning entries