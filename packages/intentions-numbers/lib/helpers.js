'use babel'

/* @flow */

import {Disposable} from 'atom'

export function disposableEvent(element: HTMLElement, event: string, callback: Function): Disposable {
  element.addEventListener(event, callback)
  return new Disposable(function() {
    element.removeEventListener(event, callback)
  })
}

export function applyDifference(value: string, difference: number, shouldRound: boolean): string {
  value = value === '0' ? '1' : value
  let newValue = + value + (difference * parseInt(value))
  if (shouldRound) {
    newValue = String(Math.round(newValue))
  } else {
    newValue = newValue.toFixed(2)
    if (newValue.slice(-2) === '00') {
      newValue = newValue.slice(0, -3)
    } else if (newValue.slice(-1) === '0') {
      newValue = newValue.slice(0, -1)
    }
  }
  return newValue
}
