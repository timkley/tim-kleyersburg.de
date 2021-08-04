---
layout: blog
date: 2021-08-04
tags: post
title: 'From Vue.js to Alpine: Our journey'
permalink: 'blog/from-vue-to-alpine/'
---

# {{ title }}

## The problem
We relaunched the e-commerce site from one of our clients in the end of 2019. It was a big relaunch, impacting the overall design, template and frontend
architecture.  
The only thing pretty much left unchanged was the backend.

The main goals defined with the client where:

- optimise PageSpeed metrics
- Improve usability and therefore conversion rate

After months of implementing, the client, and we, where happy with the results. We hit green ratings in all 4 of Lighthouses categories and the conversion rate
improved significantly.

That was until Google decided to change how Lighthouse calculates the performance
score ([What's New in Lighthouse 6.0](https://web.dev/lighthouse-whats-new-6.0/)).  
Our scores dropped from solid green ratings to red.

As a quick reminder: additionally to things like TTFB (Time till first byte) and overall network performance things like file sizes, optimised CSS or webfonts
Lighthouse moved the focus to frontend stuff like „Time To Interactive“ or „Largest Contentful Paint“. As the web becomes more and more interactive the
perceived performance becomes more important. So, in theory, we agreed with Googles step to include those new metrics. Although it's comparing apples to oranges
when Google presents nearly non-interactive sites like blogs as „good examples“.

After the first meetings with the client we postponed optimising for the new Lighthouse metrics. After analysing what devices our visitors most commonly used we
couldn’t rationalise investing much time into a problem all our competitors faced also.

That changed with Googles announcement that some of these new metrics would impact search
ranking ([Timing for bringing page experience to Google Search](https://developers.google.com/search/blog/2020/11/timing-for-page-experience)).

It was clear we shouldn’t postpone this issue further (this was late 2020 / early 2021).

We talked to the client again and decided we could gain a significant competitors advantage and of course perceived speed for the end users.

## How we analyzed
We now needed more data. To be frank: until this point the deeper performance metrics where never our biggest concern so we had some catching up to do.  
Using Google Chrome we analysed the website with a mix of the built in Lighthouse app as well as the Performance tab in DevTools.

## Our setup at this time

When we did the relaunch we completely reimplemented the frontend architecture. We were using Vue 2 as our javascript framework of choice and TailwindCSS.
Everything was bundled by Symfony Encore (Webpack).

The site was no SPA, instead we wrapped the whole site with a `#app` div which we bound the root instance to. We used renderless
components ([Renderless Components in Vue.js](https://adamwathan.me/renderless-components-in-vuejs/)) so we could write most of our templates in Twig and also
make easy use of server side variables without the need of writing an API.

*Include screenshot of renderless component with JSON encoded server variables as prop*

## Analysing the problem
Our process looked like this:
1. Do performance report in chrome
2. Look at the numbers
3. Change something
4. Create new report to confirm or refute our assumption

The most significant part of the Performance Report was the „Evaluating scripts“ part. It seemed like the browser had a lot of work to do when evaluating our javascript bundle.

*Provide screenshot of the performance report*

Our first step was to comment out the script tag so see how that improved our metrics.  
Turns out, pretty significantly:

*Provide screenshot of the performance report*

We ran some additional tests to create insights regarding Google Tag Manager and our Cookie Consent Management. Our CCM provider is pretty expensive in terms of performance budgets. We are evaluating implementing our own, more on that later (maybe).

*Provide screenshots of reports with and without GTM / CCM.*

## What needed to be done
We identified the following things needed the most attention:
1. Optimising preloading of key assets
2. Minimising blocking time
3. Optimising time to interactive
4. Minimising main thread work

## Part 1: Optimising preloading
We experienced some render blocking because of quick and dirty implementations without proper performance testing for the Google Tag Manager and our CCM.

We tested different combinations of preloading and pre-connecting and ended up with the following results:

- Preload for key assets like the CCM script
- Preconnect for GTM
- Preloading of our own key assets (like webfonts or our main css/js file)

The following tools where used:
- Lighthouse: provides direct insight which assets should be preloaded
- Firefox: the devtools tell you which fonts you are preloading but aren‘t used within the first seconds

## Part 2-4: Optimising the rest
After we optimised the preloading we knew the only parts left that are impacting our key metrics could only be our own assets as in our javascript bundle.

All these metrics are somewhat connected to each other because of this.

## Finding the problem
Before optimising we first needed to understand the problem on a deeper level. As mentioned earlier we are using renderless components for all our Vue components and are wrapping our whole site with the Vue instance.
This gives us the benefit of simple global state management. We can also simply sprinkle in some interaction by adding another mixin.

*Example of global state (e.g. search overlay)*

*Example of a mixin providing some global functionality without bloating the entry file.*

## Different versions of Vue
Vue comes in two different „flavours“: the runtime-only-build and the one with included template compiler.  
The runtime-only build is much smaller. It can only be used if you are using Single-File-Components. Those will be included in your bundle and therefore make the template compiler unnecessary.  
The template compiler enables us to provide templates from our templating engine (Twig) into the default slots of our renderless components.

But: because we were wrapping the complete site, Vue has to evaluate every DOM node it finds (around 4,500 nodes for the homepage).  
That's why we had such a long evaluate script time.

Now that we better understood the root cause we could start evaluating paths to mitigate this issue.

Unfortunately we couldn't find a way to significantly improve the performance with our current architecture. There is just no good way to switch to the runtime-only build with our template architecture and backend structure.

## Evaluating the needs
Next, we put together the components and interactivity we currently provide on the site to get a birds eye view of the things we need from a new solution.

Here are some examples of components we have on the site:
- Live search
- Dynamic offcanvas cart
- A flyout menu
- Modals

We also have some smaller functions (previously provided by mixins). Those functions are mostly used for things that don‘t need a separate component because they hold little to no state but should be easily be triggerable from everywhere, like:
- dynamically changing a product variant
- Opening the shipping modal
- Showing / hiding a global information banner

One thing all these things had in common: many of them needed to communicate with each other.

The components where not the most complex, mostly providing interactivity or preventing site reloads.

What we needed (and wanted) from a new framework was:
- Reactivity (templates rerender when data changes)
- Event system for easy communication between components
- Small footprint

## Evaluating alternatives
Proof of concept
- Event bus
- First metrics
-

Comparison between old and new metrics


Things I've learned
- https://stackoverflow.com/a/45696430 (currying) Solves a problem with the instance not being present in the event handler Solved the problem of only loading the navigation once per session
- How preload, preconnect exactly works