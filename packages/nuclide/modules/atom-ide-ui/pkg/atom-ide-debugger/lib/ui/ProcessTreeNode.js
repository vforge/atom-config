"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

function _FilterThreadConditions() {
  const data = require("../vsp/FilterThreadConditions");

  _FilterThreadConditions = function () {
    return data;
  };

  return data;
}

function _Button() {
  const data = require("../../../../../nuclide-commons-ui/Button");

  _Button = function () {
    return data;
  };

  return data;
}

function _Tree() {
  const data = require("../../../../../nuclide-commons-ui/Tree");

  _Tree = function () {
    return data;
  };

  return data;
}

function _event() {
  const data = require("../../../../../nuclide-commons/event");

  _event = function () {
    return data;
  };

  return data;
}

function _observable() {
  const data = require("../../../../../nuclide-commons/observable");

  _observable = function () {
    return data;
  };

  return data;
}

function _UniversalDisposable() {
  const data = _interopRequireDefault(require("../../../../../nuclide-commons/UniversalDisposable"));

  _UniversalDisposable = function () {
    return data;
  };

  return data;
}

var React = _interopRequireWildcard(require("react"));

var _RxMin = require("rxjs/bundles/Rx.min.js");

function _DebuggerFilterThreadsUI() {
  const data = _interopRequireDefault(require("./DebuggerFilterThreadsUI"));

  _DebuggerFilterThreadsUI = function () {
    return data;
  };

  return data;
}

function _ThreadTreeNode() {
  const data = _interopRequireDefault(require("./ThreadTreeNode"));

  _ThreadTreeNode = function () {
    return data;
  };

  return data;
}

function _constants() {
  const data = require("../constants");

  _constants = function () {
    return data;
  };

  return data;
}

function _showModal() {
  const data = _interopRequireDefault(require("../../../../../nuclide-commons-ui/showModal"));

  _showModal = function () {
    return data;
  };

  return data;
}

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = Object.defineProperty && Object.getOwnPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : {}; if (desc.get || desc.set) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } } newObj.default = obj; return newObj; } }

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Copyright (c) 2017-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the same directory.
 *
 * 
 * @format
 */
class ProcessTreeNode extends React.Component {
  constructor(props) {
    super(props);

    this._handleFocusChanged = () => {
      this.setState(prevState => this._getState(!(this._computeIsFocused() || !prevState.isCollapsed)));
    };

    this._handleCallStackChanged = () => {
      const {
        process
      } = this.props;
      this.setState({
        threads: process.getAllThreads()
      });
    };

    this.handleSelect = () => {
      this.setState(prevState => this._getState(!prevState.isCollapsed));
    };

    this._addFilterThreadsConditions = conditions => {
      this.setState({
        filterThreadConditions: conditions
      });
    };

    this.state = this._getState();
    this._disposables = new (_UniversalDisposable().default)();
  }

  componentDidMount() {
    const {
      service
    } = this.props;
    const model = service.getModel();
    const {
      viewModel
    } = service;

    this._disposables.add(_RxMin.Observable.merge((0, _event().observableFromSubscribeFunction)(viewModel.onDidChangeDebuggerFocus.bind(viewModel))).let((0, _observable().fastDebounce)(15)).subscribe(this._handleFocusChanged), (0, _event().observableFromSubscribeFunction)(model.onDidChangeCallStack.bind(model)).let((0, _observable().fastDebounce)(15)).subscribe(this._handleCallStackChanged), (0, _event().observableFromSubscribeFunction)(service.onDidChangeProcessMode.bind(service)).subscribe(() => this.setState(prevState => this._getState(prevState.isCollapsed))));
  }

  componentWillUnmount() {
    this._disposables.dispose();
  }

  _computeIsFocused() {
    const {
      service,
      process
    } = this.props;
    const focusedProcess = service.viewModel.focusedProcess;
    return process === focusedProcess;
  }

  _getState(shouldBeCollapsed) {
    const {
      process
    } = this.props;

    const isFocused = this._computeIsFocused();

    const pendingStart = process.debuggerMode === _constants().DebuggerMode.STARTING;

    const isCollapsed = shouldBeCollapsed != null ? shouldBeCollapsed : !isFocused;
    return {
      isFocused,
      threads: process.getAllThreads(),
      isCollapsed,
      pendingStart,
      filterThreadConditions: this.state != null ? this.state.filterThreadConditions : null
    };
  }

  render() {
    const {
      service,
      title,
      process
    } = this.props;
    const {
      threads,
      isFocused,
      isCollapsed,
      filterThreadConditions
    } = this.state;
    const tooltipTitle = service.viewModel.focusedProcess == null || service.viewModel.focusedProcess.configuration.adapterExecutable == null ? 'Unknown Command' : service.viewModel.focusedProcess.configuration.adapterExecutable.command + service.viewModel.focusedProcess.configuration.adapterExecutable.args.join(' ');

    const handleTitleClick = event => {
      if (!this._computeIsFocused()) {
        service.viewModel.setFocusedProcess(process, true);
        event.stopPropagation();
      }
    };

    const handleFilterThreadClick = event => {
      const disposable = (0, _showModal().default)(({
        dismiss
      }) => React.createElement(_DebuggerFilterThreadsUI().default, {
        updateFilters: this._addFilterThreadsConditions,
        dialogCloser: dismiss,
        currentFilterConditions: this.state.filterThreadConditions
      }));

      this._disposables.add(disposable);

      event.stopPropagation();
    };

    const formattedTitle = React.createElement("span", null, React.createElement("span", {
      onClick: handleTitleClick,
      className: isFocused ? 'debugger-tree-process-thread-selected' : '',
      title: tooltipTitle
    }, title, this.state.pendingStart ? ' (starting...)' : ''), React.createElement("span", null, React.createElement(_Button().Button, {
      className: 'debugger-tree-right-align',
      onClick: handleFilterThreadClick
    }, "Filter Threads")));
    return threads.length === 0 ? React.createElement(_Tree().TreeItem, null, formattedTitle) : React.createElement(_Tree().NestedTreeItem, {
      title: formattedTitle,
      collapsed: isCollapsed,
      onSelect: this.handleSelect
    }, threads.map((thread, threadIndex) => {
      if (filterThreadConditions == null || filterThreadConditions.filterThread(thread)) {
        return React.createElement(_ThreadTreeNode().default, {
          key: threadIndex,
          thread: thread,
          service: service
        });
      }
    }));
  }

}

exports.default = ProcessTreeNode;