## 3.0.0
### Major changes
* [PHP 7.1 is now required to _run_ the core](https://gitlab.com/php-integrator/core/issues/81)
  * Code that is analyzed can still be anything from PHP 5.2 all the way up to 7.1.
* [PHP 7.1 is now properly supported](https://gitlab.com/php-integrator/core/issues/40)
  * It already parsed before, but this involves properly detecting the new scalar types, multiple exception types, ...
* [Various lists containing large data, such as the constant, function, structure and namespace list are no longer rebuilt every time a command to fetch them was invoked](https://gitlab.com/php-integrator/core/issues/122)
  * This is primarily used by the autocompletion Atom package, which will benefit from an improvement in response times and minor hiccups.
* [HTML will no longer be stripped from docblock descriptions and text (except in places where it's not allowed, such as in types)](https://gitlab.com/php-integrator/core/issues/7)
  * This means you can use HTML as well as markdown in docblocks and the client side is now able to properly format it.
*  [PhpStorm's open source stubs are now used for indexing built-in structural elements](https://gitlab.com/php-integrator/core/issues/2)
  * Reflection in combination with PHP documentation data is no longer used to index built-in items.
  * These provide more accurate parameter type, return type and default value information than the documentation for the purpose of static analysis (e.g. `DateTime::createFromFormat`).
  * This reduces the maintenance burden of having two separate indexing procedures and lowers the test surface.
  * `isBuiltin` was removed for classlikes, global functions and global constants. This could previously be used for features such as code navigation since there was no physical file for the built-in items. Clients can now remove conditional code checking for this property as bulit-in items are indexed like any other code.
* [(PhpStorm) Meta files are now supported in a very rudimentary way, albeit with some restrictions (which may be lifted in the future)](https://gitlab.com/php-integrator/core/issues/10)
  * Only the `STATIC_METHOD_TYPES` setting is supported.
  * Only [the first version of the format](https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata#PhpStormAdvancedMetadata-Deprecated:Legacymetadataformat(2016.1andearlier)) is supported, as this is likely the most widely used variant.
  * The settings must be located in a namespace called `PHPSTORM_META`. It is recommended to place it in a file called `.phpstorm.meta.php` for compatibility with PhpStorm, but in theory any PHP file can contain this namespace.
  * The "templated" argument must always be the first one.
  * The class name must directly refer to the class, i.e. meta information for parent classes or interfaces will not automatically cascade down to children and implementors.

```php
// ----- .phpstorm.meta.php
<?php

namespace PHPSTORM_META {
    use App;

    $STATIC_METHOD_TYPES = [
        App\ServiceLocator::get('') => [
            'someService' instanceof App\SomeService
        ]
    ];
}
```
```php
// ----- src/App/ServiceLocator.php
<?php

namespace App;

class ServiceLocator
{
    public function get(string $name)
    {
        // ...
    }
}
```
```php
// ----- src/app/Main.php
<?php

$serviceLocator = new ServiceLocator();
$serviceLocator->get('someService')-> // Autocompletion for App\SomeService
```

### Linting
* [Some docblock warnings have been promoted to errors](https://gitlab.com/php-integrator/core/issues/33)
* [Complain about missing ampersand signs for reference parameters in docblocks](https://gitlab.com/php-integrator/core/issues/32)
* [Don't complain about type mismatches in docblocks when the qualifications of the types are different](https://gitlab.com/php-integrator/core/issues/89)
* [For docblock parameters, specializations of the type hint are now allowed to narrow down class types](https://gitlab.com/php-integrator/core/issues/35)

```php
<?php

// For interfaces
interface I {}
class A implements I {}
class B implements I {}

/**
 * @param A|B $i <-- Ok, A and B both implement I and pass the type hint.
 */
function foo(I $i) {}

// For classes
class C {}
class A extends C {}
class B extends C {}

/**
 * @param A|B $c <-- Ok, A and B both extend C and pass the type hint.
 */
function foo(C $c) {}
```

* [Processing more complex docblock types, such as compound types containing multiple array specializations and null, has substantially improved and should complain less about valid combinations](https://gitlab.com/php-integrator/core/issues/11)
* Linting messages for classlikes, functions and methods will now be properly shown over their name instead of on the first character of their definition
* Disabling unknown global constant linting now works again
* For docblock parameters, compound types containing class types will now be resolved properly (previously, only a single type was resolved)
* It is now possible to disable linting missing documentation separately from linting docblock correctness
* The fully qualified name of a global function that wasn't found (instead of just the local name)
* The fully qualified name of a global constant that wasn't found (instead of just the local name)
* Instead of an associative array, a flat list of error and warning messages will now be returned
  * The list will include the message and the range (offsets) it applies in. Other data, including the line number, is no longer included.
* Messages have become more concise and verbal baggage has been removed from them
  * Mentioning the name was redundant as the location of the linter message provides the necessary context.
  * Instead of `Docblock for constant FOO is missing @var tag`, the message will now read `Constant docblock is missing @var tag`.
  * This also increases readability, as markdown is no longer used (since it is not allowed by the language server protocol nor supported by Atom's linter v2 anymore).

### Various enhancements
* Updated dependencies
* Traits using other traits is now supported
* Default values for parameters will be used to deduce their type (if it could not be deduced from the docblock or a type hint is omitted)
* Fatal server errors will now include a much more comprehensive backtrace, listing previous exceptions in the exception chain as well
* Specialized array types containing compound types, such as `(int|bool)[]`, are now supported. This primarily affects docblock parameter type linting, as it's currently not used anywhere else
* Parsing default values of structural elements now doesn't happen twice during indexing anymore, improving indexing performance

### Various bugfixes
* [Fix incorrect type deduction for global functions without leading slash](https://github.com/php-integrator/atom-base/issues/284)
* [Deducing the type of anonymous classes no longer generates errors](https://gitlab.com/php-integrator/core/issues/106)
* [Requests for files that are not in the index will now be properly denied where applicable instead of resulting in a logic exception being thrown](https://gitlab.com/php-integrator/core/issues/104)
* [When a circular dependency or reference occurs, requests for the culprit class should now continue working, albeit without the duplicate information](https://gitlab.com/php-integrator/core/issues/79)
* Fixed the type of defines not being properly deduced from their value
* Fix not being able to use the same namespace multiple times in a file
* Fix no namespace (i.e. before the first namespace declaration) being confused for an anonymous namespace when present
* Fixed trait aliases without an explicit access modifier causing the original access modifier getting lost
* The docblock parser will no longer trip over leading and trailing bars around compound types (e.g. `@param string| $test` will become `@param string $test`)
* The variable defined in a `catch` block wasn't always being returned in the variable list

### Structural changes (mostly relevant to clients)
* [A new command `Tooltips` to provide tooltips has been added](https://gitlab.com/php-integrator/core/issues/86)
* [The invocation info command has been reworked into the `SignatureHelp` command (call tips)](https://gitlab.com/php-integrator/core/issues/92)
  * This command operates in a similar fashion, but provides full information over the available signatures instead of just information about the invocation, leaving the caller to handle further type determination and handling.
* `SemanticLint` has been renamed to just `Lint`, as it also lints syntax errors
* The class list will now only provide fields directly relevant to the class.
  * Most of the related data, such as methods and constants, were already being filtered out for performance reasons.
  * In order to fetch more information about a class, such as its parents, you now have to manually fetch this using the class info command.
* `isNullable` will no longer be returned for function and method parameters
  * This was inconsistent with return type information for functions and methods (it also didn't have an `isNullable`).
  * It didn't properly take docblock information into account, so it was actually more of an "is type hint nullable".
  * Whether or not a type is nullable, taking all factors into account (the type hint, a default value of `null`, the docblock types), can already be deduced from the actual type list (`null` will be present in it).
  * Whether the type hint should be nullable, which can be important when overriding methods, where the signatures must match, is now no longer something the client needs to worry about as the `typeHint` property will now include a PHP 7.1 question mark if the original type hint also included one.
* Data related to `throws` is now returned as an array of arrays, each with a `type` and a `description` key instead of an associative array mapping the former to the latter
  * This is recommended by [phpDocumentor](https://phpdoc.org/docs/latest/references/phpdoc/tags/throws.html).
  * This allows the same exception type to be referenced multiple times to describe it being thrown in different situations.
* The `LocalizeType` command will no longer make any chances to names that aren't fully qualified, as they are already "local" as they are
* The `verbose` option for the `reindex` command was removed. It was a hidden feature and hasn't been used in quite some time (it was originally used for testing, but actual tests have replaced it)
* Namespaces supplied by the `NamespaceList` command will now always have a start and end line (no more `null` for the last namespace)
* The `class` keyword returned as constant will now have a file, start line and end line (which are the same as the class it belongs to). It will also have a default value which is equal to the class name without leading slash
* Anonymous namespaces supplied by the `NamespaceList` command will now always have `null` as name instead of an empty string for explicitly anonymous namespaces and `null` for implicitly anonymous namespaces, as they are both the same
* The `shortName` property for classlikes is now called `name`, the FQCN can now be found in `fqcn`. This is more logical than having `name` contain the FQCN and `shortName` contain the short name
* `declaringClass.name` was renamed to `declaringClass.fqcn` for consistency
* The return type hint for functions and methods and type hints for parameters will now always be an FQCN in the case of non-scalar types
  * The non-resolved type provided no context and could be ambiguous.
  * If the type needs to be relative to local imports, you can always localize the type using the appropriate command.
    * In the case of the atom-refactoring package, this will fix the issue where stubbing an interface method would get the return type hint wrong in the stub, because it was attempting to localize a type that wasn't fully qualified in the first place (at least if the original interface method also didn't use an FQCN).
* Fixed the short and long description for classlikes being an empty string instead of `null` when not present
* Fixed the short, long and type description for global and class constants being an empty string instead of `null` when not present
* Fixed the short, long and type description for properties being an empty string instead of `null` when not present
* Fixed the short, long and return description for functions and methods being an empty string instead of `null` when not present
* Namespaces provided by the namespace list command will now also include the path to the file that they are present in
* `declaringStructure.name` was renamed to `declaringStructure.fqcn` for consistency
* `isAbstract`, `isFinal`, `isAnnotation`, `interfaces` and `directInterfaces` will no longer be returned for interfaces and traits as they are only relevant for classes
* `directImplementors` will no longer be returned for classes and traits as it is only relevant for interfaces
* `directTraitUsers` will no longer be returned for classes and interfaces as it is only relevant for traits
* `parents`, `directParents` and `directChildren` will no longer be returned for traits as they are only relevant for classes and interfaces
* `traits` and `directTraits` will no longer be returned for interfaces as they are only relevant for classes and traits
* `isPublic`, `isProtected` and `isPrivate` will no longer be returned for global constants as they are only relevant for class constants
* `fqcn` will no longer be returned for class constants as it is only relevant for global constants
* `fqcn` will no longer be returned for methods (class functions) as it is only relevant for global functions

## 2.1.6
* Fix error with incomplete default values for define expressions causing the error `ConfigurableDelegatingNodeTypeDeducer::deduce() must implement interface PhpParser\Node, null given` (https://gitlab.com/php-integrator/core/issues/87).
* Fix this snippet of code causing php-parser to generate a fatal error:

```php
<?php

function foo()
{
    return $this->arrangements->filter(function (LodgingArrangement $arrangement) {
        return
    })->first();
}
```

## 2.1.5
* Indexing performance was slightly improved.
* Fix regression where complex strings with more complex interpolated values wrapped in parantheses were failing to parse, causing indexing to fail for files containing them (https://gitlab.com/php-integrator/core/issues/83).

## 2.1.4
* Fix corner case with strings containing more complex interpolated values, such as with method calls and property fetches, failing to parse, causing indexing to fail for files containing them (https://gitlab.com/php-integrator/core/issues/83).

## 2.1.3
* Fix corner case with HEREDOCs containing interpolated values failing to parse, causing indexing to fail for files containg them (https://gitlab.com/php-integrator/core/issues/82).
* Default value parsing failures will now throw `LogicException`s.
  * This will cause them to crash the server, but that way they can be debugged as parsing valid PHP code should never fail.

## 2.1.2
* Fix `@throws` tags without a description being ignored.
* Fix symlinks not being followed in projects that have them.
* Terminate if `mbstring.func_overload` is enabled, as it is not compatible.

## 2.1.1
* Fix the `static[]` not working properly when indirectly resolved from another class (https://github.com/php-integrator/atom-autocompletion/issues/85).

## 2.1.0
* A couple dependencies have been updated.
* Composer dependencies are now no longer in Git.
* Fix `self`, `static` and `$this` in combination with array syntax not being resolved properly (https://github.com/php-integrator/atom-autocompletion/issues/85).

## 2.0.2
* Fix a database transaction not being terminated correctly when indexing failed.
* Fix constant and property default values ending in a zero (e.g. `1 << 0`) not being correctly indexed.
* Fix an error message `Call to a member function handleError() on null` showing up when duplicate use statements were found.

## 2.0.1
* Fix the class keyword being used as constant as default value for properties generating an error.
* Fix (hopefully) PHP 7.1 nullable types generating parsing errors.
  * This only fixes them generating errors during indexing, but they aren't fully supported just yet.

## 2.0.0
### Major changes
* PHP 5.6 is now required. PHP 5.5 has been end of life for a couple of months now.
  * If you're running the server and upgrading is truly not an option at the moment, you can temporarily switch back the version check in the Main.php file as currently no PHP 5.6 features are used yet. However, in due time, they might.
* A great deal of refactoring has occurred, which paved the way for performance improvements in several areas, such as type deduction.
  * Indexing should be slightly faster.
  * Everything should feel a bit more responsive.
  * Semantic linting should be significantly faster, especially for large files.
* Passing command line arguments is no longer supported and has been replaced with a socket server implementation. This offers various benefits:
  * Bootstrapping is performed only once, allowing for responses from the server with lower latency.
  * Only a single process is managing a single database. This should solve the problems that some users had with the database suddenly being locked or unwritable.
  * Only a single process is spawned. No more spawning concurrent processes to perform different tasks, which might heavily burden the CPU on a user's system as well as has a lot of overhead.
    * Sockets will also naturally queue requests, so they are handled one by one as soon as the server is ready.
  * Caching is no longer performed via file caching, but entirely in memory. This means users that don't want to, don't know how to, or can't set up a tmpfs or ramdisk will now also benefit from the better performance of memory caching.
    * Additionally this completely obsoletes the need for wonky file locks and concurrent cache file access.

### Commands
* A new command, `namespaceList`, is now available, which can optionally be filtered by file, to retrieve a list of namespaces. (thanks to [pszczekutowicz](https://github.com/pszczekutowicz))
* `resolveType` and `localizeType` now require a `kind` parameter to determine the kind of the type (or rather: name) that needs to be resolved.
  * This is necessary to distinguish between classlike, constant and function name resolving based on use statements. (Yes, duplicate use statements may exist in PHP, as long as their `kind` is different).
* `implementation` changed to `implementations` because the data returned must be an array instead of a single value. The reasoning behind this is that a method can in fact implement multiple interface methods simultaneously (as opposed to just one).
* The `truncate` command was merged into the `initialize` command. To reinitialize a project, simply send the initialize command a second time.
* `invocationInfo` will now also return the name of the invoked function, method or constructor's class.
* `invocationInfo` now returns `method` instead of `function` for class methods (as opposed to global functions).
* `deduceTypes` now expects the full expression to be passed via the new `expression` parameter. The `part` parameter has been removed.

### Global functions and constants
* Unqualified global constants and functions will now correctly be resolved.
* Semantic linting was incorrectly processing unqualified global function and constant names.
* Use statements for constants (i.e. `use const`) and functions (i.e. `use function`) will now be properly analyzed when checking for unused use statements.

### Docblocks and documentation
* In documentation for built-in functions, underscores in the description were incorrectly escaped with a slash.
* In single line docblocks, the terminator `*/` was not being ignored (and taken up in the last tag in the docblock).
* Class annotations were sometimes being picked up as being part of the description of other tags (such as `@var`, `@param`, ...).
* `@noinspection` is no longer linted as invalid tag, so you can now not be blinded by errors when reading the code of a colleague using PhpStorm.
* Variadic parameters with type hints were incorrectly matched with their docblock types and, by consequence, incorrectly reported as having a mismatching type.

### Type deduction
* The indexer was assigning an incorrect type to variadic parameters. You can now use elements of type hinted variadic parameters as expected in a foreach:

```php
protected function foo(Bar ...$bars)
{
    foreach ($bars as $bar) {
        // $bar is now an instance of Bar.
    }
}
```

* The type deducer can now (finally) cope with conditionals on properties, next to variables:

```php
class Foo
{
    /**
     * @var \Iterator
     */
    protected $bar;

    public function fooMethod()
    {
        // Before:
        $this->bar = new \SplObjectStorage();
        $this->bar-> // Still lists members of Iterator.

        if ($this->bar instanceof \DateTime) {
            $this->bar-> // Still lists members of Iterator.
        }

        // After:
        $this->bar = new \SplObjectStorage();
        $this->bar-> // Lists members of SplObjectStorage.

        if ($this->bar instanceof \DateTime) {
            $this->bar-> // Lists members of DateTime.
        }
    }
}
```

* Type deduction with conditionals has improved in many ways, for example:

```php
if ($a instanceof A || $a instanceof B) {
    if ($a instanceof A) {
        // $a is now correctly A instead of A|B.
    }
}
```

```php
$b = '';

if ($b) {
    // $b is now correctly string instead of string|bool.
}
```

* Array indexing will now properly deduce the type of array elements if the type of the array is known:

```php
/** @var \DateTime[] $foo */
$foo[0]-> // The type is \DateTime.
```

### Other
* The default value of defines was not always correctly being parsed.
* Heredocs were not correctly being parsed when analyzing default values of constants and properties.
* Attempting to index a file that did not meet the passed allowed extensions still caused it to be added to the index.
* Assigning a global constant to something caused the type of that left operand to become the name of the constant instead.
* The `class` member that each class has since PHP 5.5 (that evaluates to its FQCN) is now returned along with class constant data.
* Use statements were incorrectly reported as unused when they were being used as extension or implementation for anonymous classes.
* PHP setups with the `cli.pager` option set will now no longer duplicate JSON output. (thanks to [molovo](https://github.com/molovo))
* Parantheses inside strings were sometimes interfering with invocation info information, causing the wrong information to be returned.
* When encountering UTF-8 encoding errors, a recovery will be attempted by performing a conversion (thanks to [Geelik](https://github.com/Geelik)).
* The type of built-in global constants is now deduced from their default value as Reflection can't be used to fetch their type nor do we have any documentation data about them.
* Previously a fix was applied to make FQCN's actually contain a leading slash to clearly indicate that they were fully qualified. This still didn't happen everywhere, which has been corrected now.
* When a class has a method that overrides a base class method and implements an interface method from one of its own interfaces, both the `implementation` and `override` data will now be set as they are both relevant.
* Parent members of built-in classlikes were being indexed twice: once for the parent and once for the child, which was resulting in incorrect inheritance resolution results, unnecessary data storage and a (minor) performance hit.
* Built-in interfaces no longer have `isAbstract` set to true. They _are_ abstract in a certain sense, but this property is meant to indicate if a classlike has been defined using the abstract keyword. It was also not consistent with the behavior for non-built-in interfaces.
  * Built-in interface methods also had `isAbstract` set to `true` instead of `false`.

## 1.2.0
* Initial split from the [php-integrator/atom-base](https://github.com/php-integrator/atom-base) repository. See [its changelog](https://github.com/php-integrator/atom-base/blob/master/CHANGELOG.md) for what changed in older versions.
