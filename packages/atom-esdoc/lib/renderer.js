'use babel';

import { firstBlockComment, endBlockComment, blockComment, lineComment } from './parser';


const START = ' * '
const DELIMITER = `\t`

const RENDERERS = {
  class: node => {
    return [];
  },
  constructor: node => {
    let lines = [];

    for (let param of node.params) {
      lines.push(`${START}@param {${param.type ? param.type : 'type'}}${DELIMITER}${param.name} - this is the parameter ${param.name}`);
    }

    return lines;
  },
  get: node => {
    return `/** @type {${(node.returns.type) ? node.returns.type : '<type>'}} */`;
  },
  set: node => {
    if (node.params.length > 0) {
      return `/** @type {${node.params[0].type}} */`;
    }
    return `/** @type {<type>} */`;
  },
  var: node => {
    return `/** @type {${node.type}} */`;
  },
  let: node => {
    return `/** @type {${node.type}} */`;
  },
  const: node => {
    return `/** @type {${node.type}} */`;
  },
  method: node => {
    let lines = [];

    for (let param of node.params) {
      lines.push(`${START}@param {${param.type ? param.type : '<type>'}}${DELIMITER}${param.name} - this is the parameter ${param.name}`);

      if (param.properties) {
        for (let property of param.properties) {
          lines.push(...renderFunctionProperty(`${param.name}.${property.name}`, property));
        }
      }
    }

    if (`returns` in node) {
      lines.push(`${START}`);
      lines.push(`${START}@return {${(node.returns.type) ? node.returns.type : '<type>'}} `);
    }

    if (`throws` in node) {
      lines.push(`${START}`);
      for (let exec of node.throws) {
        let line = `${START}@throws {${exec.throws}}`;
        if (exec.value) line += ` - ${exec.value}`;
        lines.push(line);
      }
    }

    return lines;
  },
  function: node => {
    return RENDERERS['method'](node);
  }
}

function renderFunctionProperty(path, property) {
  let list = [];
  list.push(`${START}@param {${property.type ? property.type : '<type>'}}${DELIMITER}${path} - this is the parameter ${property.name}`);

  if (property.properties) {
    for (let prop of property.properties) {
      list.push(...renderFunctionProperty(`${path}.${prop.name}`, prop));
    }
  }

  return list;
}

function renderItem(structure) {
  let lines = [];
  let content = null;
  let lineNums = lines.length;

  let name = "";
  if ("name" in structure) {
    name = structure.name;
  }

  if (!("kind" in structure)) {
    return { content, lineNums };
  }

  if (structure.kind === 'get' || structure.kind === 'set' || structure.kind === 'var' || structure.kind === 'const' || structure.kind === 'let') {
    content = RENDERERS[structure.kind](structure);
    lineNums = 1;
    return { content, lineNums };
  }

  lines.push(...['/**', `${START}${name}`, `${START}`]);

  if (RENDERERS[structure.kind])
    lines.push(...RENDERERS[structure.kind](structure));


  lines.push(...[' */']);

  content = lines.join('\n');
  lineNums = lines.length;

  return { content, lineNums };
}

export function render(structure) {
  if ('declarations' in structure) {

    let overwrite = { ...structure };
    delete overwrite.declarations;

    let content = "";
    let lineNums = 0;

    for (let declaration of structure.declarations) {
      let strucOut = renderItem({ ...declaration, ...overwrite });
      content += strucOut.content + "\n";
      lineNums += strucOut.lineNums
    }
    content = content.replace(/\n$/, "");
    return { content, lineNums };
  }
  return renderItem(structure);
}

export function newLine(previousLine) {
  if (endBlockComment.test(previousLine)) {
    return "";
  } else
  if (firstBlockComment.test(previousLine) || blockComment.test(previousLine)) {
    return `\n* `;
  } else if (lineComment.test(previousLine)) {
    return `\n// `;
  }
}
