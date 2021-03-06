"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.uriFromInfo = uriFromInfo;
exports.infoFromUri = infoFromUri;
exports.TERMINAL_DEFAULT_INFO = exports.TERMINAL_DEFAULT_ICON = exports.TERMINAL_DEFAULT_LOCATION = exports.URI_PREFIX = void 0;

var _crypto = _interopRequireDefault(require("crypto"));

var _url = _interopRequireDefault(require("url"));

function _uuid() {
  const data = _interopRequireDefault(require("uuid"));

  _uuid = function () {
    return data;
  };

  return data;
}

function _isEmpty() {
  const data = _interopRequireDefault(require("lodash/isEmpty"));

  _isEmpty = function () {
    return data;
  };

  return data;
}

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Copyright (c) 2017-present, Facebook, Inc.
 * All rights reserved.
 *
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree. An additional grant
 * of patent rights can be found in the PATENTS file in the same directory.
 *
 *  strict
 * @format
 */
// Generate a unique random token that is included in every URI we generate.
// We use this to check that URIs containing shell commands and similarly
// sensitive data were generated by this instance of Nuclide.  The goal is
// to prevent externally generated URIs from ever resulting in command
// execution.
const trustToken = _crypto.default.randomBytes(256).toString('hex'); // The external interface TerminalInfo leaves everything optional.
// When we open a terminal we will instantiate missing fields with defaults.


const URI_PREFIX = 'atom://nuclide-terminal-view';
exports.URI_PREFIX = URI_PREFIX;
const TERMINAL_DEFAULT_LOCATION = 'pane';
exports.TERMINAL_DEFAULT_LOCATION = TERMINAL_DEFAULT_LOCATION;
const TERMINAL_DEFAULT_ICON = 'terminal';
exports.TERMINAL_DEFAULT_ICON = TERMINAL_DEFAULT_ICON;
const TERMINAL_DEFAULT_INFO = {
  remainOnCleanExit: false,
  defaultLocation: TERMINAL_DEFAULT_LOCATION,
  icon: TERMINAL_DEFAULT_ICON,
  initialInput: '',
  title: '',
  cwd: '',
  preservedCommands: [],
  trustToken
};
exports.TERMINAL_DEFAULT_INFO = TERMINAL_DEFAULT_INFO;

function uriFromInfo(info) {
  const uri = _url.default.format({
    protocol: 'atom',
    host: 'nuclide-terminal-view',
    slashes: true,
    query: {
      cwd: info.cwd == null ? '' : info.cwd,
      command: info.command == null ? '' : JSON.stringify(info.command),
      title: info.title == null ? '' : info.title,
      key: info.key != null && info.key !== '' ? info.key : _uuid().default.v4(),
      remainOnCleanExit: info.remainOnCleanExit,
      defaultLocation: info.defaultLocation,
      icon: info.icon,
      environmentVariables: info.environmentVariables != null ? JSON.stringify([...info.environmentVariables]) : '',
      preservedCommands: JSON.stringify(info.preservedCommands || []),
      initialInput: info.initialInput != null ? info.initialInput : '',
      trustToken
    }
  });

  if (!uri.startsWith(URI_PREFIX)) {
    throw new Error("Invariant violation: \"uri.startsWith(URI_PREFIX)\"");
  }

  return uri;
}

function infoFromUri(paneUri, uriFromTrustedSource = false) {
  const {
    query
  } = _url.default.parse(paneUri, true);

  if ((0, _isEmpty().default)(query)) {
    // query can be null, '', or {}
    return Object.assign({}, TERMINAL_DEFAULT_INFO, {
      key: _uuid().default.v4()
    });
  } else {
    if (!(query != null)) {
      throw new Error("Invariant violation: \"query != null\"");
    }

    const cwd = query.cwd ? {
      cwd: query.cwd
    } : {};
    const command = query.command ? {
      command: JSON.parse(query.command)
    } : {};
    const title = query.title ? {
      title: query.title
    } : {};
    const remainOnCleanExit = query.remainOnCleanExit === 'true';
    const key = query.key;
    const defaultLocation = query.defaultLocation || TERMINAL_DEFAULT_LOCATION;
    const icon = query.icon || TERMINAL_DEFAULT_ICON;
    const environmentVariables = query.environmentVariables ? new Map(JSON.parse(query.environmentVariables)) : new Map();
    const preservedCommands = JSON.parse(query.preservedCommands || '[]');
    const initialInput = query.initialInput || ''; // Information that can affect the commands executed by the terminal,
    // and that therefore must come from a trusted source.
    //
    // If we detect that the URL did not come from this instance of Nuclide,
    // we just omit these fields so the user gets a default shell.

    const trustedFields = Object.assign({}, cwd, command, {
      environmentVariables,
      preservedCommands,
      initialInput
    }); // Everything here is cosmetic information that does not affect
    // processes running in the resulting terminal.

    const untrustedFields = Object.assign({}, title, {
      remainOnCleanExit,
      defaultLocation,
      icon,
      key
    });
    const isTrusted = uriFromTrustedSource || query.trustToken === trustToken;
    return Object.assign({}, untrustedFields, isTrusted ? trustedFields : TERMINAL_DEFAULT_INFO);
  }
}