Feature: Media can be created to test embedding and accessing them.

  Scenario: Upload file to default collection and link it
    Given there is an image
      | title | Lorem Image |
    And there is a page
      | title | testpage |
      | url   | /test    |
    And the page contains an image module in block
      | images.ids.0 | 1000 |
    When I visit "/test"
    Then the page shows up
    And I see an img tag
      | src | /media/1000/download/1px.jpg?v=1 |
      | alt | Lorem Image                      |

  Scenario: Upload file to custom collection
    Given there is an image in collection "sulu_contact"
      | file | tests/fixtures/1px.jpg |