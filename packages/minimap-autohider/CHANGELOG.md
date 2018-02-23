# Changelog

## 1.5.4 - Clean up styling/less variable logic (@cbarrick) and basic unit tests

**Patch**

- removed unit tests, config and setTimeout makes them ahrd to deal with
  - maybe later
- visible percentage is now handled by changing a less variable
  - makes for cleaner code
  - matches other config option logic
- `.scrolling` is no longer used
- changing visible percentage now requires reload, same as transition duration

## 1.5.3 - last change was a hardcore typo

**Minor**

- element !== editor, oops

## 1.5.2 - editor.editorElement -> editor.element

**Minor**

- editorElement is private, and being removed in the future
  - switch to element, which is public and references the same thing

## 1.5.1 - forgot to check the ShowOnHover, changed it to a dropdown

**Minor**

- check HiddenPercentage instead of the ShowOnHover config
- remove unused clickHandler method
- switch observers to `onDidChange` so old value can be used

## 1.5.0 - Deprecate `.scrolling` class, replace yarn with npm@5, add event options

**Major**

- Deprecate `.scrolling` class
  - can still be used, but I'm now using `.autovisible` in the package itself
- add an option to disable visibility on scroll
- add an option for showing the minimap on hover
- add an option for showing the minimap on click

**Minor**

- replace yarn with npm@5, because I'm a hipster

## 1.4.0 - Add error handling for writeFile for Node v7

**Major**

- Add callback to writeFile for saving settings to `.less`
- Fire off `atom.notifications.addWarning` if writeFile fails

**Minor**

- none

## 1.3.3 - Fix TransitionDuration Bug

**Minor**

- reenable transition duration
  - add disclaimer that it requires a reload
  - add a `default.less` file in case `custom.less` is being written as it's read

## 1.3.2 - Patch TransitionDuration Bug

**Minor**

- remove TransitionDuration setting until I can actually fix it
  - Broke windows in 1.14.4

## 1.3.1 - Configurable Transitions

**Minor**

- reformat Changelog (so meta)
- reformat Readme
- new gifs

## 1.3.0 - Configurable Transitions

**Major**

- added a config option for transition duration
- had to add file writing logic since less can't see config
- added custom.less (autogenerated)
- update spec to use config transition duration

## 1.2.0 - The Performance-ening

**Major**

- transition now works on transform, not left, for silky smooth frames
- updated readme
- added delay to opacity spec

**Minor**

- added support for left-aligned minimaps (previously broken)

## 1.1.0 - Renamed

**Major**

- this is really a breaking change, but I'm a rebel
- renamed to `minimap-autohider` instead of keeping the name or adding `-2`

**Minor**

- removed an inconsistent spec

## 1.0.0 - Fork!

**Breaking**

- Convert to vanilla node4-compliant javascript
- remove `::shadow` selector (deprecated in Atom 1.13)
- fix refererence to `editor.editorElement.onDidChangeScrollTop` (deprecated in Atom 1.13)

**Major**

- add settings config, currently just for custom delay before hiding
- added working specs

## 0.10.1

**Major**

- merge ncreep’s hover class change to keep the minimap open on hover

## 0.9.0 - First Release