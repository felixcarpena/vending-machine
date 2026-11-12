Feature: See what the machine holds
  In order to know whether the machine needs servicing
  As a service person
  I want to see the stock of each slot and the coins the machine holds

  Background:
    Given the machine has these slots:
      | slot  | product | price | stock |
      | WATER | WATER   | 0.65  | 10    |
      | JUICE | JUICE   | 1.00  | 2     |
      | SODA  | SODA    | 1.50  | 0     |
    And the machine holds these coins:
      | coin | count |
      | 0.05 | 3     |
      | 0.10 | 0     |
      | 0.25 | 7     |
      | 1    | 0     |

  Scenario: The status lists every slot and every coin
    When the service person enters "STATUS"
    Then the status shows these slots:
      | slot  | product | price | stock |
      | WATER | WATER   | 0.65  | 10    |
      | JUICE | JUICE   | 1.00  | 2     |
      | SODA  | SODA    | 1.50  | 0     |
    And the status shows these coins:
      | coin | count |
      | 0.05 | 3     |
      | 0.10 | 0     |
      | 0.25 | 7     |
      | 1    | 0     |

  Scenario: Asking changes nothing
    Given the customer entered "0.25"
    When the service person enters "STATUS"
    Then nothing comes out
    And the inserted money is "0.25"
