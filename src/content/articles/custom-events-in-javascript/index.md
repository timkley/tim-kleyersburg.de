---
date: 2022-04-14
title: 'JavaScript: Custom Events explained'
hero: 'hero-image.jpg'
metaDescription: 'How to use events to make your site more maintainable'
---

In every bigger project I've worked on (especially in e-commerce) there comes a time where you need to tie different parts of your JavaScript together. Maybe you want to track something when a user interacts with your site or does something of value (like adding a product to the cart). Or you need to trigger some behaviour in a totally different component.

The most straightforward approach is to just add the needed functionality to the code where the new behaviour should happen:

```js
function addToCart(productId) {
	// add product to cart
	// ...

	// code you'll often see in Google Tag Manager integrations
	dataLayer.push({
		event: 'addToCart',
		// ...
	})
}
```

If you are only doing this for one integration it might be fine. But imagine you need to do this for multiple services and things will become messy very quickly:

```js
function addToCart(productId) {
	// add product to cart
	// ...

	dataLayer.push({
		event: 'addToCart',
		// ...
	})

	otherIntegration.trackAddToCart()

	anotherServiceWhichNeedsMuchMoreWork.startTransaction()
	anotherServiceWhichNeedsMuchMoreWork.addProduct()
	anotherServiceWhichNeedsMuchMoreWork.sendTransaction()
}
```

If you keep doing this your site _will_ become harder to maintain because you can never be sure if you might be breaking functionality in some of the added parts.

Lets take a look at an alternative, very flexible approach using native JavaScript events.

## What are events?

Basically events are things that can happen on your site. You can listen for these events to happen and act on them. Browsers implement a lot of events that cover every interaction you can have with a site.

### Built-in events

These are the events like mouse clicks, taps, key presses and so on. If you want, paste the following snippet into the console of your browser dev tools. It'll log every event that is happening to the console.

```js
Object.keys(window).forEach(key => {
    if(/./.test(key)){
        window.addEventListener(key.slice(2), event => {
            console.log(key, event)
        })
    }
})
// ~~stolen~~ kindly borrowed from https://stackoverflow.com/a/61399370
```

You probably added event listeners before to let something happen on events like a `click` by using `element.addEventListener('click')`.

You maybe know you can add built-in events to HTML elements by using the `onX` attributes, where `X` is substituted be the name of the event.

These attributes don't depend on any additional JavaScript to work. When this attribute exists it will always work. Try adding `onclick="alert('Hello!')"` to any element with your browsers dev tools, click on it and you will see the alert.

### Custom events

Everthing which is not a standard interaction can be a custom event. In an e-commerce site this could be the add-to-cart functionality. Most likely this action will be triggered by a built-in event, like a click, but by using a custom event you gain a lot of flexibility because your not dependant on that click anymore.  
Custom events have to be dispatched manually when the specific event actually happens.

First, define your custom event. It needs a name, I'll use `custom-event` in this case, and accepts an optional second object parameter which includes all properties a normal event can set as well as an additional `detail` property which can return details about your specific event.

```js
const customEvent = new CustomEvent(
	'custom-event',
	{ detail: 'your-details' }
)
```

Secondly, you'll need to dispatch the event. Events can be dispatched from any `HTMLElement`. Depending on your use case you'll either use a specific element or just use the window element:

```js
window.dispatchEvent(customEvent)
```

That's all there is to it. Just define a new event, give it a name and an optional payload, dispatch it and you have a global custom event which you can now use in other parts of your site.

Lets rewrite our first example to something more maintainable:

```js
function addToCart(productId) {
	// add product to cart
	// ...

	const addToCartEvent = new CustomEvent(
		'add-to-cart',
		{ detail: { productId: productId } }
	)
	window.dispatchEvent(addToCartEvent)
}
```

I like to use a little helper function for creating and dispatching global events for better readability.    
I also like the ability to pass the original event (this can be useful if you dispatch your custom event with a built-in attribute like `onsubmit` and want to have access to the original event).

```js
function customEvent(name, payload = null, originalEvent = null) {
    // options should be an object with:
    // name: 'string',
    // payload: 'object'
    // originalEvent: 'this', if you need the actual event target

    const customEvent = new CustomEvent(name, {
        detail: {
            payload: payload,
            originalEvent: originalEvent
        },
    })

    window.dispatchEvent(customEvent)
}
```

Let's now use our helper to dispatch the event:

```js
function addToCart(productId) {
	// add product to cart
	// ...

	customEvent('add-to-cart', { productId: productId })
}
```

