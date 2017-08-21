module.exports =

  apply: ->

    root = document.documentElement


    # Font Size
    setFontSize = (currentFontSize) ->
      if Number.isInteger(currentFontSize)
        root.style.fontSize = currentFontSize + 'px'
      else if currentFontSize is 'Auto'
        root.style.fontSize = ''

    atom.config.onDidChange 'slim-dark-ui.fontSize', ->
      setFontSize(atom.config.get('slim-dark-ui.fontSize'))

    setFontSize(atom.config.get('slim-dark-ui.fontSize'))


    # Layout Mode
    setLayoutMode = (layoutMode) ->
      root.setAttribute('theme-slim-dark-ui-layoutmode', layoutMode.toLowerCase())

    atom.config.onDidChange 'slim-dark-ui.layoutMode', ->
      setLayoutMode(atom.config.get('slim-dark-ui.layoutMode'))

    setLayoutMode(atom.config.get('slim-dark-ui.layoutMode'))
