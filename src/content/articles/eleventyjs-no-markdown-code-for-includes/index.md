---
date: 2022-03-19
title: '11ty quick tip: Nunjucks include in markdown without indentation'
metaDescription: "If you're not careful, included files are not correctly rendered."
---

**TL;DR:** Scroll to the bottom to see how to use nunjucks whitespace control to correctly render an include within a markdown file.

---

When I was writing my [Create an API proxy with Cloudflare Workers](/api-proxy-with-cloudflare-workers) article I wanted to dynamically include the widget for what I last scrobbled so everyone could see what the purpose should be.

I knew I could set [a default template engine for markdown files](https://www.11ty.dev/docs/config/#default-template-engine-for-markdown-files), which is used to parse the files before markdown renders the rest of the file. Since I'm using [Nunjucks](https://mozilla.github.io/nunjucks/) for the rest of my site I changed the default from `liquid` to `njk` in my `.eleventy.js` configuration file:

```js
module.exports = function(eleventyConfig) {
  return {
    markdownTemplateEngine: 'njk'
  }
};
```

Next, I tried to simply include the same widget I was including on my homepage by writing:

{% raw %}
```
{% include 'last-tweet.njk' %}
```
{% endraw %}

Unfortunately, this wasn't working as expected and looked like this:

{% image 'broken-nunjucks-include.jpg', 'Screenshot of the broken Nunjucks include' %}

At first I thought it just included the raw code, but then realized that the outer parts seemed to work as expected but then markdowns [Indented code blocks](https://spec.commonmark.org/0.28/#indented-code-blocks) feature kicked in.  
This is also mentioned in the 11ty docs as a [common pitfall](https://www.11ty.dev/docs/languages/markdown/#there-are-extra-and-in-my-output).

The docs pointed me in the right direction but I just couldn't find the real culprit of why it wasn't working as expected.

To understand what the problem was let's take a quick look how I implemented that Last Scrobble widget: Basically, we have two templates. One that provides the structure of the card and one that extends it to provide the individual content. I hate to repeat myself so I reach for this pattern as often as I can.

{% raw %}
`_last-thing.njk`

```
<div>
	<div>
        {% block link %}{% endblock %}
    </div>
	{% block content %}{% endblock %}
</div>
```

`last-scrobble.njk`

```
{% extends '_last-thing.njk' %}

{% block link %}
    <a href="https://www.last.fm/user/Timmotheus">@timmotheus</a>
{% endblock %}

{% block content %}
    Dynamic track title and artist
{% endblock %}
```
{% endraw %}

Turns out: my problem was my notorious need for correctly indenting everything. When providing the content for the blocks I naturally indented everything between the `block` statements, therefore adding to much indentation. Changing it to the following solved my problem:

{% raw %}
```
{% extends '_last-thing.njk' %}

{% block link %}
    <a href="https://www.last.fm/user/Timmotheus">@timmotheus</a> [tl! remove]
<a href="https://www.last.fm/user/Timmotheus">@timmotheus</a> [tl! add]
{% endblock %}

{% block content %}
    Dynamic track title and artist [tl! remove]
Dynamic track title and artist [tl! add]
{% endblock %}
```
{% endraw %}

But that's ugly. [Whitespace control](https://mozilla.github.io/nunjucks/templating.html#whitespace-control) to the rescue! Quoting from the docs:

> Occasionally you don't want the extra whitespace, but you still want to format the template cleanly, which requires whitespace.

Yep, that's what I wanted. My first instinct was to use it on the `include`. But that was wrong, because my extra whitespace was clearly coming from my blocks. So I changed my implementation of `last-tweet.njk` to this:

{% raw %}
```
{% extends '_last-thing.njk' %}

{% block link %} [tl! remove]
{%- block link -%} [tl! add]
    <a href="https://www.last.fm/user/Timmotheus">@timmotheus</a>
{% endblock %} [tl! remove]
{%- endblock -%} [tl! add]

{% block content %} [tl! remove]
{%- block content -%} [tl! add]
    Dynamic track title and artist
{% endblock %} [tl! remove]
{%- endblock -%} [tl! add]
```
{% endraw %}

**That's it!** You should now be able to include a nunjucks template without any code indentation from markdown messing up your HTML.