Instead of writing all the tracking and other logic, which has nothing to do with actually adding the product to the cart, we are now simply dispatching a custom event in one line.

This keeps our `addToCart` function small and clean but still puts us in a position to add more to it. If you are familiar with the [SOLID principles](https://www.digitalocean.com/community/conceptual_articles/s-o-l-i-d-the-first-five-principles-of-object-oriented-design) of programming you maybe recognise the `S` (Single responsibility) and `O` (Open for extension, closed for modification) of this. By just raising an event we don't break the single responsibility principle and at the same time allow extension because we can now react to the dispatched event without the need to modify our original code.

In another part of our code (maybe in a file called `tracking.js`) we can now write the following code:

```js
function addToCartHandler(event) {
	// the handler will accept the event
	// you can access the payload from the `detail` property
	const productId = event.detail.productId

	// do your thing, like tracking or any other functionality
}

// attach your custom event listener
window.addEventListener('add-to-cart', addToCartHandler)
// 🎉 done!
```

You can attach as many handlers as you want and therefore append on your add-to-cart functionality as much as you need. If the need comes up to add another tracking library you can just attach another event listener and can leave your add-to-cart functionality completely unchanged, thus reducing the chance of introducing nasty bugs 🐛.

## Interoperability between frameworks

Since custom events are native to the browser nothing prevents you from creating bridges between multiple frameworks.

You could, for example, listen for a custom event dispatched by a Vue component in your native javascript bundle or even a simple inline script tag. You just need to make sure you attach your event listeners _before_ dispatching the events. Otherwise nothing will happen.

There is one particular use-case where we leveraged custom events within a Vue application: replacing HTML directly from a server request while maintaining interactivity.

We have an e-commerce application which uses Vue as its framework of choice, but uses renderless components. For the most part there are no backend APIs, so changing something like the price of an article based on the choice of a user means we'll have to swap out some parts with server side rendered HTML.

As you may know, this breaks any Vue-based event handlers because they are destroyed with the swapped out HTML leaving you with dumb old HTML.

What we did is, we swapped out Vue specific handlers like `v-on:click` with built-in event attributes and use the helper function to dispatch a global event:

```
<button v-on.click="chooseOption({'size': 'xl'})"> [tl! remove]
<button onclick="customEvent('choose-option', {'size': 'xl'})"> [tl! add]
```

Quick recap on the `onX` attributes: you don't have to attach any event listeners yourself, thus eliminating the need for some extra step which re-initializes event listeners after swapping out the HTML. In combination with a custom event that makes them perfect for this use-case because you can just dynamically add HTML with these attributes and they just work.

Another benefit is that you don't need to wire up every piece of HTML with a dedicated click handler to make it do something. Instead it'll just fire your custom event for which you defined your handlers beforehand.

So, within our relevant Vue component we can now do something like this:

```js
methods: {
	chooseOption(optionsObject) {
		// handle loading and swapping out the
		// HTML of the product card
		// ...
	}
},
// Use the `created` lifecycle hook to attach our event listener
created() {
	// we'll attach an event listener for the name of your custom
	// event and use our existing method to defer to it
	window.addEventListener('choose-option', event => this.chooseOption(event.detail))
}
```

I skipped all other parts, like checking for the correct product id and so on but I hope you'll get the idea.

One thing that's great about this approach is that by choosing to use the browsers API for events you are able to dispatch and listen for events from every part of your app as long as you make sure to always define your listeners before dispatching events.

Below you can find an interactive demonstration of how this works. It uses Vue for managing the cart state, AlpineJS for the logging of our events and you can add more buttons dynamically and see everything still works as expected.

<p class="codepen" data-height="300" data-default-tab="js,result" data-slug-hash="GRrVyZG" data-user="timkley" style="height: 300px; box-sizing: border-box; display: flex; align-items: center; justify-content: center; border: 2px solid; margin: 1em 0; padding: 1em;">
  <span>See the Pen <a href="https://codepen.io/timkley/pen/GRrVyZG">
  Custom Events</a> by Tim (<a href="https://codepen.io/timkley">@timkley</a>)
  on <a href="https://codepen.io">CodePen</a>.</span>
</p>
<script async src="https://cpwebassets.codepen.io/assets/embed/ei.js"></script>

### Resources

[Event - Web APIs | MDN](https://developer.mozilla.org/en-US/docs/Web/API/Event)  
[CustomEvent - Web APIs | MDN](https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent)