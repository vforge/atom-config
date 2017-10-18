IdeCView = require './ide-c-view'
{CompositeDisposable} = require 'atom'

module.exports = IdeC =
  ideCView: null
  modalPanel: null
  subscriptions: null

  activate: (state) ->
    @ideCView = new IdeCView(state.ideCViewState)
    @modalPanel = atom.workspace.addModalPanel(item: @ideCView.getElement(), visible: false)

    # Events subscribed to in atom's system can be easily cleaned up with a CompositeDisposable
    @subscriptions = new CompositeDisposable

    # Register command that toggles this view
    @subscriptions.add atom.commands.add 'atom-workspace', 'ide-c:toggle': => @toggle()

  deactivate: ->
    @modalPanel.destroy()
    @subscriptions.dispose()
    @ideCView.destroy()

  serialize: ->
    ideCViewState: @ideCView.serialize()

  toggle: ->
    console.log 'IdeC was toggled!'

    if @modalPanel.isVisible()
      @modalPanel.hide()
    else
      @modalPanel.show()
