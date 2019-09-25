Feature: Users can check in and check out of a building

  Scenario: Users can check into a building
    Given a building
    When "bob" checks into the building
    Then "bob" should have been checked into the building

  Scenario: Users that check in twice raise a check in anomaly
    Given a building
    And "bob" checked into the building
    When "bob" checks into the building
    Then "bob" should have been checked into the building
    And a check-in anomaly should have been detected for "bob" in this building
