# Pluggit - Storage

[![Build Status](https://scrutinizer-ci.com/g/jmartin82/virtual-storage/badges/build.png?b=master)](https://scrutinizer-ci.com/g/jmartin82/virtual-storage/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jmartin82/virtual-storage/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jmartin82/virtual-storage/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jmartin82/virtual-storage/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jmartin82/virtual-storage/?branch=master)


Storage is a filesystem abstraction which allows you to easily swap out a local filesystem for a remote one.

## TLDR;
```php

//faster way
(new StorageBuilder())->build()->put('/tmp/test.txt',"this is a test");

//more customized
$sb = new StorageBuilder();
$s = $sb->addAdapter('S3AWS')
    ->addAdapter(new DropBox())
    ->addAdapter('FileSystem')
    ->setLogger(new Logger())
    ->build(new \Cmp\Storage\Strategy\FallBackChainStrategy());
$s->put('/tmp/test.txt',"this is a test");


```

## Installation

Add this repo to your composer.json

````json
"repositories": {
  "cmp/storage": {
    "type": "vcs",
    "url": "git@github.com:CMProductions/storage.git"
  }
}
````

Then require it as usual:

``` bash
composer require "cmp/storage"
```


##Functions available from storage

### Exists
Check whether a file exists.

### Get
Read a file and return the content.

### GetStream
Retrieves a read-stream for a file.

### Rename
Rename a file.

### Delete
Delete a file or directory (even if is not empty).

### Put
Create a file or update if exists. It will create the missing folders.

### PutStream
Create a file or update if exists. It will create the missing folders.

__*Note:*__ Use stream functions for big files.


##Adapters

It provides a generic API for handling common tasks across multiple file storage engines. If you want add a new one, you have to implements ``\Cmp\Storage\AdapterInterface``.

The adapter interface contains these methods:

* `exists`
* `get`
* `getStream`
* `rename`
* `delete`
* `put`
* `putStream`
* `getName`

###Builtin

This libs is shipped with two builtin adapters ready to use.

* FileSystem: This adapter interacts directly with the host filesystem.
* S3AWS: This adapter interacts with Amazon S3 service. (You must add some env parameters to use it)

##Strategies

It allows you specify different call strategies when you have been registered more than one adapter.
If you want create a custom call strategy you must extend ``\Cmp\Storage\Strategy\AbstractStorageCallStrategies``

__*Note:*__ By default the lib uses the CallAllStrategy.

###Builtin

There are two ready to use strategies.

* `\Cmp\Storage\StrategyFallBackChainStrategy` : This strategy call each adapter by insertion order since one of them return a some result. (In case of error, it will be logged silently)
* `\Cmp\Storage\Strategy\CallAllStrategy` : This strategy call all providers to apply the same action. (perfect for fist migration steps)


##Logging

The lib provides a default stdout logger but you can change in any moment by any PSR-3 logger compliant.


##StorageBuilder

The storage builder takes te responsibility of create an abstract storage for you.

The available functions are:

__Fluid calls:__

* `setStrategy(AbstractStorageCallStrategy $strategy)` : Set a custom strategy
* `setLogger(LoggerInterface $logger)` : Set custom logger
* `addAdapter($adapter)` : Add a new adapter
* `build(AbstractStorageCallStrategy $callStrategy = null, LoggerInterface $logger = null)` : Build the virtual storage

__Non fluid calls:__

* `getStrategy()` : Get the current strategy
* `getLogger()` :Get the current Logger
* `hasLoadedAdapters()`Check if one or more adapters has been loaded


### Requirements

* php 5.6


### Contribution

* Fork the repo.
* Fix an issue or add a new adapter or improve something or whatever you want.
* Follow the PSR-2 style.
* Run the tests. (add more tests if is needed).
* Make a pull request.
