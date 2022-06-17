Feature: Create forms with sulu form builder

  Background:
    Given there is a sulu form
      | title       | contact-form                  |
      | fromEmail   | test@elbformat.de             |
      | toEmail     | test@elbformat.de             |
      | successText | <p>Erfolgreich abgesendet</p> |
      | submitLabel | Senden                        |
    And the form contains a "text" field
      | title         | I am a text field  |
      | placeholder   | I am a placeholder |
      | defaultValue  | default            |
      | shortTitle    | shorty             |
      | required      | true               |
      | options.align | left               |
    And the form contains a "dropdown" field
      | title           | Please choose      |
      | options.choices | Option A\nOption B |
    And there is a page
      | title | Form page |
      | url   | /form     |
    And the page contains a form module in block
      | form | 1000 |

  Scenario: Show form
    When I go to "/form"
    Then the page shows up
    And the page contains a form named "dynamic_form1000"
    And I see an input tag
      | type        | text                   |
      | name        | dynamic_form1000[text] |
      | required    | required               |
      | value       | default                |
      | placeholder | I am a placeholder     |
    And I see a select tag
      | name | dynamic_form1000[dropdown] |
    And I see an option tag "Option A"
      | value | Option A |
    And I see an option tag "Option B"
      | value | Option B |

  Scenario: Submit form
    When I go to "/form"
    And I use form "dynamic_form1000"
    And I fill "hi" into "dynamic_form1000[text]"
    And I select "Option B" from "dynamic_form1000[dropdown]"
    And I submit the form
    Then the response status code is 302
    When I follow the redirect
    Then the page shows up
    And I see a p tag "Erfolgreich abgesendet"
    # RFE: Check mails, when MailerContext is ready in SymfonyBehatBundle