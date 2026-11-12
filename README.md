# Vending machine

A model of a vending machine: it takes coins, sells products, gives change and lets a service person restock it.

The problem statement is in [docs/challenge.md](docs/challenge.md). The work starts from the problem rather than the code. [docs/event-storming.md](docs/event-storming.md) describes what happens at the machine and lists the open questions the statement leaves. [docs/requirements.md](docs/requirements.md) settles them and describes what the machine does, and [docs/design.md](docs/design.md) describes how it is built.

## Requirements

Docker and make. PHP and Composer run inside the container, so nothing else is needed on the host.

## Running

```
make install       # build the image and install the dependencies
make acceptance    # run the acceptance scenarios that are already implemented
make spec-status   # list the scenarios still to implement, tagged @wip
make shell         # open a shell in the container
```

Add `DEBUG=1` to any target to start an Xdebug session, for example `make acceptance DEBUG=1`. The IDE should listen on port 9003 with a server named `vending-machine` that maps the repository root to `/app`.
