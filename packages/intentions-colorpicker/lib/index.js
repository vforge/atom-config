'use babel'

import {isColor, colorRegexp} from './helpers'

const Package = module.exports = {
  activate() {
    require('atom-package-deps').install()
    this.colorPicker = null
  },
  provideIntentionsList() {
    return {
      grammarScopes: ['*'],
      getIntentions: ({textEditor, bufferPosition}) => {
        const intentions = []

        if (this.colorPicker !== null && isColor(textEditor, bufferPosition)) {
          intentions.push({
            priority: 100,
            icon: 'color-mode',
            title: 'Choose color from colorpicker',
            selected: _ => {
              this.colorPicker.open(textEditor)
            }
          })
        }

        return intentions
      }
    }
  },
  provideIntentionsShow() {
    return {
      grammarScopes: ['*'],
      getIntentions({textEditor, visibleRange}) {
        const matches = []
        const text = textEditor.scanInBufferRange(colorRegexp, visibleRange, function(match) {
          matches.push({
            range: match.range,
            created: Package.intentionCreated
          })
        })
        return matches
      }
    }
  },
  intentionCreated({textEditor, element, marker, matchedText}) {
    element.style.color = matchedText
    element.style.fontWeight = 'bold'
    element.addEventListener('click', function() {
      textEditor.setCursorBufferPosition(marker.getBufferRange().start)
      if (Package.colorPicker) {
        Package.colorPicker.open(textEditor)
      }
    })
  },
  consumeColorPicker(colorPicker) {
    this.colorPicker = colorPicker
  }
}
