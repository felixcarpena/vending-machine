# Design

This document says how the machine is built. What it does is in [requirements.md](requirements.md), and this document refers to its rules by number instead of repeating them.

## Bounded context

Everything here belongs to one bounded context, the vending context: a single machine selling products for coins, and being serviced. Its language is the key terms in the requirements. Inside this context a product is only what a slot holds and what comes out of the machine; the selector, the price and the stock belong to the slot.

Several things a larger system would have are outside it. Choosing products and prices for a fleet would be a catalog context, and in it a product would mean something richer. Planning restock routes would be a machine operations context, which would talk to this one by sending the same service command the CLI sends today. Card or app payments would be a payments context, and sales reporting would consume what this context publishes, once its domain events are turned into integration events that carry what reporting needs, such as an event id and when the sale happened. None of them exist yet, but the boundary is drawn so they can be added without changing the model.

## Domain model

### The aggregate

There is one aggregate, `VendingMachine`. A sale has to check stock, money and change and then change all three together (rules 3, 4, 8 and 11), so they sit inside one consistency boundary and are saved in one go.

The machine is made of four parts:

| Part | Kind | Holds |
|---|---|---|
| `Slots` | Immutable collection of `Slot` values, keyed by selector | Every slot: the product it holds, its price and its stock |
| `Escrow` | Immutable value object | The coins the customer has inserted and not yet spent or taken back |
| `AvailableChange` | Immutable value object | The coins the machine can pay change with |
| `OutputTray` | Immutable value object | What came out of the machine, products and coins, and has not been taken yet |

`VendingMachine` is the only object reached from outside, and the only one with a repository. As an entity it has an identity, a `VendingMachineId`, which commands and queries carry. It is a UUID v7, generated when the machine is started, because a v7 is ordered by creation time and stays index-friendly once machines are stored. The id is what lets the model work unchanged when there is more than one machine. A slot is a value: a sale or a service builds a new one, and the selector it is kept under in `Slots` is what tells slots apart. A slot holds a `Product`, which is only what comes out of the machine, its name for now; price and stock belong to the slot, so the same product could sit in two slots. The collection finds a slot, takes a unit out and restocks, so the machine never walks a list itself. The escrow and the available change have no identity of their own either: each change produces a new value that the machine keeps.

These are built from smaller value objects. `Money` is an amount in integer cents, so no float ever touches a price. `Coin` is an enum of the four accepted coins. `Coins` is a bag of coins, the coins themselves rather than a count per coin, used for the escrow, the available change and the change paid out. `Selector` is the code of a slot: a value object, not an enum, because the catalog is data, not code. It only has to be a non-empty code; how a selector is written on the command line is the CLI's business. `StockLevels` is the number of units requested for each slot when the machine is serviced, never negative. `StartingSetup` is what every new machine starts with: the catalog's slots and the starting change.

An alternative was considered and left out: the machine and a customer session as two aggregates, like a shop and a cart. It fits concurrent or remote buying, but here the session's coins are part of the sale's invariant, so splitting them would put one atomic rule across two transactions. If remote or card buying arrives, it becomes its own aggregate that asks the machine to sell, and that is where the model would split.

### Who owns each rule

The machine handles the rules that involve more than one of its parts. The rest sit with the object whose data they are about.

| Rule | Owner | How |
|---|---|---|
| 1. accepted coins | `Coin` | Only the four coins exist as cases |
| 5. change only in 0.05, 0.10, 0.25 | `Coin` | Each coin knows whether it can be given as change |
| 2. products, prices and stock | `Slot` and `Slots` | A slot knows its price and takes one unit out, refusing when it is empty. It refuses a price that is not above zero or not a multiple of the greatest common divisor of the accepted coins. That is a necessary condition for a price to be payable, not a sufficient one: with the current coins every such multiple can be paid, but with only 0.10 and 0.25 coins the divisor would still be 0.05, and 0.05 or 0.15 could never be paid. The collection finds a slot by its selector or refuses an unknown one |
| 3. money covers the price | `Money` | Compares two amounts |
| 6 and 12. return the inserted coins as inserted | `Escrow` | Holds the exact coins inserted, so the machine hands back those same coins and starts a new, empty escrow |
| 4, 8 and 10. pay change with the fewest coins, or refuse | `AvailableChange` with `ChangeMaker` | Works out the coins for an amount from what it holds, or says it cannot |
| 11. inserted coins never pay change | `Escrow` and `VendingMachine` | The escrow lists the ways to cover the price with the inserted coins, cheapest first. The machine keeps the first one whose change it can pay from the coins it already had and hands the rest back as inserted |
| 7. servicing replaces stock and change | `Slots` and `VendingMachine` | The collection restocks every slot from `StockLevels` and empties the ones not mentioned. The machine replaces the available change and leaves the escrow alone |
| 9. a refused sale changes nothing | `VendingMachine` | Runs every check before changing anything |
| 13. starting stock and change | `StartingSetup` | Starts every new machine with the catalog's slots and the starting change. Infrastructure only reads it from the Symfony parameters in `config/` |

