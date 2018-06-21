'use babel';

import { parse } from './parser';
import { render, newLine } from './renderer';


export function comment(code, lineNum = 1) {

  let desc = parse(code, lineNum);

  if (!desc) {
    return '';
  }

  return render(desc);
}

export function addLine(previousLine) {
  return newLine(previousLine);
}
