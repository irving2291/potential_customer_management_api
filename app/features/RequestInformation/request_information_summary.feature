Feature: Request Information summary

    Scenario: Get summary for request information in range
        When I send a GET request to "/api/v1/requests-information/summary?from=2024-07-01&to=2024-07-31"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should contain the field "total"
        And the JSON should contain the field "new"
        And the JSON should contain the field "won"
        And the JSON should contain the field "in_progress"
        And the JSON should contain the field "recontact"
        And the JSON should contain the field "lost"
