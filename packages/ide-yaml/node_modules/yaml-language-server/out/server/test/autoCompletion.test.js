"use strict";
/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Red Hat. All rights reserved.
 *  Licensed under the MIT License. See License.txt in the project root for license information.
 *--------------------------------------------------------------------------------------------*/
const vscode_languageserver_1 = require("vscode-languageserver");
const yamlLanguageService_1 = require("../src/languageService/yamlLanguageService");
const jsonSchemaService_1 = require("../src/languageService/services/jsonSchemaService");
const testHelper_1 = require("./testHelper");
const yamlParser_1 = require("../src/languageService/parser/yamlParser");
const arrUtils_1 = require("../src/languageService/utils/arrUtils");
var assert = require('assert');
let languageService = yamlLanguageService_1.getLanguageService(testHelper_1.schemaRequestService, testHelper_1.workspaceContext, [], null);
let schemaService = new jsonSchemaService_1.JSONSchemaService(testHelper_1.schemaRequestService, testHelper_1.workspaceContext);
let uri = 'http://json.schemastore.org/bowerrc';
let languageSettings = {
    schemas: []
};
let fileMatch = ["*.yml", "*.yaml"];
languageSettings.schemas.push({ uri, fileMatch: fileMatch });
languageService.configure(languageSettings);
suite("Auto Completion Tests", () => {
    describe('yamlCompletion with bowerrc', function () {
        describe('doComplete', function () {
            function setup(content) {
                return vscode_languageserver_1.TextDocument.create("file://~/Desktop/vscode-k8s/test.yaml", "yaml", 0, content);
            }
            function parseSetup(content, position) {
                let testTextDocument = setup(content);
                let yDoc = yamlParser_1.parse(testTextDocument.getText());
                return completionHelper(testTextDocument, testTextDocument.positionAt(position), false);
            }
            it('Autocomplete on root node without word', (done) => {
                let content = "";
                let completion = parseSetup(content, 0);
                completion.then(function (result) {
                    assert.notEqual(result.items.length, 0);
                }).then(done, done);
            });
            it('Autocomplete on root node with word', (done) => {
                let content = "analyt";
                let completion = parseSetup(content, 6);
                completion.then(function (result) {
                    assert.notEqual(result.items.length, 0);
                }).then(done, done);
            });
            it('Autocomplete on default value (without value content)', (done) => {
                let content = "directory: ";
                let completion = parseSetup(content, 12);
                completion.then(function (result) {
                    assert.notEqual(result.items.length, 0);
                }).then(done, done);
            });
            it('Autocomplete on default value (with value content)', (done) => {
                let content = "directory: bow";
                let completion = parseSetup(content, 15);
                completion.then(function (result) {
                    assert.notEqual(result.items.length, 0);
                }).then(done, done);
            });
            it('Autocomplete on boolean value (without value content)', (done) => {
                let content = "analytics: ";
                let completion = parseSetup(content, 11);
                completion.then(function (result) {
                    assert.equal(result.items.length, 2);
                }).then(done, done);
            });
            it('Autocomplete on boolean value (with value content)', (done) => {
                let content = "analytics: fal";
                let completion = parseSetup(content, 11);
                completion.then(function (result) {
                    assert.equal(result.items.length, 2);
                }).then(done, done);
            });
            it('Autocomplete on number value (without value content)', (done) => {
                let content = "timeout: ";
                let completion = parseSetup(content, 9);
                completion.then(function (result) {
                    assert.equal(result.items.length, 1);
                }).then(done, done);
            });
            it('Autocomplete on number value (with value content)', (done) => {
                let content = "timeout: 6";
                let completion = parseSetup(content, 10);
                completion.then(function (result) {
                    assert.equal(result.items.length, 1);
                }).then(done, done);
            });
            it('Autocomplete key in middle of file', (done) => {
                let content = "scripts:\n  post";
                let completion = parseSetup(content, 11);
                completion.then(function (result) {
                    assert.notEqual(result.items.length, 0);
                }).then(done, done);
            });
            it('Autocomplete key in middle of file 2', (done) => {
                let content = "scripts:\n  postinstall: /test\n  preinsta";
                let completion = parseSetup(content, 31);
                completion.then(function (result) {
                    assert.notEqual(result.items.length, 0);
                }).then(done, done);
            });
            it('Autocomplete does not happen right after :', (done) => {
                let content = "analytics:";
                let completion = parseSetup(content, 9);
                completion.then(function (result) {
                    assert.notEqual(result.items.length, 0);
                }).then(done, done);
            });
            it('Autocomplete does not happen right after : under an object', (done) => {
                let content = "scripts:\n  postinstall:";
                let completion = parseSetup(content, 21);
                completion.then(function (result) {
                    assert.notEqual(result.items.length, 0);
                }).then(done, done);
            });
            it('Autocomplete on multi yaml documents in a single file on root', (done) => {
                let content = `---\nanalytics: true\n...\n---\n...`;
                let completion = parseSetup(content, 28);
                completion.then(function (result) {
                    assert.notEqual(result.items.length, 0);
                }).then(done, done);
            });
            it('Autocomplete on multi yaml documents in a single file on scalar', (done) => {
                let content = `---\nanalytics: true\n...\n---\njson: \n...`;
                let completion = parseSetup(content, 34);
                completion.then(function (result) {
                    assert.notEqual(result.items.length, 0);
                }).then(done, done);
            });
        });
    });
});
function completionHelper(document, textDocumentPosition, isKubernetes) {
    //Get the string we are looking at via a substring
    let linePos = textDocumentPosition.line;
    let position = textDocumentPosition;
    let lineOffset = arrUtils_1.getLineOffsets(document.getText());
    let start = lineOffset[linePos]; //Start of where the autocompletion is happening
    let end = 0; //End of where the autocompletion is happening
    if (lineOffset[linePos + 1]) {
        end = lineOffset[linePos + 1];
    }
    else {
        end = document.getText().length;
    }
    let textLine = document.getText().substring(start, end);
    //Check if the string we are looking at is a node
    if (textLine.indexOf(":") === -1) {
        //We need to add the ":" to load the nodes
        let newText = "";
        //This is for the empty line case
        let trimmedText = textLine.trim();
        if (trimmedText.length === 0 || (trimmedText.length === 1 && trimmedText[0] === '-')) {
            //Add a temp node that is in the document but we don't use at all.
            if (lineOffset[linePos + 1]) {
                newText = document.getText().substring(0, start + (textLine.length - 1)) + "holder:\r\n" + document.getText().substr(end + 2);
            }
            else {
                newText = document.getText().substring(0, start + (textLine.length)) + "holder:\r\n" + document.getText().substr(end + 2);
            }
        }
        else {
            //Add a semicolon to the end of the current line so we can validate the node
            if (lineOffset[linePos + 1]) {
                newText = document.getText().substring(0, start + (textLine.length - 1)) + ":\r\n" + document.getText().substr(end + 2);
            }
            else {
                newText = document.getText().substring(0, start + (textLine.length)) + ":\r\n" + document.getText().substr(end + 2);
            }
        }
        let jsonDocument = yamlParser_1.parse(newText);
        return languageService.doComplete(document, position, jsonDocument, isKubernetes);
    }
    else {
        //All the nodes are loaded
        position.character = position.character - 1;
        let jsonDocument = yamlParser_1.parse(document.getText());
        return languageService.doComplete(document, position, jsonDocument, isKubernetes);
    }
}
//# sourceMappingURL=autoCompletion.test.js.map