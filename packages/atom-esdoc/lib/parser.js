"use babel";

import * as parser from 'babylon';
import traverse from 'babel-traverse';

const PARSE_OPTS = {
  locations: true,
  sourceType: 'module',
  plugins: ['*'],
};

export const firstBlockComment = /^\s*\/\*[^\/]/; // Matches zero to many white spaces followed by /*, not followed by /
export const blockComment = /^\s+\*/; // Matches zero to many white spaces followed by *, not followed by /
export const lineComment = /^((\s*\/\/))/;
export const endBlockComment = /\*\/[ \t]*$/;


const SWITCHES = {
  ClassDeclaration: node => {
    return {
      kind: "class",
      name: node.id.name
    }
  },
  Identifier: node => {
    return {
      name: node.name
    }
  },
  StringLiteral: node => {
    return {
      value: node.value,
      type: "String"
    }
  },
  NumericLiteral: node => {
    return {
      value: node.value,
      type: "Number"
    };
  },
  BooleanLiteral: node => {
    return {
      value: node.value,
      type: "Boolean"
    };
  },
  ArrayExpression: node => {
    let elements = [];
    for (let ele of node.elements) {
      elements.push(SWITCHES[ele.type](ele));
    }
    return { type: "ArrayPattern", elements: elements };
  },
  ArrayPattern: node => {
    return { type: "Array" };
  },
  ObjectProperty: node => {
    return Object.assign(SWITCHES[node.key.type](node.key), SWITCHES[node.value.type](node.value));
  },
  ObjectExpression: node => {
    let properties = [];
    for (let property of node.properties) {
      properties.push(SWITCHES[property.type](property));
    }
    return { type: "Object", properties: properties };
  },
  ObjectPattern: node => {
    return { type: "Object" };
  },
  NewExpression: node => {
    return { type: node.callee.name }
  },
  AssignmentPattern: node => {
    return Object.assign(SWITCHES[node.left.type](node.left), SWITCHES[node.right.type](node.right));
  }
};


const BODY_SWITCHES = {
  ReturnStatement: node => {
    return {
      returns: (node.argument.type in SWITCHES) ? SWITCHES[node.argument.type](node.argument) : {}
    };
  },
  ThrowStatement: node => {
    return {
      throws: (node.argument.callee) ? node.argument.callee.name : "Error"
    };
  },
  BlockStatement: node => {
    return parseBody(node);
  },
  IfStatement: node => {
    return parseBody(node.consequent);
  },
  ForStatement: node => {
    return parseBody(node.body);
  }
}

// https://github.com/babel/babylon/blob/master/ast/spec.md
const ENTRY_POINTS = {

  VariableDeclaration: node => {
    let obj = {
      kind: node.kind,
      declarations: []
    }

    for (let declaration of node.declarations) {
      let dec = {};

      if (declaration.id.type in SWITCHES)
        dec = { ...dec, ...(SWITCHES[declaration.id.type](declaration.id)) }

      if (declaration.init.type in SWITCHES)
        dec = { ...dec, ...(SWITCHES[declaration.init.type](declaration.init)) }

      obj.declarations.push(dec);
    }

    return obj;
  },

  ClassDeclaration: node => {
    return SWITCHES[node.type](node);
  },

  ExportNamedDeclaration: node => {
    console.log(node);

    let exported = {
      export: true
    };

    if (node.declaration) {
      if (node.declaration.declarations) {
        exported = { ...exported, ...(ENTRY_POINTS[node.declaration.type](node.declaration)) };
      } else {
        exported = { ...exported, ...(SWITCHES[node.declaration.type](node.declaration)) };
      }
    }

    return exported;
  },

  ClassMethod: node => {
    let out = {
      kind: (node.kind) ? node.kind : 'method',
      async: node.async,
      params: [],
      throws: []
    };

    Object.assign(out, SWITCHES[node.key.type](node.key));

    for (let param of node.params) {
      out.params.push(SWITCHES[param.type](param));
    }

    let bodies = [];
    for (let body of node.body.body) {
      if (body.type in BODY_SWITCHES)
        bodies.push(BODY_SWITCHES[body.type](body));
    }

    for (let body of bodies) {
      if ("returns" in body) {
        Object.assign(out, body);
      } else if ("throws" in body) {
        out.throws.push(body);
      }
    }

    return out;

  },

  FunctionDeclaration: node => {
    let out = {
      kind: "function",
      async: node.async,
      params: [],
      throws: []
    }

    Object.assign(out, SWITCHES[node.id.type](node.id));

    for (let param of node.params) {
      out.params.push(SWITCHES[param.type](param));
    }

    let bodies = [];
    for (let body of node.body.body) {
      if (body.type in BODY_SWITCHES)
        bodies.push(BODY_SWITCHES[body.type](body));
    }

    for (let body of bodies) {
      if ("returns" in body) {
        Object.assign(out, body);
      } else if ("throws" in body) {
        out.throws.push(body);
      }
    }

    return out;
  }

}

function parseBody(node) {
  let obj = {};
  for (let body of node.body) {
    if (body.type in BODY_SWITCHES)
      obj = BODY_SWITCHES[body.type](body);
  }
  return obj;
}


function parseNode(node) {
  return obj = (node.type in ENTRY_POINTS) ? ENTRY_POINTS[node.type](node) : "";
}


function onLine(node, lineNum) {
  const startLine = node.loc.start.line;
  return startLine === lineNum || startLine - 1 === lineNum;
}

function getNode(ast, lineNum) {
  let node = null;
  traverse(ast, {
    enter(path) {
      if (onLine(path.node, lineNum)) {
        if (!node) {
          node = parseNode(path.node);
        }
      }
    }
  });

  return (node) ? node : {};;
}

function getAST(code) {
  try {
    const ast = parser.parse(code, PARSE_OPTS);
    return ast;
  } catch (e) {
    throw new Error(`esdoc expects valid JavaScript. Error parsing: ${e.message}`);
  }
}


export function parse(code, lineNum = 1) {
  let ast = getAST(code);
  let node = getNode(ast, lineNum + 1);

  if (!node) {
    return null;
  }

  console.log("-------------------------");
  console.log(node);
  return node;
}