`ChangeMaker` is a domain service: the algorithm for picking coins belongs to no single object. It is an interface because the obvious approach, taking the largest coin first, fails with a limited supply. With one 0.25 and three 0.10 coins it cannot pay 0.30, even though three 0.10 coins would. The implementation searches the combinations and a test pins that case.

### Events and refusals

A domain event records something that happened. `VendingMachine` extends `AggregateRoot`, which keeps the events the machine records and hands each one out once. The events themselves are immutable data objects that implement the `DomainEvent` interface and carry no behaviour. The machine records `CoinInserted`, `CoinsReturned`, `ProductDispensed`, `ChangeReturned`, `OutputTaken` and `MachineServiced`, and the application publishes them after saving. `ProductDispensed` carries the selector, the product and the price charged, so a sale can be counted from the event alone.

A refused purchase is not something that happened, because nothing in the machine changed. So a refusal is a domain exception that extends `Refusal`, an abstract base class in `Shared\Domain`: `NotEnoughMoney` (carrying the price and the inserted amount), `EmptySlot` and `ChangeNotAvailable`. `UnknownSlot` covers a selector no slot has, and the CLI reports it as invalid input. The CLI and the logging middleware recognise a refusal by that class, so an exception from a library or a broken invariant is reported as an error, never as a refusal.

## From use cases to code

Each use case that changes the machine becomes one command. Commands change the machine and return nothing. Reading the machine goes through a query, which changes nothing and returns a read model, never the aggregate. The read models reuse the domain's immutable value objects, such as `Money` and `Coins`, instead of copying them into primitives.

| Use case | Message | Machine | Events |
|---|---|---|---|
| Start a machine, when the CLI starts | `StartMachine` command | a new machine from the `StartingSetup` | none |
| Service the machine | `ServiceMachine` command | `service(stock, change)` | `MachineServiced` |
| Insert a coin | `InsertCoin` command | `insertCoin(coin)` | `CoinInserted` |
| Return the money | `ReturnMoney` command | `returnMoney()` | `CoinsReturned`, when there was money |
| Buy a product | `BuyProduct` command | `buy(selector, changeMaker)` | `ProductDispensed`, `ChangeReturned` when there is change |
| See what came out, after every line | `GetOutputTray` query | read only | none |
| Take what came out, after every line | `TakeOutput` command | `takeOutput()` | `OutputTaken`, when there was something |
| Show the inserted money, after every line | `GetInsertedMoney` query | read only | none |
| See what the machine holds | `GetMachineContents` query | read only | none |

`StartMachine` is not a use case of the statement but the way a session begins. The CLI generates the machine's UUID v7 and sends it in the command, so it knows which machine it drives without the command returning anything.

Since commands return nothing, what the customer receives is part of the machine's state, as it is in a real machine: the product and the coins fall into the output tray and stay there until they are taken. After every line the CLI asks what is in the tray, prints it and takes it. Command and query stay strictly apart, and the CLI does not depend on the events, which are still published for the log and for a reporting context later.

## Architecture

The code is organised by module first and by layer second. The vending bounded context lives in `src/Vending/`. The building blocks it is made from live in `src/Shared/`, which holds no business concepts. Each module is the root of its namespace, `Vending\` and `Shared\`, so the bounded context shows in every import.

```
src/
  Shared/
    Domain/                    AggregateRoot, DomainEvent
    Application/Bus/           CommandBus, QueryBus, EventBus, Command, Query
    Infrastructure/Bus/        the buses built on Symfony Messenger, and the middleware that logs every message
    Infrastructure/Symfony/    the minimal kernel that wires Symfony
  Vending/
    Domain/                    the machine, its value objects, StartingSetup, ChangeMaker, events, exceptions, the repository port
    Application/               one folder per use case: the command or query and its handler
    Infrastructure/            the in-memory repository, reading the starting setup from configuration
    UI/Cli/                    the session command; Input/ turns text into messages, Output/ turns results into text
