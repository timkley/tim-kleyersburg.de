---
date: 2022-01-01
title: 'Easy dark mode with TailwindCSS'
hero: 'hero-image.jpg'
metaDescription: 'If you are using TailwindCSS, implementing dark mode is super simple.'
tags:
    - css
---

Implementing dark mode with TailwindCSS is not that new. Experimental support was added in [September 2020](https://github.com/tailwindlabs/tailwindcss/pull/2279) and being promoted to a first class citizen in November 2020 when TailwindCSS was updated to v2.

I'm currently using version 3 of TailwindCSS with the amazing [Typography plugin](https://tailwindcss.com/docs/typography-plugin). Dark mode is already activated because I am using the JIT (just-in-time) mode and is leveraging `prefers-color-scheme` to automatically switch between light and dark mode.

Since my site is very simple the changes were just a few lines of code. Just use the `dark` modifier in front of all classes that should be applied when the user prefers dark mode. In my case, most work was done with these few lines of code:

```html
<body class="bg-gray-50">
    <!-- [tl! --] -->
    <body class="bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
        <!-- [tl! ++] -->
    </body>
</body>
```

```postcss
a {
    @apply hover:text-gray-700 // [tl! --]
    @apply hover:text-gray-700 dark:hover:text-gray-400 // [tl! ++];
}
```

After updating the Typography plugin to the latest version using `npm install -D @tailwindcss/typography@latest` the only change needed to use the default inverted dark mode is to add _one_ class:

```html
<div class="prose">
    <!-- [tl! --] -->
    <div class="prose dark:prose-invert"><!-- [tl! ++] --></div>
</div>
```

Now, to get all the details right, go through your site to spot everything that is not properly styled when using dark mode.

> _Tip:_ Using Chrome with the dev tools open, hit `Cmd + Shift + P` to open the command palette. Type `prefers-color-scheme` and choose the simulate option for `dark` or `light` to quickly review your changes without needing to change your system preferences.

This may be the trickiest part if your site is larger. In my case [I needed to change 6 files or 8 lines](https://github.com/timkley/tim-kleyersburg.de/pull/13/files) to make it everything look good.
