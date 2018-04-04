'use babel'

import path from 'path'
import Provider from '../lib/linter-annotations-provider'

const { atom, beforeEach, describe, expect, it, waitsForPromise } = global

describe('Provider', () => {
  beforeEach(() => waitsForPromise(async () => {
    await atom.packages.activatePackage('linter-annotations')
    await atom.packages.activatePackage('language-javascript')
    await atom.packages.activatePackage('language-ruby')
    await atom.packages.activatePackage('language-python')
    await atom.packages.activatePackage('language-shellscript')
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
    it('should returns 7 messages in `fixture.js`', () => {
      waitsForPromise(() => {
        const grammar = atom.grammars.getGrammars().find(({ name }) => name === 'JavaScript')
        return atom.workspace.open(path.join(__dirname, 'files', 'fixture.js'))
          .then(editor => (editor.setGrammar(grammar) || Provider.lint(editor)))
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

    it('should returns 1 messages in `fixture.erb`', () => {
      waitsForPromise(() => {
        const grammar = atom.grammars.getGrammars().find(({ name }) => name === 'HTML (Ruby - ERB)')
        return atom.workspace.open(path.join(__dirname, 'files', 'fixture.erb'))
          .then(editor => (editor.setGrammar(grammar) || Provider.lint(editor)))
          .then(messages => {
            expect(messages.length).toEqual(1)

            const warnings = messages.filter(message => message.severity === 'warning')
            expect(warnings.length).toEqual(1)
            expect(warnings[0].location.position).toEqual([[1, 4], [1, 22]])
            expect(warnings[0].excerpt).toEqual('TODO: Do something')
          })
      })
    })

    it('should return 84 messages in `fixture.py`', () => {
      waitsForPromise(() => {
        const grammar = atom.grammars.getGrammars().find(({ name }) => name === 'Python')
        return atom.workspace.open(path.join(__dirname, 'files', 'fixture.py'))
          .then(editor => (editor.setGrammar(grammar) || Provider.lint(editor)))
          .then(messages => {
            expect(messages.length).toEqual(84)
            const warnings = messages.filter(message => message.severity === 'warning')
            const textAA = messages.filter(message => message.excerpt === 'TODO: AA')
            const textEmpty = messages.filter(message => message.excerpt === 'TODO')
            expect(warnings.length).toEqual(84)
            expect(textEmpty.length).toEqual(30)
            expect(textAA.length).toEqual(54)
            expect(warnings[0].location.position).toEqual([[1, 5], [1, 9]])
            expect(warnings[1].location.position).toEqual([[2, 5], [2, 10]])
            expect(warnings[2].location.position).toEqual([[5, 1], [5, 5]])
            expect(warnings[3].location.position).toEqual([[6, 1], [6, 6]])
            expect(warnings[4].location.position).toEqual([[7, 2], [7, 6]])
            expect(warnings[5].location.position).toEqual([[8, 2], [8, 7]])
            expect(warnings[6].location.position).toEqual([[9, 3], [9, 7]])
            expect(warnings[7].location.position).toEqual([[10, 3], [10, 8]])
            expect(warnings[8].location.position).toEqual([[11, 3], [11, 7]])
            expect(warnings[9].location.position).toEqual([[12, 3], [12, 8]])
            expect(warnings[10].location.position).toEqual([[13, 5], [13, 9]])
            expect(warnings[11].location.position).toEqual([[14, 5], [14, 10]])
            expect(warnings[12].location.position).toEqual([[15, 4], [15, 8]])
            expect(warnings[13].location.position).toEqual([[16, 4], [16, 9]])
            expect(warnings[14].location.position).toEqual([[17, 7], [17, 11]])
            expect(warnings[15].location.position).toEqual([[18, 7], [18, 12]])
            expect(warnings[16].location.position).toEqual([[19, 1], [19, 8]])
            expect(warnings[17].location.position).toEqual([[20, 2], [20, 9]])
            expect(warnings[18].location.position).toEqual([[21, 3], [21, 10]])
            expect(warnings[19].location.position).toEqual([[22, 2], [22, 9]])
            expect(warnings[20].location.position).toEqual([[23, 3], [23, 10]])
            expect(warnings[21].location.position).toEqual([[24, 4], [24, 11]])
            expect(warnings[22].location.position).toEqual([[25, 3], [25, 10]])
            expect(warnings[23].location.position).toEqual([[26, 4], [26, 11]])
            expect(warnings[24].location.position).toEqual([[27, 5], [27, 12]])
            expect(warnings[25].location.position).toEqual([[28, 1], [28, 9]])
            expect(warnings[26].location.position).toEqual([[29, 2], [29, 10]])
            expect(warnings[27].location.position).toEqual([[30, 3], [30, 11]])
            expect(warnings[28].location.position).toEqual([[31, 2], [31, 10]])
            expect(warnings[29].location.position).toEqual([[32, 3], [32, 11]])
            expect(warnings[30].location.position).toEqual([[33, 4], [33, 12]])
            expect(warnings[31].location.position).toEqual([[34, 3], [34, 11]])
            expect(warnings[32].location.position).toEqual([[35, 4], [35, 12]])
            expect(warnings[33].location.position).toEqual([[36, 5], [36, 13]])
            expect(warnings[34].location.position).toEqual([[37, 1], [37, 6]])
            expect(warnings[35].location.position).toEqual([[38, 2], [38, 7]])
            expect(warnings[36].location.position).toEqual([[39, 3], [39, 8]])
            expect(warnings[37].location.position).toEqual([[40, 3], [40, 8]])
            expect(warnings[38].location.position).toEqual([[41, 5], [41, 10]])
            expect(warnings[39].location.position).toEqual([[42, 4], [42, 9]])
            expect(warnings[40].location.position).toEqual([[43, 7], [43, 12]])
            expect(warnings[41].location.position).toEqual([[44, 1], [44, 9]])
            expect(warnings[42].location.position).toEqual([[45, 2], [45, 10]])
            expect(warnings[43].location.position).toEqual([[46, 3], [46, 11]])
            expect(warnings[44].location.position).toEqual([[47, 2], [47, 10]])
            expect(warnings[45].location.position).toEqual([[48, 3], [48, 11]])
            expect(warnings[46].location.position).toEqual([[49, 4], [49, 12]])
            expect(warnings[47].location.position).toEqual([[50, 3], [50, 11]])
            expect(warnings[48].location.position).toEqual([[51, 4], [51, 12]])
            expect(warnings[49].location.position).toEqual([[52, 5], [52, 13]])
            expect(warnings[50].location.position).toEqual([[53, 1], [53, 10]])
            expect(warnings[51].location.position).toEqual([[54, 2], [54, 11]])
            expect(warnings[52].location.position).toEqual([[55, 3], [55, 12]])
            expect(warnings[53].location.position).toEqual([[56, 2], [56, 11]])
            expect(warnings[54].location.position).toEqual([[57, 3], [57, 12]])
            expect(warnings[55].location.position).toEqual([[58, 4], [58, 13]])
            expect(warnings[56].location.position).toEqual([[59, 3], [59, 12]])
            expect(warnings[57].location.position).toEqual([[60, 4], [60, 13]])
            expect(warnings[58].location.position).toEqual([[61, 5], [61, 14]])
            expect(warnings[59].location.position).toEqual([[62, 1], [62, 6]])
            expect(warnings[60].location.position).toEqual([[63, 2], [63, 7]])
            expect(warnings[61].location.position).toEqual([[64, 3], [64, 8]])
            expect(warnings[62].location.position).toEqual([[65, 3], [65, 8]])
            expect(warnings[63].location.position).toEqual([[66, 5], [66, 10]])
            expect(warnings[64].location.position).toEqual([[67, 4], [67, 9]])
            expect(warnings[65].location.position).toEqual([[68, 7], [68, 12]])
            expect(warnings[66].location.position).toEqual([[69, 1], [69, 9]])
            expect(warnings[67].location.position).toEqual([[70, 2], [70, 10]])
            expect(warnings[68].location.position).toEqual([[71, 3], [71, 11]])
            expect(warnings[69].location.position).toEqual([[72, 2], [72, 10]])
            expect(warnings[70].location.position).toEqual([[73, 3], [73, 11]])
            expect(warnings[71].location.position).toEqual([[74, 4], [74, 12]])
            expect(warnings[72].location.position).toEqual([[75, 3], [75, 11]])
            expect(warnings[73].location.position).toEqual([[76, 4], [76, 12]])
            expect(warnings[74].location.position).toEqual([[77, 5], [77, 13]])
            expect(warnings[75].location.position).toEqual([[78, 1], [78, 12]])
            expect(warnings[76].location.position).toEqual([[79, 2], [79, 13]])
            expect(warnings[77].location.position).toEqual([[80, 3], [80, 14]])
            expect(warnings[78].location.position).toEqual([[81, 2], [81, 13]])
            expect(warnings[79].location.position).toEqual([[82, 3], [82, 14]])
            expect(warnings[80].location.position).toEqual([[83, 4], [83, 15]])
            expect(warnings[81].location.position).toEqual([[84, 3], [84, 14]])
            expect(warnings[82].location.position).toEqual([[85, 4], [85, 15]])
            expect(warnings[83].location.position).toEqual([[86, 5], [86, 16]])

            expect(textAA[0].location.position).toEqual([[19, 1], [19, 8]])

            expect(textEmpty[0].location.position).toEqual([[1, 5], [1, 9]])
          })
      })
    })

    it('should return 3 messages in `fixture.sh`', () => {
      waitsForPromise(() => {
        const grammar = atom.grammars.getGrammars().find(({ name }) => name === 'Shell Script')
        return atom.workspace.open(path.join(__dirname, 'files', 'fixture.sh'))
          .then(editor => (editor.setGrammar(grammar) || Provider.lint(editor)))
          .then(messages => {
            expect(messages.length).toEqual(3)

            expect(messages[0].severity).toEqual('error')
            expect(messages[0].excerpt).toEqual('FIXME: This FIXME is visible')
            expect(messages[0].location.position).toEqual([[1, 1], [1, 29]])

            expect(messages[1].severity).toEqual('error')
            expect(messages[1].excerpt).toEqual('FIXME: This FIXME is visible')
            expect(messages[1].location.position).toEqual([[4, 3], [4, 31]])

            expect(messages[2].severity).toEqual('warning')
            expect(messages[2].excerpt).toEqual('TODO: This TODO is visible')
            expect(messages[2].location.position).toEqual([[5, 3], [5, 29]])
          })
      })
    })
  })
})
