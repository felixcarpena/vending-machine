Feature: Buy a product
  In order to get a product
  As a customer
  I want to pick it once I have inserted enough money and get any change back

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

  Scenario Outline: Paying the exact price
    When the customer enters "<coins>, GET-<slot>"
    Then the machine gives out "<product>"
    And the inserted money is "0.00"

    Examples:
      | coins                  | slot  | product |
      | 0.25, 0.25, 0.10, 0.05 | WATER | WATER   |
      | 1                      | JUICE | JUICE   |
      | 1, 0.25, 0.25          | SODA  | SODA    |

  @wip
  Scenario: Paying more than the price gives the product and the change
    When the customer enters "1, GET-WATER"
    Then the machine gives out "WATER, 0.25, 0.10"

  @wip
  Scenario: A sale takes one unit, pays the change and keeps the inserted coins
    When the customer enters "1, GET-WATER"
    Then the machine now has these slots:
      | slot  | product | price | stock |
      | WATER | WATER   | 0.65  | 9     |
      | JUICE | JUICE   | 1.00  | 10    |
      | SODA  | SODA    | 1.50  | 10    |
    And the machine now holds these coins:
      | coin | count |
      | 0.05 | 10    |
      | 0.10 | 9     |
      | 0.25 | 9     |
      | 1    | 1     |

  @wip
  Scenario: Change is paid with the fewest coins
    When the customer enters "1, 1, GET-SODA"
    Then the machine gives out "SODA, 0.25, 0.25"

  @wip
  Scenario: Change is still paid when the largest coin first would fail
    Given the machine holds these coins:
      | coin | count |
      | 0.05 | 0     |
      | 0.10 | 5     |
      | 0.25 | 1     |
      | 1    | 0     |
    When the customer enters "1, 1, GET-SODA"
    Then the machine gives out "SODA, 0.10, 0.10, 0.10, 0.10, 0.10"

  Scenario: The exact price needs no change, even with none in the machine
    Given the machine holds no coins
    When the customer enters "1, GET-JUICE"
    Then the machine gives out "JUICE"

  Scenario: Not enough money
    When the customer enters "0.25, GET-JUICE"
    Then the purchase is refused because only "0.25" is inserted for a price of "1.00"
    And nothing comes out
    And the inserted money is "0.25"

  Scenario: Sold out
    Given the machine has these slots:
      | slot  | product | price | stock |
      | WATER | WATER   | 0.65  | 0     |
      | JUICE | JUICE   | 1.00  | 10    |
      | SODA  | SODA    | 1.50  | 10    |
    When the customer enters "1, GET-WATER"
    Then the purchase is refused because "WATER" is sold out
    And nothing comes out
    And the inserted money is "1.00"

  @wip
  Scenario: The change cannot be paid
    Given the machine holds no coins
    When the customer enters "1, GET-WATER"
    Then the purchase is refused because the change cannot be paid
    And nothing comes out
    And the inserted money is "1.00"

  @wip
  Scenario: The inserted coins the price does not need are handed back, never used as change
    Given the machine holds these coins:
      | coin | count |
      | 0.05 | 0     |
      | 0.10 | 0     |
      | 0.25 | 10    |
      | 1    | 0     |
    When the customer enters "1, 0.10, GET-JUICE"
    Then the machine gives out "JUICE, 0.10"
    And the inserted money is "0.00"

  Scenario: A refused purchase changes nothing in the machine
    Given the machine holds no coins
    When the customer enters "1, GET-WATER"
    Then the machine now has these slots:
      | slot  | product | price | stock |
      | WATER | WATER   | 0.65  | 10    |
      | JUICE | JUICE   | 1.00  | 10    |
      | SODA  | SODA    | 1.50  | 10    |
    And the machine now holds these coins:
      | coin | count |
      | 0.05 | 0     |
      | 0.10 | 0     |
      | 0.25 | 0     |
      | 1    | 0     |

  Scenario: After a refusal the customer can add money and buy
    Given the customer entered "0.25, GET-JUICE"
    When the customer enters "0.25, 0.25, 0.25, GET-JUICE"
    Then the machine gives out "JUICE"

  Scenario: An unknown slot is invalid input
    When the customer enters "1, GET-COFFEE"
    Then the input "GET-COFFEE" is rejected as invalid
    And nothing comes out
    And the inserted money is "1.00"
