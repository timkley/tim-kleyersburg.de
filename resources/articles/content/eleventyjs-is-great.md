---
date: 2022-01-03
title: Going all in with Jamstack and Eleventy
hero: hero-image.jpg
excerpt: I rebuilt my personal site with Eleventy, a static site generator, and am loving it!
tags: [11ty]
image: /articles/img/ogimages/eleventyjs-is-great.webp
---

I wanted to create a new version of my website for quite some time. [The old version](https://github.com/timkley/tim-kleyersburg.de/tree/54da506cab1f437faf98c4c87e5c89dd82b99222) was just one site which was also statically generated HTML. I was using `gulp` along with a plugin so I could use [Twig templates](https://twig.symfony.com/doc/2.x/), mainly because I hate to repeat myself in templates, even if it's just a few hundred lines of markup.

I learned the hard way that missing abstraction is the source of many bugs that never should have occurred in the first place. In my early days it was much easier to just copy and paste the (seemingly final) pieces of code, swap out their contents and _be done with it_ ™.

Jokes on me: I never should have assumed there's such a thing as _being done_ when it comes to the web. Everything is subject to change, at any time. Some may call this a curse, to me it's one of the greatest things about the web. Every mistake can be undone, nothing is final. Accepting this premise greatly reduced my anxiety about shipping the perfect thing on the first try.

> Ship early, iterate, improve.

I don't know about you, but I know what _I_ did last summer. I wanted to redesign my website and start to write articles about things I care.

At first I tried to come up with a solution with my existing setup but couldn't really see how to integrate a blog into my site.

![Screenshot of my previous site](screenshot-old-site.jpg)

Like every good developer I naturally questioned my whole stack. Technology moves fast, especially when it comes to the web. My site was 2 or 3 years old, a Methusalem in web-years (that's kinda like dog-years). Frontend tooling moved fast and there where a bunch of options I explored in my head.

Developer experience is important to me. I want to be able to make my own decisions as well as be pampered with relevant features that make my life easier and not reinvent the wheel.

I knew I did not want to install a CMS. At first [Statamic](https://statamic.com) seemed like an obvious choice. We are using it for every new site we build in [our agency](https://www.wacg.de) and I like everything about it.  
But: a CMS also has drawbacks. In my case I didn't want another system I have to manage. I didn't want its shiny cool features. I just wanted to create content and present myself as easy as possible.

So I explored something else.

## Jamstack: the elephant in the room

As an avid reader of [CSS-Tricks](https://css-tricks.com/) the [Jamstack](https://jamstack.org/) was a buzzword I've read a few times before. But it never really sparked my attention. Working mainly with the LAMP-stack (Linux, Apache, MySQL, PHP) at work, because the dynamic component of PHP always was a necessity, another "stack" seemed like something that would just waste my (very limited) mental capacity.

But: my personal site didn't need PHP and I had no interest in using PHP for it.

### What is the Jamstack?

Personally, what pushed me in the wrong direction how to think about the Jamstack was the word "stack" itself. It sounded like many parts of some server environment I was unfamiliar with. Which sounded like a lot to learn.

But that's not the case.

> Jamstack is an _architecture_ designed to make the web faster, more secure, and easier to scale.

Source: [jamstack.org](https://jamstack.org), emphasis by me.

It's not about Apache, Linux or PHP. It's basically about static HTML being served as fast as possible. And since it's static it can also be served by a CDN from multiple locations with great speed and security.

Writing static HTML sounds like a nightmare if your website contains more than one page. I'd rather have a slower site and don't have to change static HTML for every page of my website. I'd use a server side language like PHP to dynamically create the HTML.

Fortunately we don't have to choose one over the other! One part of Jamstack is "Pre-rendering", which essentially means to create all the HTML before serving it, not in the run-time.

## Static Site Generators – especially Eleventy

This is where Static Site Generators (SSG) come in. One of many SSGs is [Eleventy](https://11ty.dev). It seems like Eleventy really gained traction in the last year, I definitely read more about it in the last months (altough this could be because my interest in it increased). After about half a year of using it I can say: it is all I wanted from my previous setup but is so much more mature. It doesn't dictate how I have to structure my website, what CSS framework I should use or what the best base markup is.

Eleventy focuses on your content and not on the layout. _Bring Your Own HTML_ was never so easy. And in the end it'll output static HTML which you can host, probably, anywhere.

If you are just starting your journey into web programming and are learning HTML and CSS for the first time, also learning about servers, content management systems, databases, and more might be overwhelming. Eleventy, with its flat learning curve, could be the perfect companion on your journey.  
You can start very small and add more and more features (if you want and need) on top of it.

### Resources

There are a lot of great tutorials on the web on how to use Eleventy. These are some that helped me the most when starting out:

🔗 [11ty.dev/docs/](https://www.11ty.dev/docs/)

The official documentation is a good place to start. It explains all concepts in detail and can be used as a reference.

🔗 [11ty.rocks](https://11ty.rocks/)

11ty.rocks is a collection of many great resources, tutorials, plugins and much more, created by [Stephanie Eckles](https://x.com/5t3ph). The site itself is, of course, also built with Eleventy. You can browse the source code on [GitHub](https://github.com/5t3ph/11ty-rocks).

🔗 [learneleventyfromscratch.com](https://learneleventyfromscratch.com/)

Learn Eleventy From Scratch was the tutorial I learned to most from. It is a 31 lessons tutorial which covers nearly every use case you could think of. If you already know some HTML and CSS, this should get you very far.
