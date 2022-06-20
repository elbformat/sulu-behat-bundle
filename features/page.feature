Feature: Example/Test for SuluPageContext

  Scenario: Render default home page
    When I go to "/"
    Then the response status code is 200
    And I see "Hello World"
    And I don't see "Goodbye World"
    And I see a div tag
      | class | content |
    And I don't see a span tag
    """
    Imprint?
    not here
    """
    And I don't see an a tag "Imprint"
      | href | /imprint |

  Scenario: Create a page with a simple module and check if it renders correctly
    Given there is a page
      | title | default-page  |
      | url   | /default-page |
    And the page contains a text module in block
      | headline | Block Headline |
      | text     | Lorem Ipsum    |
    When I go to "/default-page"
    Then the response status code is 200
    And I see a h1 tag "default-page"
    And I see a p tag "/default-page"
    And I see a h2 tag "Block Headline"
    And I see a p tag "Lorem Ipsum"

  Scenario: Create a page without modules
    Given there is a page
      | title | default-page  |
      | url   | /default-page |
    When I go to "/default-page"
    Then the page shows up
    Then the response status code is 200
    And I see a h1 tag "default-page"
    And I see a p tag "/default-page"
    And I don't see a h2 tag "Block Headline"
    And I don't see a p tag "Lorem Ipsum"

  Scenario: Create a page tree, the child containing a reference to the parent
    Given there is a default page
      | title | Parent |
      | url   | /test  |
    And there is a page as child of 1000
      | title | Child       |
      | url   | /test/child |
    And the page contains a reference module in block
      | title   | Show references  |
      | links.0 | IDENTIFIER[1000] |
    When I navigate to "/test/child"
    Then the page shows up
    And I see an a tag "Parent"
      | href | http://localhost/test |