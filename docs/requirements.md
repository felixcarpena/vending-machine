# Requirements

This document says what the machine does. It starts from the statement in [challenge.md](challenge.md) and the questions raised in [event-storming.md](event-storming.md), and settles every one of them. How the machine is built is in a separate design document.

The statement is intentionally simple, and the only part it leaves open on purpose is how the actions are driven. The event storming went further than that and asked questions a real machine would raise. Most of them are not part of this problem, so the last section says which questions became rules and which were left out.

## Key terms

| Term | Meaning |
|---|---|
| Coin | One of the accepted denominations: 0.05, 0.10, 0.25 and 1. |
| Slot | A position in the machine. It holds one product and has a selector, a price and a stock. |
| Product | What a slot holds and what comes out of the machine. The statement also calls it an item. |
| Selector | The code the customer uses to pick a slot: `WATER`, `JUICE` or `SODA`. In the statement each slot is named after the product it holds. |
| Stock | How many units a slot holds. |
| Inserted money | The coins the customer has put in and not yet spent or taken back. The machine holds these coins apart from its change, in what the vending industry calls the escrow, until a sale or a return decides where they go. |
| Available change | The coins the machine holds to pay change. |
| Change | The coins paid back when the inserted money is more than the price. |
| Output tray | Where the product and the coins come out of the machine. They stay there until the customer takes them. |

## Rules

From the statement:

1. The machine accepts 0.05, 0.10, 0.25 and 1 coins.
2. The machine sells at least three products, each from its own slot: Water at 0.65, Juice at 1.00 and Soda at 1.50.
3. A sale happens only when the inserted money covers the slot's price.
4. Money above the price is paid back as change, from the available change.
5. Change is paid only in 0.05, 0.10 and 0.25 coins.
6. Returning the money gives back everything inserted so far.
7. Servicing sets the stock of each slot and the available change, replacing what was there. It changes nothing else.

Decided here, because the statement does not say:

8. A sale that needs change the machine cannot pay is refused.
9. After a refused sale the inserted money stays in the machine, so the customer can add coins, pick another product or take the money back.
10. When the change can be paid in more than one way, the machine pays it with the fewest coins its available change allows. This is what example 3 does.
11. The inserted coins never pay change. To cover the price, the machine keeps the cheapest choice of inserted coins whose change it can pay from the coins it already had, and hands the other inserted coins back as they were inserted. A customer who pays 1.10 for a 1.00 product, when the machine has no 0.10, gets the product and their own 0.10 back. The sale is refused only when no choice of inserted coins leaves change the machine can pay.
12. Returning the money gives back the coins exactly as they were inserted. A 1 coin comes back as a 1 coin. Rule 5 is about change, not about returning the customer's own coins.
13. The machine starts with a configured stock and available change, so it can sell before anyone services it. The starting values are configuration, chosen so the statement's examples work straight away.

## Use cases

Each use case has a feature file with its scenarios. The feature files are the executable version of this section.

### Service the machine

The service person sets the stock of each slot and the number of coins of each type the machine holds. The new values replace the old ones, and anything the service person does not give is set to zero, so after servicing the machine holds exactly what was stated. The inserted money, the products in each slot and their prices are not touched.

Feature: `features/service_the_machine.feature`

### Insert a coin

The customer inserts an accepted coin and the inserted money goes up by its value. The customer sees the new total.

Feature: `features/insert_a_coin.feature`

### Return the money

The customer asks for the money back. The machine gives back every coin inserted, as inserted, and the inserted money goes back to zero. If nothing was inserted, nothing comes out.

Feature: `features/return_the_money.feature`

### Buy a product

The customer picks a slot by its selector. If the slot is not empty, the inserted money covers its price, and the change can be paid, the machine dispenses the product the slot holds. It keeps the inserted coins it needs, pays any change from the coins it already had, hands back the inserted coins it did not need, and clears the inserted money. The slot's stock goes down by one.

The purchase is refused, and nothing in the machine changes, when:

- the inserted money does not cover the price. The customer is told the price and how much is inserted.
- the slot is empty.
- the change cannot be paid with the available change the machine already had.

Feature: `features/buy_a_product.feature`

### See what the machine holds

Anyone at the machine can ask what it holds: each slot with its price and stock, and how many coins of each type it has. The statement asks the machine to keep track of this state, and this is how it can be checked, for example by the service person before and after servicing. Asking changes nothing.

Feature: `features/see_what_the_machine_holds.feature`

The three examples in the statement are scenarios in these features: example 1 and 3 in buying, example 2 in returning the money.

## What happened to each open question

| Question | Outcome |
|---|---|
| H2 same coins or same amount, H8 set or add, H10 what servicing may change | Answered by the statement. Rules 6 and 7. |
| H1 returning a 1 coin, H3 no change, H4 money after a refusal, H5 which coins, H6 inserted coins as change, H14 first state | Decided here. Rules 8 to 13. |
| H11 unaccepted coin, H12 unknown selector, H13 return with nothing inserted | The statement lists the valid actions, and the interface adds one to see what the machine holds. Anything else is invalid input, rejected without changing the machine. H13 needs no rule: nothing inserted, nothing returned. |
| H7 separate cash box, H9 servicing during a purchase, H15 exact change warning | Concerns of a physical machine that this problem does not have. Out of scope. |
