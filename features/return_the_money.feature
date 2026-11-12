Feature: Return the money
  In order to change my mind before buying
  As a customer
  I want to get back the coins I have inserted

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

  Scenario: The customer gets back the coins inserted so far
    When the customer enters "0.10, 0.10, RETURN-COIN"
    Then the machine gives out "0.10, 0.10"
    And the inserted money is "0.00"

  Scenario: The coins come back as they were inserted, a 1 coin included
    When the customer enters "0.25, 1, 0.05, RETURN-COIN"
    Then the machine gives out "1, 0.25, 0.05"

  Scenario: Nothing inserted, nothing comes out
    When the customer enters "RETURN-COIN"
    Then nothing comes out

  Scenario: Returning the money leaves the machine's coins alone
    When the customer enters "1, 0.25, RETURN-COIN"
    Then the machine now holds these coins:
      | coin | count |
      | 0.05 | 10    |
      | 0.10 | 10    |
      | 0.25 | 10    |
      | 1    | 0     |
