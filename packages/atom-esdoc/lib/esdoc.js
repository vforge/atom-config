'use babel';

import { CompositeDisposable } from 'atom';

import { comment, addLine } from './esdocer';

import { firstBlockComment, blockComment, lineComment } from './parser'

import provider from './esdoc-provider';



export default {

  subscriptions: null,

  activate(state) {
    // Events subscribed to in atom's system can be easily cleaned up with a CompositeDisposable
    this.subscriptions = new CompositeDisposable();

    // Register command that toggles this view
    this.subscriptions.add(atom.commands.add('atom-text-editor', {
      'esdoc:generate': () => this.generate(),
      'esdoc:parse-enter': (evt) => this.parseEnter(evt),
      'esdoc:parse-tab': (evt) => this.parseTab(evt)
    }));
  },

  deactivate() {
    this.subscriptions.dispose();
  },

  generate() {
    const editor = atom.workspace.getActiveTextEditor();
    let code = editor.getText();
    let {
      row
    } = editor.getCursorBufferPosition();
    let lineNum = row + 1;
    let indent = editor.indentLevelForLine(editor.lineTextForBufferRow(lineNum));

    let {
      content,
      lineNums
    } = comment(code, lineNum);

    if (content) {
      editor.insertText(`\n${content}`);
      for (let i = 0; i < lineNums; i++) {
        editor.setIndentationForBufferRow(lineNum + i, indent, {
          preserveLeadingWhitespace: true
        });
      }

      return true;
    }

    return false;
  },

  parseEnter(evt) {
    const editor = atom.workspace.getActiveTextEditor();

    let currentPosition = editor.getCursorBufferPosition(),
      previousLineText = editor.lineTextForBufferRow(currentPosition.row - 1),
      currentLineText = editor.lineTextForBufferRow(currentPosition.row),
      moveColumns = 0;

    if (firstBlockComment.test(currentLineText)) {

      editor.selectToBeginningOfLine();
      editor.delete();

      if (this.generate()) {
        return;
      }

      editor.insertText(currentLineText);

    } else if (blockComment.test(currentLineText) ||
      lineComment.test(currentLineText)) {

      let indent = editor.indentLevelForLine(currentLineText);
      let content = addLine(currentLineText);

      if (content) {
        editor.insertText(content);
        editor.setIndentationForBufferRow(currentPosition.row + 1, indent, {
          preserveLeadingWhitespace: true
        });

        return;
      }
    }

    evt.abortKeyBinding();
  },

  parseTab(evt) {
    const editor = atom.workspace.getActiveTextEditor();

    let currentPosition = editor.getCursorBufferPosition(),
      previousLineText = editor.lineTextForBufferRow(currentPosition.row - 1),
      currentLineText = editor.lineTextForBufferRow(currentPosition.row),
      moveColumns = 0;

    if (firstBlockComment.test(currentLineText)) {

      editor.selectToBeginningOfLine();
      editor.delete();

      this.generate();

      return;
    }

    evt.abortKeyBinding();
  },


  provideAutocomplete() {
    return provider;
  }

};
