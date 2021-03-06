"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.getPortForJavaDebugger = getPortForJavaDebugger;
exports.getJavaVSAdapterExecutableInfo = getJavaVSAdapterExecutableInfo;
exports.prepareForTerminalLaunch = prepareForTerminalLaunch;
exports.javaDebugWaitForJdwpProcessStart = javaDebugWaitForJdwpProcessStart;
exports.javaDebugWaitForJdwpProcessExit = javaDebugWaitForJdwpProcessExit;
exports.getSdkVersionSourcePath = getSdkVersionSourcePath;

function _fsPromise() {
  const data = _interopRequireDefault(require("../nuclide-commons/fsPromise"));

  _fsPromise = function () {
    return data;
  };

  return data;
}

function _nuclideUri() {
  const data = _interopRequireDefault(require("../nuclide-commons/nuclideUri"));

  _nuclideUri = function () {
    return data;
  };

  return data;
}

function _UniversalDisposable() {
  const data = _interopRequireDefault(require("../nuclide-commons/UniversalDisposable"));

  _UniversalDisposable = function () {
    return data;
  };

  return data;
}

var _os = _interopRequireDefault(require("os"));

function _process() {
  const data = require("../nuclide-commons/process");

  _process = function () {
    return data;
  };

  return data;
}

var _RxMin = require("rxjs/bundles/Rx.min.js");

function _serverPort() {
  const data = require("../nuclide-commons/serverPort");

  _serverPort = function () {
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
 *  strict-local
 * @format
 */
const JAVA = 'java';

function _getAndroidHomeDir() {
  return process.env.ANDROID_HOME || '/opt/android_sdk';
}

async function getPortForJavaDebugger() {
  return (0, _serverPort().getAvailableServerPort)();
}

async function getJavaVSAdapterExecutableInfo(debug) {
  return {
    command: JAVA,
    args: await _getJavaArgs(debug)
  };
}

async function prepareForTerminalLaunch(config) {
  const {
    classPath,
    entryPointClass
  } = config;

  const launchPath = _nuclideUri().default.expandHomeDir(classPath);

  const attachPort = await (0, _serverPort().getAvailableServerPort)(); // Note: the attach host is passed to the Java debugger engine, which
  // runs on the RPC side of Nuclide, so it is fine to always pass localhost
  // as the host name, even if the Nuclide client is on a different machine.

  const attachHost = '127.0.0.1';
  return Promise.resolve({
    attachPort,
    attachHost,
    launchCommand: 'java',
    launchCwd: launchPath,
    targetExecutable: launchPath,
    launchArgs: ['-Xdebug', `-Xrunjdwp:transport=dt_socket,address=${attachHost}:${attachPort},server=y,suspend=y`, '-classpath', launchPath, entryPointClass, ...(config.runArgs || [])]
  });
}

async function javaDebugWaitForJdwpProcessStart(jvmSuspendArgs) {
  return new Promise(resolve => {
    const disposable = new (_UniversalDisposable().default)();
    disposable.add(_RxMin.Observable.interval(1000).mergeMap(async () => {
      const line = await _findJdwpProcess(jvmSuspendArgs);

      if (line != null) {
        disposable.dispose();
        resolve();
      }
    }).timeout(30000).subscribe());
  });
}

async function javaDebugWaitForJdwpProcessExit(jvmSuspendArgs) {
  return new Promise(resolve => {
    const disposable = new (_UniversalDisposable().default)();
    let pidLine = null;
    disposable.add(_RxMin.Observable.interval(1000).mergeMap(async () => {
      const line = await _findJdwpProcess(jvmSuspendArgs);

      if (line != null) {
        if (pidLine != null && pidLine !== line) {
          // The matching target process line has changed, so the process
          // we were watching is now gone.
          disposable.dispose();
          resolve();
        }

        pidLine = line;
      } else {
        disposable.dispose();
        resolve();
      }
    }).subscribe());
  });
}

async function _getJavaArgs(debug) {
  const baseJavaArgs = ['-classpath', await _getClassPath(), 'com.facebook.nuclide.debugger.JavaDbg'];
  const debugArgs = debug ? ['-Xdebug', '-Xrunjdwp:transport=dt_socket,address=127.0.0.1:' + (await (0, _serverPort().getAvailableServerPort)()).toString() + ',server=y,suspend=n'] : [];
  return debugArgs.concat(baseJavaArgs);
}

async function _getClassPath() {
  const serverJarPath = _nuclideUri().default.join(__dirname, 'Build', 'java_debugger_server.jar');

  if (!(await _fsPromise().default.exists(serverJarPath))) {
    throw new Error(`Could not locate the java debugger server jar: ${serverJarPath}. ` + 'Please check your Nuclide installation.');
  } // Determining JDK lib path varies by platform.


  let toolsJarPath;

  switch (_os.default.platform()) {
    case 'win32':
      toolsJarPath = (process.env.JAVA_HOME || '') + '\\lib\\tools.jar';
      break;

    case 'linux':
      {
        // Find java
        const java = (await (0, _process().runCommand)('which', ['java']).toPromise()).trim();
        const javaHome = await _fsPromise().default.realpath(java); // $FlowFixMe (>= v0.75.0)

        const matches = /(.*)\/java/.exec(javaHome);
        toolsJarPath = matches.length > 1 ? matches[1] + '/../lib/tools.jar' : '';
        break;
      }

    case 'darwin':
    default:
      {
        const javaHome = (await (0, _process().runCommand)('/usr/libexec/java_home').toPromise()).trim();
        toolsJarPath = javaHome + '/lib/tools.jar';
        break;
      }
  }

  if (!(await _fsPromise().default.exists(toolsJarPath))) {
    throw new Error(`Could not locate required JDK tools jar: ${toolsJarPath}. Is the JDK installed?`);
  }

  return _nuclideUri().default.joinPathList([serverJarPath, toolsJarPath]);
}

async function _findJdwpProcess(jvmSuspendArgs) {
  const commands = await (0, _process().runCommand)('ps', ['-eww', '-o', 'pid,args'], {}).toPromise();
  const procs = commands.toString().split('\n').filter(line => line.includes(jvmSuspendArgs));
  const line = procs.length === 1 ? procs[0] : null;
  return line;
}

async function getSdkVersionSourcePath(sdkVersion) {
  if (Number.isNaN(parseInt(sdkVersion, 10))) {
    return null;
  }

  const sourcesDirectory = _nuclideUri().default.join(_getAndroidHomeDir(), 'sources', 'android-' + sdkVersion);

  if (await _fsPromise().default.exists(sourcesDirectory)) {
    return sourcesDirectory;
  }

  const sdkManagerPath = _nuclideUri().default.join(_getAndroidHomeDir(), 'tools/bin/sdkmanager');

  if (await _fsPromise().default.exists(sdkManagerPath)) {
    await (0, _process().runCommand)(sdkManagerPath, ['sources;android-' + sdkVersion]); // try again

    if (await _fsPromise().default.exists(sourcesDirectory)) {
      return sourcesDirectory;
    }
  }

  return null;
}