'use babel'

import path from 'path'
import Provider from '../lib/linter-annotations-provider'

describe('Provider', () => {
  beforeEach(() => waitsForPromise(async () => {
    await atom.packages.activatePackage('linter-annotations')
    await atom.packages.activatePackage('language-javascript')
    await atom.packages.activatePackage('language-ruby')
  }))

  describe('capitalize()', () => {
    it('Should capitalize string', () => {
      expect(Provider.capitalize('fOO')).toEqual('Foo')
    })
  })

  describe('trim()', () => {
    it('Should trim string', () => {
      expect(Provider.trim('    foo    ')).toEqual('foo')
    })
  })

  describe('trimCommentEnd()', () => {
    it('Should strip comment endings', () => {
      expect(Provider.trimCommentEnd('/* test */')).toEqual('/* test')
      expect(Provider.trimCommentEnd('<%# test %>')).toEqual('<%# test')
      expect(Provider.trimCommentEnd('<!-- test --> remove')).toEqual('<!-- test')
    })
  })

  describe('lint()', () => {
    it('should retuns messages for `fixture.js`', () => {
      waitsForPromise(() => {
        return atom.workspace.open(path.join(__dirname, 'files', 'fixture.js'))
          .then((editor) => Provider.lint(editor))
          .then((messages) => {
            expect(messages.length).toEqual(7)

            const errors = messages.filter(message => message.severity === 'error')
            expect(errors.length).toEqual(1)
            expect(errors[0].location.position).toEqual([[5, 3], [5, 38]])
            expect(errors[0].excerpt).toEqual('FIXME: Something that has to be done')

            const warnings = messages.filter(message => message.severity === 'warning')
            expect(warnings.length).toEqual(5)
            expect(warnings[0].location.position).toEqual([[6, 3], [6, 37]])
            expect(warnings[0].excerpt).toEqual('TODO: Something that should be done')

            expect(warnings[1].location.position).toEqual([[7, 3], [7, 38]])
            expect(warnings[1].excerpt).toEqual('TODO: Something that should be done')

            expect(warnings[2].location.position).toEqual([[8, 3], [8, 39]])
            expect(warnings[2].excerpt).toEqual('TODO: Something that should be done')

            expect(warnings[3].location.position).toEqual([[9, 3], [9, 38]])
            expect(warnings[3].excerpt).toEqual('TODO: Something that should be done')

            expect(warnings[4].location.position).toEqual([[10, 3], [10, 7]])
            expect(warnings[4].excerpt).toEqual('TODO')

            const infos = messages.filter(message => message.severity === 'info')
            expect(infos.length).toEqual(1)
            expect(infos[0].location.position).toEqual([[4, 3], [4, 30]])
            expect(infos[0].excerpt).toEqual('NOTE: Something good to know')
          })
      })
    })

    it('should retuns 1 messages in `fixture.erb`', () => {
      waitsForPromise(() => {
        const grammar = atom.grammars.getGrammars().find(({ name }) => name === 'HTML (Ruby - ERB)')
        return atom.workspace.open(path.join(__dirname, 'files', 'fixture.erb'))
          .then(editor => (editor.setGrammar(grammar) || Provider.lint(editor)))
          .then(messages => {
            expect(messages.length).toEqual(1)

            const warnings = messages.filter(message => message.severity === 'warning')
            expect(warnings.length).toEqual(1)
            expect(warnings[0].location.position).toEqual([[1, 4], [1, 22]])
            expect(warnings[0].excerpt).toEqual('TODO: Do somehting')
          })
      })
    })
  })
})
