[![npm](https://img.shields.io/npm/l/use-vue.svg)](https://www.npmjs.org/package/use-vue)
[![npm](https://img.shields.io/npm/v/use-vue.svg)](https://www.npmjs.org/package/use-vue)
[![npm](https://img.shields.io/npm/dm/use-vue.svg)](https://www.npmjs.org/package/use-vue)
[![Travis CI](https://img.shields.io/travis/lixinliang/use-vue.svg)](https://travis-ci.org/lixinliang/muse-vue)
[![bitHound Code](https://www.bithound.io/github/lixinliang/use-vue/badges/code.svg)](https://www.bithound.io/github/lixinliang/use-vue)
[![Greenkeeper badge](https://badges.greenkeeper.io/lixinliang/use-vue.svg)](https://greenkeeper.io/)
[![Twitter](https://img.shields.io/badge/twitter-@qq393464140-blue.svg)](http://twitter.com/qq393464140)

# use-vue

> Declare `use vue` in Atom as `use babel`.

## Getting started
```
$ npm install --save use-vue
```

## Usage

```js
import 'use-vue';
import app from './app.vue';

console.log(app);
```

## CSP

`CSP` is `Content Security Policy`. In `Atom`, it would occur.

```js
new Vue(app).$mount('app'); // CSP Error
```

You can import `Vue` from `use-vue`.

```js
import { Vue } from 'use-vue';
import app from './app.vue';
new Vue(app).$mount('app');
```

Or, use [loophole](https://www.npmjs.com/package/loophole) to avoid this problem.

```js
import 'use-vue';
import Vur from 'vue'; // select the Vue version you want.
import app from './app.vue';
import { allowUnsafeNewFunction } from 'loophole';

allowUnsafeNewFunction(() => {
    new Vue(app).$mount('app');
});
```

## Language

As `Atom` default, support this following language,

```vue
<script lang="ts">
    // code
</script>

<script lang="babel">
    // code
</script>

<script lang="coffee">
    // code
</script>

<style lang="less">
    // code
</style>
```

If you want to use any language else, you can register yourself. [@see](https://github.com/lixinliang/require-extension-vue#loaderstyleregister)


## License

MIT
