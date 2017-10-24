'use babel'

export default {

  name: 'Annotations',

  grammarScopes: ['*'],

  scope: 'file',

  lintsOnChange: true,

  lint: function (textEditor) {
    return new Promise((resolve) => {
      const filePath = textEditor.getPath()
      const messages = textEditor.getText()
        .split(/\r?\n/)
        .reduce((all, line, lineIdx) => {
          return all.concat(this.extractAnnotations(line, lineIdx, filePath))
        }, [])
        .filter(annotation => this.isValidAnnotiation(textEditor, annotation))
        .map(annotation => this.formatMessage(annotation))

      resolve(messages)
    })
  },

  getConfig () {
    return ['error', 'warning', 'info'].reduce((config, key) => {
      config[key] = (atom.config.get(`linter-annotations.${key}`) || [])
        .map(exp => new RegExp(`(${exp})(\\s*:\\s+)?(.*)`))
      return config
    }, {})
  },

  extractAnnotations (line, lineIdx, filePath) {
    const annotations = []
    const confs = this.getConfig()

    line = this.trimCommentEnd(line)

    Object.keys(confs).forEach((type) => {
      confs[type].forEach((exp) => {
        let match = line.match(exp)
        if (match) {
          annotations.push({ type, match, line, lineIdx, filePath })
        }
      })
    })

    return annotations
  },

  isValidAnnotiation (textEditor, annotation) {
    if (!annotation) return false

    let { scopes } = textEditor.scopeDescriptorForBufferPosition(
      [annotation.lineIdx, annotation.match.index]
    )

    return scopes.filter(scope => scope.match(/^comment\..*/)).length !== 0
  },

  formatMessage (annotation) {
    const key = this.trim(annotation.match[1])
    const text = this.trim(annotation.match[3])

    return {
      severity: annotation.type,
      excerpt: text ? `${key}: ${text}` : key,
      location: {
        file: annotation.filePath,
        position: [
          [annotation.lineIdx, annotation.match.index],
          [annotation.lineIdx, annotation.line.length]
        ]
      }
    }
  },

  trimCommentEnd (str) {
    return this.trim(str)
      .replace(/\s*\*\/$/g, '')
      .replace(/\s*%>$/g, '')
      .replace(/\s*-->.*$/g, '')
  },

  trim (str) {
    return String(str).replace(/(^[\s:]+|[\s:]+$)/g, '')
  },

  capitalize (str) {
    return String(str)
      .toLowerCase()
      .replace(/^[a-z]/, char => char.toUpperCase())
  }
}
