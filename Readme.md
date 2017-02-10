# Pluggit - Storage

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/CMProductions/storage/badges/quality-score.png?b=master&s=52f830493e587ebebad057b3bad44c3aad65e4ff)](https://scrutinizer-ci.com/g/CMProductions/storage/?branch=master)

Storage is a filesystem abstraction which allows you to easily swap out a local filesystem for a remote one.

## TLDR;
```php

//one adapter (save data to S3)
$s3Adapter = new \Cmp\Storage\Adapter\S3AWSAdapter();
$s3Adapter->put('/tmp/test.txt',"this is a test");


//two adapters with a fallback strategy and decorated with a logger
$s3Adapter = new \Cmp\Storage\Adapter\S3AWSAdapter();
$fallBackAdapter = (new StorageBuilder())->addAdapter($s3Adapter)
    ->addAdapter($s3Adapter) //the order matters with FallBackChainStrategy
    ->addAdapter($fileSystemAdapter)
    ->setLogger(new Logger())
    ->build(new \Cmp\Storage\Strategy\FallBackChainStrategy());

//it saves data to S3 and if fails save the data to FS
$fallBackAdapter->put('/tmp/test.txt',"this is a test");


//one step more fs adapter bind to one folder and strategy to another folder
$vfs = new \Cmp\Storage\MountableVirtualStorage($fileSystemStorage); //bind to any path that non match with mountpoint folders
$localMountPoint = new \Cmp\Storage\MountPoint('/tmp', $fileSystemAdapter);
$publicMountPoint = new \Cmp\Storage\MountPoint('/var/www/app/public', $fallBackAdapter);
$vfs->registerMountPoint($localMountPoint);
$vfs->registerMountPoint($publicMountPoint);

/*
//move file from /tmp (FS) to /var/www/app/public (S3) and if fails try to move from /tmp (FS) to /var/www/app/public (FS)
*/
$vfs->move('/tmp/testfile.jpg','/var/www/app/public/avatar.jpg' );
```

## Installation

Add this repo to your composer.json

````json
"repositories": {
  "pluggit/storage": {
    "type": "vcs",
    "url": "git@github.com:CMProductions/storage.git"
  }
}
````

Then require it as usual:

``` bash
composer require "pluggit/storage"
```


##Adapters

It provides a generic API for handling common tasks across multiple file storage engines. If you want add a new one, you have to implements ``\Cmp\Storage\AdapterInterface``.

The adapter interface contains these methods:

* `exists`
* `get`
* `getStream`
* `rename`
* `copy`
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

##Mountpoints

Some times you will want use different adapters or strategies depending of the path you are working. We solve this using the MountableVirtualStorage.
MountableVirtualStorage needs be constructed with one VirtualStorage implementation (Adapter or Strategy) and it binds this VirtualStorage to '/'.

After that you can register new mount points.

Example:

```php
 $s3Adapter = new \Cmp\Storage\Adapter\S3AWSAdapter();
 $fileSystemAdapter = new \Cmp\Storage\Adapter\FileSystemAdapter();

 $localMountPoint = new \Cmp\Storage\MountPoint('/tmp', $fileSystemAdapter);
 $publicMountPoint = new \Cmp\Storage\MountPoint('/var/www/app/public', $s3Adapter);

 $vfs = new \Cmp\Storage\MountableVirtualStorage($fileSystemStorage); //bind to /
 $vfs->registerMountPoint($localMountPoint);
 $vfs->registerMountPoint($publicMountPoint);

 $vfs->delete('/tmp/testfile'); //running over filesystem adapter
 $vfs->put('/var/www/app/public/testfile', '..some content..')); //running over AWS S3 adapter
```

* Movement between mount points are also allowed.
* You can register adapters, strategies or any class that implements VirtualStorage


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


##Functions available from storage

### Exists
Check whether a file exists.

### Get
Read a file and return the content.

### GetStream
Retrieves a read-stream for a file.

### Rename
Rename a file.

### Copy
Copy a file.

### Delete
Delete a file or directory (even if is not empty).

### Put
Create a file or update if exists. It will create the missing folders.

### PutStream
Create a file or update if exists. It will create the missing folders.

__*Note:*__ Use stream functions for big files.


### Requirements

* php 5.6


### Contribution

* Fork the repo.
* Fix an issue or add a new adapter or improve something or whatever you want.
* Follow the PSR-2 style.
* Run the tests. (add more tests if is needed).
* Make a pull request.
