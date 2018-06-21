'use babel';

import tags from './esdoc-tags';

const funcList = [];

for (let tag of tags) {
  tag.replacementPrefix = '@';
  let match = tag.snippet.match(/^(\@\w+)/);
  funcList.push(match[0]);
}


module.exports = {
  // This will work on JavaScript and CoffeeScript files, but not in js comments.
  selector: '.source.js .comment, .source.coffee .comment',
  //disableForSelector: '.source.js, .source.coffee',

  // This will take priority over the default provider, which has an inclusionPriority of 0.
  // `excludeLowerPriority` will suppress any providers with a lower priority
  // i.e. The default provider will be suppressed
  inclusionPriority: 1,
  excludeLowerPriority: true,

  // This will be suggested before the default provider, which has a suggestionPriority of 1.
  suggestionPriority: 2,

  getSuggestions: function(options) {
    let editor = options.editor,
      bufferPosition = options.bufferPosition,
      scopeDescriptor = options.scopeDescriptor,
      prefix = options.prefix,
      activatedManually = options.activatedManually;

    let line = editor.getTextInRange([
      [bufferPosition.row, 0], bufferPosition
    ])


    return new Promise(function(resolve) {
      if (/\*[ ]{0,1}(\@)/.test(line)) {
        let match = line.match(/(\@\w+)/);
        let filter = (match) ? match[0] : "";

        if (match) {
          var tagsFiltered = tags.filter(function(obj, idx) {
            obj.replacementPrefix = filter;
            return (obj.snippet.startsWith(filter));
          });
          return resolve(tagsFiltered);
        }

        return resolve(tags);
      }
    });

  },

  onDidInsertSuggestion: function(options) {
    /*
    let editor = options.editor,
      triggerPosition = options.triggerPosition,
      suggestion = options.suggestion;
    */
  }

}
