# Event storming

Before writing any code I ran a short big-picture event storming on the problem statement. The goal was to agree on what happens at the machine and what we call things, and to list the questions the statement leaves open. Nothing here is a design decision yet. Aggregates, classes and layers come later and are built from this.

The only source is the statement itself. There is no product owner to ask, so the open questions at the end get answered as explicit decisions in a later step.

## Actors

Two people use the machine. The customer inserts coins, picks a product or asks for the money back. The service person opens the machine to set how many products and how many coins for change it holds. The statement mentions nobody else and no external system.

## What happens

The board uses the usual event storming notes. A command is what an actor wants to do. An event is something that happened, in the past tense. A policy is what the machine does on its own after an event ("whenever X, then Y"). A read model is what the actor looks at before acting. A hot spot is an open question, numbered so the list at the end can refer to it.

### Servicing the machine

The service person sends `SERVICE`, opens the machine, sets how many units of each product it holds and how many coins it has for change, and puts it back into service. They decide what to load by looking at the machine's inventory.

| Actor | Command | Events | Read model | Hot spots |
|---|---|---|---|---|
| Service person | Service the machine | Machine opened for service, Product stock set, Available change set, Machine back in service | Inventory: stock per product, coins per type | H7, H8, H9, H10, H14 |

### Inserting money

The customer inserts one coin at a time and watches the display. An accepted coin (0.05, 0.10, 0.25 or 1) adds to the inserted money. Anything else is rejected.

| Actor | Command | Events | Read model | Hot spots |
|---|---|---|---|---|
| Customer | Insert coin | Coin inserted, or Coin rejected | Display: inserted money | H11 |

### Buying a product

The customer looks at the product panel and presses a selector. Whenever a product is selected, the machine checks four things: the product exists, it is in stock, the inserted money covers the price, and the difference can be paid from the coins available for change. If all four hold, the product comes out and its stock goes down. Whenever the inserted money is above the price, the difference is paid as change. The inserted money is then cleared.

| Actor | Command | Events | Policies | Read model | Hot spots |
|---|---|---|---|---|---|
| Customer | Select product (`GET-WATER`, `GET-JUICE`, `GET-SODA`) | Product dispensed, Product stock decreased, Change returned, Available change decreased, Inserted money cleared | Check the four conditions before selling. Pay the difference as change. | Product panel: selector, price, sold out | H5, H6, H7, H15 |

If one of the checks fails, the purchase is refused. The reason matters to the customer, so each one is its own event. Whenever a purchase is refused, the inserted money stays in the machine.

| Event | Read model | Hot spots |
|---|---|---|
| Purchase refused: not enough money | Display: price and inserted money | H4 |
| Purchase refused: sold out | Product panel: sold out | H4 |
| Purchase refused: change not available | | H3, H4 |
| Unknown product selected | | H12 |

### Returning the money

The customer presses `RETURN-COIN` and gets back everything inserted so far. The inserted money goes back to zero.

| Actor | Command | Events | Read model | Hot spots |
|---|---|---|---|---|
| Customer | Return coins | Coins returned, Inserted money cleared | Display: inserted money | H1, H2, H13 |

## The statement's examples as events

All three examples assume the machine has already been serviced with stock and change.

| Input | Output | Events |
|---|---|---|
| `1, 0.25, 0.25, GET-SODA` | `SODA` | Coin inserted ×3, Product dispensed, Inserted money cleared |
| `0.10, 0.10, RETURN-COIN` | `0.10, 0.10` | Coin inserted ×2, Coins returned, Inserted money cleared |
| `1, GET-WATER` | `WATER, 0.25, 0.10` | Coin inserted, Product dispensed, Change returned, Inserted money cleared |

## Rules

These are every rule the statement states, written as the business would say them.

1. The machine accepts 0.05, 0.10, 0.25 and 1 coins.
2. There are at least three products: Water at 0.65, Juice at 1.00 and Soda at 1.50. Each has a selector, a price and a count.
3. A product is sold only when the inserted money covers its price.
4. Money above the price comes back as change, paid from the coins available for change.
5. The machine only gives out 0.05, 0.10 and 0.25 coins.
6. Returning the money gives back everything inserted so far.
7. Servicing sets the coins available for change and how many of each product there are.
8. The machine keeps track of its products, the coins available for change and the inserted money.

## Words

The statement uses "item" and "product" for the same thing. We use product. Inserted money is what the customer has put in and not spent yet. Stock is how many units of a product are left. Available change is the coins the machine holds to pay change, and change is what comes back after a sale. A selector is the button that names a product.

## Open questions

These are the places where the statement says nothing, or says two things. Each one becomes a decision before the behaviour is specified.

| # | Question |
|---|---|
| H1 | The machine accepts 1 coins, but the list of things it gives out only has 0.05, 0.10 and 0.25. If the customer inserts 1 and asks for the money back, what comes out? |
| H2 | Example 2 returns the same two 0.10 coins. Is giving back the same coins the rule, or only the same amount? |
| H3 | If the customer paid more than the price and the machine cannot make the difference, is the sale refused or does it go ahead without change? |
| H4 | After a refused purchase, does the inserted money stay for another try or is it returned? |
| H5 | When several coin combinations make the change, which one is paid? Example 3 uses the fewest coins. |
| H6 | Can the coins inserted for a sale be used to pay that same sale's change? |
| H7 | Do coins from sales become available change, or go somewhere else? |
| H8 | Servicing "sets" stock and change. Does it overwrite the counts or add to them? |
| H9 | What happens to inserted money if the machine is serviced in the middle of a purchase? |
| H10 | Can servicing change prices or add products? |
| H11 | What happens to a coin that is not accepted, such as 0.01 or 2? |
| H12 | Is selecting an unknown product a refused purchase or invalid input? |
| H13 | What does returning the money do when nothing has been inserted? |
| H14 | What does the machine hold before it is serviced for the first time? |
| H15 | Does the customer see that the machine cannot give change before choosing a product? |
