---
date: 2022-05-21
title: 'Alpine Expression Error: Cannot set properties of null (setting _x_dataStack)'
hero: 'hero-image.jpg'
metaDescription: 'How to fix this Alpine.js Expression Error'
tags:
  - javascript
  - quicktip
---

{% image 'alpine-expression-error.png', 'Screenshot of a console showing a warning about an Alpine expression error' %}

If you ran into the above error before, one of the most common causes is forgetting to define exactly one root element.

So, if your code looks like this:

```html
<template x-if="loading">
  Loading...
</template>
```

You need to change it to this (instead of a `div` you may use any other valid HTML element):

```html
<template x-if="loading">
  <div>
    Loading...
  </div>
</template>
```

As stated in the [Alpine.js docs](https://alpinejs.dev/directives/if), `template` tags may only contain one root element, and text in itself does not qualify as an element.