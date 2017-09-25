'use babel'

/* @flow */

import {CompositeDisposable} from 'atom'
import debounce from 'sb-debounce'
import {disposableEvent, applyDifference} from './helpers'
import type {TextEditor, TextEditorMarker} from 'atom'

const CURSOR_CLASS = 'number-range-cursor'

export class Numbers {
  marker: TextEditorMarker;
  element: HTMLElement;
  textEditor: TextEditor;
  checkpoint: ?number;
  subscriptions: CompositeDisposable;

  constructor({element, marker, textEditor}: {element: HTMLElement, marker: TextEditorMarker, textEditor: TextEditor}) {
    this.marker = marker
    this.element = element
    this.textEditor = textEditor
    this.checkpoint = null
    this.subscriptions = new CompositeDisposable()
  }
  activate(event: MouseEvent) {
    const x = event.screenX
    const value = this.textEditor.getTextInBufferRange(this.marker.getBufferRange())
    const shouldRound = value.indexOf('.') === -1

    atom.views.getView(this.textEditor).classList.add(CURSOR_CLASS)

    this.checkpoint = this.textEditor.getBuffer().createCheckpoint()
    this.subscriptions.add(disposableEvent(window, 'mousemove', debounce(e => {
      const difference = (e.screenX - x) / 300
      const newValue = applyDifference(value, difference, shouldRound)
      this.textEditor.setTextInBufferRange(this.marker.getBufferRange(), newValue)
    }, 16, true)))
    this.subscriptions.add(disposableEvent(window, 'mouseup', () => {
      this.dispose()
    }))
  }
  dispose() {
    if (this.checkpoint !== null) {
      this.textEditor.getBuffer().groupChangesSinceCheckpoint(this.checkpoint)
    }
    atom.views.getView(this.textEditor).classList.remove(CURSOR_CLASS)
    this.subscriptions.dispose()
  }
}
