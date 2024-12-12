# Rapid Design Pattern Implementations

This package allows users to quickly add functionality to their projects based upon a design pattern they want to use to implement it. 

For instance, if you're a developer looking to add an object to your application that uses 'States' or the 'State Pattern', then this package makes it simple to create that functionality with a high degree of flexibility and customisation to not only speed up your development, but also to ensure delivery of the functionality starting from a place of an accurate pattern implementation. 

Let's look an a quick example.

Imagine you want to add a 'Transaction' model to your application. In this example, a 'Transaction' can have various 'States' e.g. Started, Processing, Completed, Failed, Cancelled.

So, knowing that it would probably be best to use the recognizable 'State Pattern' to implement this functionality, we can use the package to create the basis for this implementation:

```bash
php artisan implement-pattern:state
```

Running this command will trigger a series of console interactions that will confirm some specifics and will then create the folders and classes necessary for the implementation:


```
└── Transactions (containing or 'Context' folder)
   ├── Contracts
   │   ├── HasTransactionStates
   │   ├── IsTransactionState
   ├── States
   │   ├── Started
   │   ├── Processing
   │   ├── Completed
   │   ├── Failed
   │   ├── Cancelled
   └── Transaction ('Context' object that uses the States)

```

## Installation

You can install the package via composer:

```bash
composer require anthony-brindley/design-pattern-implementor
```

## Usage

Using the package is as simple as running any other Artisan command:

```bash
php artisan implement-pattern:state
```

Then answer the questions presented to you in the console. 


## Patterns Index

### Creational
1. Factory
1. 


### Structural


### Behavioural



## TODO/Roadmap

1. Finish adding working patterns:
- Creational
- - ~~Factory~~
- - Abstract Factory
- - Builder
- - Prototype
- - Singleton

- Structural
- - ~~Adaptor~~
- - Bridge
- - Composite
- - ~~Decorator~~
- - ~~Facade~~
- - Flyweight
- - ~~Proxy~~

- Behavioural
- - Chain Of Responsibility
- - Command
- - Iterator
- - Mediator
- - Memento
- - ~~Observer~~
- - ~~State~~
- - ~~Strategy~~
- - Template Method
- - Visitor

2. Add Tests
3. Refactor and optimize
4. Add Readme's and supporting resources for patterns

## Testing

I've added some inital tests to the package. During development, these tests can be run (thanks to `composer scripts`) by the following commands: 

```bash
composer test
composer test -- -p // run parallel tests
composer test -- -filter="some-group" // pass the filter param to pest
```