'use babel';

import fs from 'fs';
import path from 'path';
import Vue from 'vue/dist/vue';
import loader from 'require-extension-vue';
import { allowUnsafeNewFunction } from 'loophole';

/**
 * Require Atom package
 * @param {String} packageName package name
 * @return {Any} export of package
 */
let requireAtomPackage = ( packageName ) => require(path.join(atom.packages.resourcePath, 'src', `${ packageName }.js`));

let PackageTranspilationRegistry = requireAtomPackage('package-transpilation-registry');
let packageTranspilationRegistry = new PackageTranspilationRegistry;

const COMPILERS = {
    'ts' : packageTranspilationRegistry.wrapTranspiler(requireAtomPackage('typescript')),
    'babel' : packageTranspilationRegistry.wrapTranspiler(requireAtomPackage('babel')),
    'coffee' : packageTranspilationRegistry.wrapTranspiler(requireAtomPackage('coffee-script')),
};

/**
 * Register 'ts', use <script lang="ts"> in .vue file
 */
loader.script.register('ts', ( content, filePath ) => {
    let compiler = COMPILERS.ts;
    return compiler.compile(content, filePath);
});

/**
 * Register 'babel', use <script lang="babel"> in .vue file
 */
loader.script.register('babel', ( content, filePath ) => {
    let compiler = COMPILERS.babel;
    return compiler.compile(`'use babel';${ content }`, filePath);
});

/**
 * Register 'coffee', use <script lang="coffee"> in .vue file
 */
loader.script.register('coffee', ( content, filePath ) => {
    let compiler = COMPILERS.coffee;
    return compiler.compile(content, filePath);
});

/**
 * Register 'less', use <style lang="less"> in .vue file
 */
loader.style.register('less', ( content, filePath ) => {
    return atom.themes.lessCache.cssForFile(filePath, content);
});

/**
 * Append style in current place
 */
let atomStyles = document.querySelector('atom-styles');
loader.style.exports(function ( style, { index, styles, filePath } ) {
    style.setAttribute('source-path', filePath);
    style.setAttribute('vue-style', index - 2);
    style.setAttribute('priority', 0);
    atomStyles.appendChild(style);
});

/**
 * Resolve Content Security Policy problem
 */
let $mount = Vue.prototype.$mount;
Vue.prototype.$mount = function () {
    allowUnsafeNewFunction(() => {
        $mount.apply(this, arguments);
    });
};

/**
 * @export {Vue}
 */
exports.Vue = Vue;
