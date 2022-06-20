@admin
Feature: Perform admin actions in backend

  Scenario: Login in and call page list ajax action
    Given I am logged in as admin
    When I make a GET request to "/admin/metadata/list/pages"
    Then the response status code is 200
    And the response json contains
    """
    {
      "id": {
        "label": "ID"
      }
    }
    """