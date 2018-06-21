# Atom ESDoc package

![dependency status](https://david-dm.org/iocast/atom-esdoc.svg)
![GitHub version](https://badge.fury.io/gh/iocast%2Fatom-esdoc.svg)


Atom package for quick esdoc comment generation.
Forked from [Atom easy JSDoc by Tom Andrews](https://github.com/tgandrews/atom-easy-jsdoc)

## Install

```bash
apm install esdoc
```

## Usage

Control-Shift-d or Control-Shift-j to add comment templates.

To add comments for any piece of code, position the cursor anywhere on the line preceding the line you wish to comment on.
```javascript
/**
 * functionComment - description
 *
 * @param  {type} argA description
 * @param  {type} argB description
 * @param  {type} argC description
 * @return {type}      description
 */
function functionComment (argA, argB, argC) {
    return 'esdoc';
}
```

```javascript
/**
 * This is an empty comment
 */
var a = 'A';
```

## Autocontinue

Comments now are automatically continued if the user hits enter (new line event) while inside of a block (`/**...`, `//` etc.).

## Autocompletion

Pressing **enter** or **tab** after `/**` will yield a new line and will close the comment, if the following line is a valid JavaScript code.


## Contribute
I'll be adding features periodically, however bug fixes, feature requests, and pull requests are all welcome.


## Release

* Add info to `CHANGELOG.md`.
* Push everything

Update registry:

```
/Applications/Atom.app/Contents/Resources/app/apm/bin/apm publish vX.X.X
```
