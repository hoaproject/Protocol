![Hoa](http://static.hoa-project.net/Image/Hoa_small.png)

Hoa is a **modular**, **extensible** and **structured** set of PHP libraries.
Moreover, Hoa aims at being a bridge between industrial and research worlds.

# Hoa\Protocol ![state](http://central.hoa-project.net/State/Protocol)

This library provides the `hoa://` protocol, which is a way to abstract resource
accesses.

## Installation

With [Composer](http://getcomposer.org/), to include this library into your
dependencies, you need to require
[`hoa/protocol`](https://packagist.org/packages/hoa/protocol):

```json
{
    "require": {
        "hoa/protocol": "~1.0"
    }
}
```

Please, read the website to [get more informations about how to
install](http://hoa-project.net/Source.html).

## Quick usage

We propose a quick overview of how to list the current tree of the protocol, how
to resolve a `hoa://` path and finally how to add a new node in this tree.

### Explore resources

First of all, to get the instance of the `hoa://` protocol, you should use the
static `getInstance` method on the `Hoa\Protocol\Protocol` class which
represents the root of the protocol tree:

```php
echo Hoa\Protocol\Protocol::getInstance();

/**
 * Might output:
 *   Application
 *     Public
 *   Data
 *     Etc
 *       Configuration
 *       Locale
 *     Lost+found
 *     Temporary
 *     Variable
 *       Cache
 *       Database
 *       Log
 *       Private
 *       Run
 *       Test
 *   Library
 */
```

We see that there is 3 “sub-roots”:

  1. `Application`, representing resources of the application, like public files
     (in the `Public` node), models, resources… everything related to the
     application,
  2. `Data`, representing data required by the application, like configuration
     files, locales, databases, tests etc.
  3. `Library`, representing all Hoa's libraries.

Thus, `hoa://Library/Protocol/README.md` represents the abstract path to this
real file. No matter where you are on the disk, this path will always be valid
and pointing to this file. This becomes useful in an application where you would
like to access to a configuration file like this
`hoa://Data/Etc/Configuration/Foo.php`: Maybe the `Data` directory does not
exist, maybe the `Etc` or `Configuration` directories do not exist neither, but
each node of the `hoa://` tree resolves to a valid directory which contains your
`Foo.php` configuration file. This is an **abstract path for a resource**.

### Resolving a path

We can either resolve a path by using the global `resolve` function or the
`Hoa\Protocol\Protocol::resolve` method:

```php
var_dump(
    resolve('hoa://Library/Protocol/README.md')
);

/**
 * Might output:
 *     string(37) "/usr/local/lib/Hoa/Protocol/README.md"
 */
```

### Register new nodes in the tree

The `hoa://` protocol is a tree. Thus, to add a new “component”/“directory” in
this tree, we must create a node and register it as a child of an existing node.
Thus, in the following example we will create a `Usb` node, pointing to the
`/Volumes` directory, and we will add it as a new sub-root, so an immediate
child of the root:

```php
$protocol   = Hoa\Protocol\Protocol::getInstance();
$protocol[] = new Hoa\Protocol\Node('Usb', '/Volumes/');
```

Here we are. Now, resolving `hoa://Usb/StickA` might point to `/Volumes/StickA`
(if exists):

```php
var_dump(
    resolve('hoa://Usb/StickA')
);

/**
 * Might output:
 *     string(15) "/Volumes/StickA"
 */
```

## Documentation

Different documentations can be found on the website:
[http://hoa-project.net/](http://hoa-project.net/).

## License

Hoa is under the New BSD License (BSD-3-Clause). Please, see
[`LICENSE`](http://hoa-project.net/LICENSE).
