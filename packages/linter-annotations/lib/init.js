'use babel'

import LinterEsprimaProvider from './linter-annotations-provider'
import { install } from 'atom-package-deps'

const { atom } = global

module.exports = {

  config: {
    error: {
      title: 'Error keys',
      description: 'List of keys divided by ","',
      type: 'array',
      default: ['FIXME'],
      items: {
        type: 'string'
      }
    },
    warning: {
      title: 'Warning keys',
      description: 'List of keys divided by ","',
      type: 'array',
      default: ['TODO'],
      items: {
        type: 'string'
      }
    },
    info: {
      title: 'Info keys',
      description: 'List of keys divided by ","',
      type: 'array',
      default: ['NOTE'],
      items: {
        type: 'string'
      }
    }
  },

  activate () {
    if (!atom.inSpecMode()) {
      install('linter-annotations', true)
    }
  },

  provideLinter () { return LinterEsprimaProvider }
}
