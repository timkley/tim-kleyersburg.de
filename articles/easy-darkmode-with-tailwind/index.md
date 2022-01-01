---
date: 2022-01-01
title: 'Easy dark mode with TailwindCSS'
metaDescription: 'If you are using TailwindCSS, implementing dark mode is super simple.'
---

Implementing dark mode with TailwindCSS is not that new. Experimental support was added in [September 2020](https://github.com/tailwindlabs/tailwindcss/pull/2279) and being promoted to a first class citizen in November 2020 when TailwindCSS was updated to v2.

I'm currently using version 3 of TailwindCSS with the amazing [Typography plugin](https://tailwindcss.com/docs/typography-plugin). Dark mode is already activated because I am using the JIT (just-in-time) mode and is leveraging `prefers-color-scheme` to automatically switch between light and dark mode.

Since my site is very simple the changes were just a few lines of code. Just use the `dark` modifier in front of all classes that should be applied when the user prefers dark mode. In my case, most work was done with these few lines of code:

```html
 <body class="bg-gray-50"> <!-- [tl! --] -->
 <body class="bg-gray-50 dark:bg-gray-800 dark:text-gray-100"> <!-- [tl! ++] -->
```

```postcss
a {
    @apply hover:text-gray-700 // [tl! --]
    @apply hover:text-gray-700 dark:hover:text-gray-400 // [tl! ++]
}
```

After updating the Typography plugin to the latest version using `npm install -D @tailwindcss/typography@latest` to only change to use the default inverted dark mode is as follows:

```html
 <div class="prose"> <!-- [tl! --] -->
 <div class="prose dark:prose-invert"> <!-- [tl! ++] -->
```

The only things left to do is to get all the details right, which can be tricky of your site is larger. In my case [I needed to change 6 files or 8 lines](https://github.com/timkley/tim-kleyersburg.de/pull/13/files) to make it work.