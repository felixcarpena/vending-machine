# Vending machine

A model of a vending machine: it takes coins, sells products, gives change and lets a service person restock it. It is written in PHP 8.5 and driven from an interactive command line session.

## Requirements

Docker and make. PHP, Composer and every tool run inside the container, so nothing else is needed on the host.

## Running

```
make install       # build the image and install the dependencies
make run           # start an interactive vending machine session
make test          # run the PHPUnit suites
make analyse       # run static analysis
make lint          # check the coding standard (make fix applies it)
make acceptance    # run the acceptance scenarios that are already implemented
make spec-status   # list the scenarios still to implement, tagged @wip
make shell         # open a shell in the container
```

Add `DEBUG=1` to any target to start an Xdebug session, for example `make acceptance DEBUG=1`. The IDE should listen on port 9003 with a server named `vending-machine` that maps the repository root to `/app`.

A session looks like this. A line can hold several actions separated by commas, so the examples of the statement can be pasted as they are.

```
Vending machine ready.
Actions: 0.05 | 0.10 | 0.25 | 1 | GET-WATER | GET-JUICE | GET-SODA | RETURN-COIN | SERVICE | STATUS | EXIT
> 1, GET-WATER
-> WATER, 0.25, 0.10
  Inserted: 0.00
> 0.10, 0.10, RETURN-COIN
-> 0.10, 0.10
  Inserted: 0.00
> 0.25, GET-JUICE
  Purchase refused: not enough money (price 1.00, inserted 0.25)
  Inserted: 0.25
> SERVICE WATER=5 JUICE=5 SODA=5 0.05=20 0.10=20 0.25=20
  Machine serviced.
  Inserted: 0.25
> STATUS
  WATER holds WATER at 0.65 (5 left)
  JUICE holds JUICE at 1.00 (5 left)
  SODA holds SODA at 1.50 (5 left)
  Coins: 0.05 x20, 0.10 x20, 0.25 x20, 1 x0
  Inserted: 0.25
> EXIT
```

`->` shows what comes out of the machine. Each product sits in a slot, and `GET-` takes the slot's selector; here every slot is named after the product it holds. The machine starts with ten units in each slot and ten coins of each change coin, so it can sell straight away.

Every command, query and event the session handles is logged to the error output, following the console verbosity: `-v` shows refused actions and errors, `-vv` adds every command and query, `-vvv` every event. From `make shell`, run `bin/vending-machine -vv` to watch them.

The session runs on a compiled service container, and Symfony does not rebuild it when `config/` changes. `make run` and `make acceptance` remove `var/cache` first, so they always start from the current configuration, also when moving between commits. From `make shell`, run `rm -rf var/cache` after changing `config/`.

## How it was built

The work started from the problem, not from the code, and the documents in `docs/` follow that order. [challenge.md](docs/challenge.md) is the statement. [event-storming.md](docs/event-storming.md) maps what happens at the machine and lists the questions the statement leaves open. [requirements.md](docs/requirements.md) settles those questions as rules and use cases. [design.md](docs/design.md) explains the model and the architecture: one bounded context, one aggregate, which object owns each rule, and the layers.

The behaviour was written as Gherkin in `features/` before any code, and Behat runs it end to end by starting the real CLI and talking to it through its input and output. The code was then built outside-in: first a walking skeleton, a machine that can be started and driven from the console; then servicing and status, which let the scenarios prepare a machine; then one use case at a time, each turning its own scenarios green. The commit history follows that path, and every commit passes the tests, static analysis and the coding standard on its own, so it can be read commit by commit.

The code is organised by module and then by layer. `src/Vending` is the bounded context, with `Domain`, `Application`, `Infrastructure` and `UI`. `src/Shared` holds the building blocks it relies on: the aggregate root, domain events, and the command, query and event buses, implemented with Symfony Messenger. The domain and application layers do not depend on Symfony.

Tests are grouped by level. `tests/Unit` covers the domain on its own, `tests/Application` the use cases with an in-memory repository, `tests/Integration` the adapters and the wiring of the Symfony kernel and the CLI, and `features/` the use cases end to end.

## Known limits and trade-offs

These are deliberate, or left for when the problem asks for them. Each says what would change.

- **Commands and read models carry domain value objects.** A message is valid as soon as it is created, and the CLI depends on the domain's value objects. An asynchronous transport would need serializers for them, or primitive messages mapped at the edge.
- **The events are domain events only.** They carry no event id and no time. A reporting context would get integration events built from them with what it needs.
- **The machine lives in memory.** There is no way to rebuild it from saved state, no version for optimistic locking and no outbox for the events: one process holds one machine, so none of them has anything to protect yet. Persisting it brings all three.
- **Every command handler repeats the same steps:** load the machine, act, take its events, save it, publish them. A middleware could own them; they stay explicit so each handler reads on its own.
- **`Selector` normalises what it is given** (trimmed, upper case), while the CLI only accepts selectors already written in upper case.