```

Symfony is wired through that kernel and the files in `config/`. Messenger's buses, their middleware and the handlers are declared there, so the handlers stay free of framework attributes.

Inside each module there are four layers, and dependencies only point inward: UI to application to domain, with infrastructure implementing the ports the inner layers declare.

| Layer | Contains | Depends on |
|---|---|---|
| `Domain` | Aggregates, value objects, domain services, events, exceptions, repository ports | Nothing outside the domain |
| `Application` | Commands, queries, their handlers, and the bus ports | Domain |
| `Infrastructure` | Adapters for the ports: the repository, the buses and their logging, reading the starting setup from configuration, the service container | Application, Domain, Symfony, Monolog |
| `UI` | The CLI: reading input, sending commands and queries, printing results | Application, Symfony Console, and the domain's value objects, which commands carry so that a message is valid as soon as it is created |

The domain and application layers import nothing from Symfony. Symfony appears only in infrastructure and UI, behind our own interfaces.

There are three bus interfaces, not one, because their contracts differ. A command changes the machine and returns nothing. A query returns a result and changes nothing. An event goes to any number of subscribers, possibly none. A single bus interface would make each of them accept a contract that is not its own.

Every command, query and event goes through a Messenger middleware that logs it with Monolog: what was sent, for which machine and how long it took, business refusals as notices and anything unexpected as errors. The log goes to the console's error output and follows its verbosity: nothing by default, refusals and errors with `-v`, every command and query with `-vv`, every event with `-vvv`. The domain never logs.

The repository is in memory and nothing else. A CLI session is one process, so the machine lives in memory for as long as the session runs. Persisting it, for example in Redis, means writing one more adapter for the same port, and giving the machine a way to be rebuilt from the saved state. The in-memory repository does not need one, because it keeps copies of the objects themselves.

## The CLI

`bin/vending-machine` starts an interactive session. It lists the actions, then reads one line at a time until `EXIT`.

```
> 1
  Inserted: 1.00
> GET-WATER
-> WATER, 0.25, 0.10
  Inserted: 0.00
> 0.10, 0.10, RETURN-COIN
-> 0.10, 0.10
  Inserted: 0.00
> GET-SODA
  Purchase refused: not enough money (price 1.50, inserted 0.00)
  Inserted: 0.00
```

A line can carry several actions separated by commas, so the statement's examples can be pasted as they are. After a line, the session prints everything that came out of the machine during it on one line, in the statement's format: `->` followed by the product and then the coins, largest first. When nothing came out there is no `->` line. Refusals and invalid input are printed as they happen. The line always ends with the inserted money.

`STATUS` prints each slot with the product it holds, its price and its stock, and the count of each coin the machine holds.

Servicing takes its values on the same line, for example `SERVICE WATER=10 JUICE=10 SODA=10 0.05=20 0.10=20 0.25=20`, and takes the whole line: a line that holds a service and any comma, such as `SERVICE WATER=1, 0.05=2` or `SERVICE WATER=1,`, is invalid input as a whole and changes nothing, and so is a `SERVICE` with nothing to set. Anything not given is set to zero. Anything that is not a valid action is reported as invalid input and changes nothing.

The CLI is split by direction. `Input/` turns a line into the messages to send: `ActionParser` reads every action, coins included. `Output/` turns results into text: `StatusView`, `TrayView`, `RefusalView`, `MoneyFormat` and `CoinFormat`. The command itself only runs the loop: read a line, send what the parser found, write what the views produce.

## Testing

Each level tests one thing.

| Suite | Tool | Covers |
|---|---|---|
| Unit | PHPUnit | The domain alone: every rule, including the regression cases for rule 11 and for the largest-coin-first failure |
| Application | PHPUnit | Handlers called directly, with the in-memory repository and a recording event bus |
| Integration | PHPUnit | The adapters: the in-memory repository, the Messenger buses and their logging, the starting setup read from configuration, and the CLI command |
| Acceptance | Behat | Every use case end to end, by starting the real CLI, writing to its input and reading its output. Scenarios set up the machine through the CLI itself, with `SERVICE`, so there is no test-only way into it |

Tests are grouped by suite first and then mirror the source tree, for example `tests/Unit/Vending/Domain/`.

The acceptance scenarios are written before the code and tagged `@wip`, which keeps them out of the default run until their use case is built. PHPStan at its strictest level and a coding standard check run alongside the tests.

## Build order

1. Skeleton: Docker, Composer, make targets, and the Behat features tagged `@wip`.
2. Start a machine and drive it from the CLI: the walking skeleton, tested end to end through the console.
3. Service the machine and see what it holds. Scenarios set up the machine with `SERVICE`, so from here on every iteration turns its own scenarios green.
4. Insert a coin and return the money.
5. Buy a product with the exact amount, refusing it when the money is short or the slot is empty.
6. Buy a product and receive change, refusing it when the change cannot be paid.
