'use babel'

function getRegexp() {
  return /rgb\(\d+, ?\d+, ?\d+\)|rgba\(\d+, ?\d+, ?\d+, ?\d?\.?\d+\)|rgba\(#[0-9a-fA-F]{3,6}, ?\d?\.?\d+\)|#[0-9a-fA-F]{3,6}/g
}

export const colorRegexp = getRegexp()
export function isColor(textEditor, bufferPosition) {
  const colorRegexp = getRegexp()
  const lineText = textEditor.getTextInRange([[bufferPosition.row, 0], [bufferPosition.row, Infinity]])

  let match
  do {
    match = colorRegexp.exec(lineText)
    if (match !== null) {
      const offsetStart = match.index
      const offsetEnd = offsetStart + match[0].length
      if (bufferPosition.column >= offsetStart && offsetEnd >= bufferPosition.column) {
        return true
      }
    }
  } while (match !== null)
  return false
}
