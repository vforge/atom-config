# orql

A reimplmentation RQL for JavaScript arrays based on rql/js-array from Kris Zyp (https://github.com/persvr/rql).

([original file](https://github.com/persvr/rql/blob/master/js-array.js)).

No more eval or new Function. (it has been made for Adobe Air projects. Adobe Air does not allow eval or new Function).

Contains could also check if an array is contained in another array.

Could handle dotted path for sub properties.

## Install

```shell
npm i orql
# or
bower i orql
# or
git clone https://github.com/nomocas/orql.git
```

## Examples

```javascript
var filtered = orql([{a:{b:3}},{a:3}], "a.b=3"); // -> [{a:{b:3}]
```

For full API, look at [RQL github](https://github.com/persvr/rql);

## Tests

### Under nodejs

You need to have mocha installed globally before launching test. 
```
> npm install -g mocha
```
Do not forget to install dev-dependencies. i.e. : from 'decompose' folder, type :
```
> npm install
```

then, always in 'decompose' folder simply enter :
```
> mocha
```

### In the browser

Simply serve "orql" folder in you favorite web server then open ./index.html.

You could use the provided "gulp web server" by entering :
```
> gulp serve-test
```


## Licence

The [MIT](http://opensource.org/licenses/MIT) License

Copyright (c) 2015 Gilles Coomans <gilles.coomans@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the 'Software'), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
