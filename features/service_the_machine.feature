@wip
Feature: Service the machine
  In order to keep the machine able to sell and give change
  As a service person
  I want to set how many units each slot holds and how many coins the machine holds

  Background:
    Given the machine has these slots:
      | slot  | product | price | stock |
      | WATER | WATER   | 0.65  | 10    |
      | JUICE | JUICE   | 1.00  | 10    |
      | SODA  | SODA    | 1.50  | 10    |
    And the machine holds these coins:
      | coin | count |
      | 0.05 | 10    |
      | 0.10 | 10    |
      | 0.25 | 10    |
      | 1    | 0     |

  Scenario: Servicing replaces the stock and the coins
    When the service person enters "SERVICE WATER=5 JUICE=3 SODA=0 0.05=1 0.10=2 0.25=3 1=4"
    Then the machine now has these slots:
      | slot  | product | price | stock |
      | WATER | WATER   | 0.65  | 5     |
      | JUICE | JUICE   | 1.00  | 3     |
      | SODA  | SODA    | 1.50  | 0     |
    And the machine now holds these coins:
      | coin | count |
      | 0.05 | 1     |
      | 0.10 | 2     |
      | 0.25 | 3     |
      | 1    | 4     |

  Scenario: Anything not given is set to zero
    Given the customer entered "1, GET-WATER"
    When the service person enters "SERVICE WATER=4 0.25=2"
    Then the machine now has these slots:
      | slot  | product | price | stock |
      | WATER | WATER   | 0.65  | 4     |
      | JUICE | JUICE   | 1.00  | 0     |
      | SODA  | SODA    | 1.50  | 0     |
    And the machine now holds these coins:
      | coin | count |
      | 0.05 | 0     |
      | 0.10 | 0     |
      | 0.25 | 2     |
      | 1    | 0     |

  Scenario: Servicing leaves the inserted money alone
    Given the customer entered "0.25, 0.10"
    When the service person enters "SERVICE WATER=1"
    Then the inserted money is "0.35"

  Scenario: The machine sells what servicing put in
    Given the service person entered "SERVICE SODA=1 0.25=2"
    When the customer enters "1, 1, GET-SODA"
    Then the machine gives out "SODA, 0.25, 0.25"

  Scenario Outline: Invalid servicing changes nothing
    When the service person enters "<line>"
    Then the input "<line>" is rejected as invalid
    And the machine now has these slots:
      | slot  | product | price | stock |
      | WATER | WATER   | 0.65  | 10    |
      | JUICE | JUICE   | 1.00  | 10    |
      | SODA  | SODA    | 1.50  | 10    |
    And the machine now holds these coins:
      | coin | count |
      | 0.05 | 10    |
      | 0.10 | 10    |
      | 0.25 | 10    |
      | 1    | 0     |

    Examples:
      | line                    |
      | SERVICE COFFEE=3        |
      | SERVICE WATER=-1        |
      | SERVICE 0.50=2          |
      | SERVICE WATER=ten       |
      | SERVICE WATER=1, 0.05=2 |
      | 0.25, SERVICE WATER=1   |
      | SERVICE WATER=1,        |
      | SERVICE                 |
