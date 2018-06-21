module.exports = [{
    snippet: '@access ${1:<public|protected|private>}',
    description: 'Alias are @public, @protected and @private'
  },
  {
    snippet: '@deprecated ${1:[description]}'
  },
  {
    snippet: '@desc ${1:<description>}',
    description: '<description> supports markdown.\nNormally you don\'t need to use @desc, because first section in doc comment is determined automatically as @desc.'
  },
  {
    snippet: '@example ${1:<JavaScript>}',
    description: 'you can use <caption>...</caption> at first line.'
  },
  {
    snippet: '@experimental ${1:[description]}'
  },
  {
    snippet: '@ignore',
    description: 'The identifier is not displayed in document.'
  },
  /*
  {
    snippet: '{@link ${1:<identifier>}}',
    description: 'e.g. If device spec is low, you can use {@link MySimpleClass}.'
  },
  */
  {
    snippet: '@see ${1:<URL>}'
  },
  {
    snippet: '@since ${1:<version>}'
  },
  {
    snippet: '@todo ${1:<description>}'
  },
  {
    snippet: '@version ${1:<version>}'
  },
  {
    snippet: '@extends {${1:<identifier>}}',
    description: 'Normally you don\'t need to use @extends. because ES2015 has the Class-Extends syntax. however, you can use this tag if you want to explicitly specify.'
  },
  {
    snippet: '@implements {${1:<identifier>}}'
  },
  {
    snippet: '@interface'
  },
  {
    snippet: '@abstract'
  },
  {
    snippet: '@emits {${1:<identifier>}} ${2:[description]}'
  },
  {
    snippet: '@listens {${1:<identifier>}} ${2:[description]}'
  },
  {
    snippet: '@override'
  },
  {
    snippet: '@param {${1:<type>}} ${2:<name>} - ${3:[description]}'
  },
  {
    snippet: '@return {${1:<type>}} ${2:[description]}'
  },
  {
    snippet: '@throws {${1:<identifier>}} ${2:[description]}'
  },
  {
    snippet: '@type {${1:<type>}}'
  },
  {
    snippet: '@external {${1:<identifier>}} ${2:<URL>}'
  },
  {
    snippet: '@typedef {${1:<type>}} ${2:<name>}'
  },
  {
    snippet: '@test {${1:<identifier>}}'
  }
]
