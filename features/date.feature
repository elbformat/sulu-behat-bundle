Feature: Manipulate the date being used by sulu
  Scenario: Set a future date
    Given the current date is "2025-03-04 11:12:13"
    When I navigate to "/"
    Then I See "The current date is 04.03.2025 11:12:13"