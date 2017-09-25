'use babel'

import {Numbers} from './numbers'

const Package = module.exports = {
  activate() {
    require('atom-package-deps').install()
  },
  provideIntentionsShow() {
    return {
      grammarScopes: ['*'],
      getIntentions({textEditor}) {
        const matches = []
        const textEditorElement = atom.views.getView(textEditor)
        for (const element of Array.from(textEditorElement.querySelectorAll('::shadow .numeric'))) {
          const position = element.getBoundingClientRect()
          const bufferPosition = textEditor.bufferPositionForScreenPosition(textEditorElement.component.screenPositionForMouseEvent({
            clientX: position.left,
            clientY: position.top
          }))
          matches.push({
            range: [[bufferPosition.row, bufferPosition.column], [bufferPosition.row, bufferPosition.column + element.textContent.length]],
            created: Package.intentionCreated
          })
        }
        return matches
      }
    }
  },
  intentionCreated({element, marker, textEditor}) {
    element.addEventListener('click', function() {
      textEditor.setCursorBufferPosition(marker.getBufferRange().start)
    })
    element.addEventListener('mousedown', function(e) {
      e.stopImmediatePropagation()
      e.preventDefault()
      const numbers = new Numbers({element, marker, textEditor})
      numbers.activate(e)
    })
  }
}
