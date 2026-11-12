Feature: Insert a coin
  In order to pay for a product
  As a customer
  I want each coin I insert to add to the money I have put in

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

  Scenario Outline: An accepted coin adds its value
    When the customer enters "<coin>"
    Then nothing comes out
    And the inserted money is "<inserted>"

    Examples:
      | coin | inserted |
      | 0.05 | 0.05     |
      | 0.10 | 0.10     |
      | 0.25 | 0.25     |
      | 1    | 1.00     |

  Scenario: Coins add up
    When the customer enters "0.25, 0.25, 0.10"
    Then the inserted money is "0.60"

  Scenario Outline: A coin the machine does not accept is rejected
    Given the customer entered "0.25"
    When the customer enters "<coin>"
    Then the input "<coin>" is rejected as invalid
    And nothing comes out
    And the inserted money is "0.25"

    Examples:
      | coin |
      | 0.01 |
      | 0.50 |
      | 2    |
