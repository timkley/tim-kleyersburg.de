---
date: 2021-09-01
title: 'Customisable TailwindCSS colours without build step'
metaDescription: 'How to let the customers set their own colours in your product / application.'
---

# Customisable TailwindCSS colours without build step

One of our clients provides a marketplace. You can book a package and get a complete website with content, e-commerce functionality and the possibility to add your own content and define your brand colours.

The project was built with [TailwindCSS](https://tailwindcss.com) and our config looked something like this:

```
module.exports = {
    theme: {
        extend: {
            colors: {
                'primary-dark': '#1c3d5a',
                'primary': '#3490dc',
                'primary-light': '#bcdefa',
            }
        },
    },
    // ... rest of the config
}
```

Our first problem was with semantics. The first layout consisted of one color in different shades. It made sense to group these colours together. But if the clients choose 3 very different colours, because that's their corporate design, these keywords would loose meaning.

This is one of the very few and still very manageable downsides of using TailwindCSS. You have to search&replace all your template files if you want to rename a colour. But most of the time your good to go with a regular expression like this one:

```regex
(?:$|^|)(your-color-name)(?:$|^|)
```

> I don't understand this RegEx enough to explain it in detail. I [just googled it](https://regex101.com/library/1COSOf).

In the end we didn't change the names because the entry page still used the original layout, so in development it would only loose meaning when manually choosing a marketplace vendor. We could live with that.

## CSS Variables to the rescue!

After initial thoughts of generating the accompanying CSS files for each vendor (the dev ops guy hated that idea) we put together a proof of concept using [CSS Variables](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties).

We changed our `tailwind.config.js` to this:

```
module.exports = {
    theme: {
        extend: {
            colors: {
                'primary-dark': 'var(--primary-dark)',
                'primary': 'var(--primary)',
                'primary-light': 'var(--primary-light)',
            }
        },
    },
    // ... rest of the config
}
```

Now we could just set the colours from our server side rendered templates like this:

```
<style>
    :root {
        --primary-dark: #1c3d5a;
        --primary: #3490dc;
        --primary-light: #bcdefa;
    }
</style>

```

And that's it for the frontend part! Now every vendor can set his own colours in his backend and the change live as soon as the settings were saved.

### Live preview

With just using CSS we could also provide a live preview in the frontend for logged in vendors so they could see how their colour combination would look.

Setting a CSS variable from JavaScript is as simple as calling the `setProperty` method on whichever element makes sense for your application.

Since we set those variables for `:root` the `documentElement` made sense for us:

```
document.documentElement.style.setProperty(`--variable-name`, newValue);
```

### Internet Explorer support

In an earlier iteration we still needed to support Internet Explorer 11. Fortunately this changed since then, but if you want or need to support IE11 you can use the [this ponyfill](https://github.com/jhildenbiddle/css-vars-ponyfill).

After successful installation it was literally a one-liner, we only had to call `cssVars()` when we detected we had an IE11 user on the site.

We're still glad we could remove this dependency from our bundle.