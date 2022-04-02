---
date: 2022-03-18
title: 'Create an API proxy with Cloudflare Workers'
hero: 'hero-image.jpg'
metaDescription: 'Use Cloudflare Workers as a proxy for other API services without running your own server.'
---

In this article we'll discover how to use [Cloudflare Workers](https://workers.cloudflare.com/) to consume third party APIs without the need for your own server or compromising on security.  
I used this to create this dynamic widget on my homepage to show which song I listened to last, scrobbled from Spotify to [Last.fm](https://last.fm):

<div class="not-prose text-base my-4">
  {% include 'last-scrobble.njk' %}
</div>

## Why don't you use Javascript?

You might ask yourself, since we are just writing plain JavaScript: why not use it directly from our bundle? In case of the Last.fm API this might actually not be the worst idea in the world, since it's a read-only API. You can't change anything on behalf of the user, only consume.

But: with Twitters API, for example, you might actually be able to post a tweet on behalf of a user when you get access to their API keys.

So, the short answer is: security. You don't want your API keys floating around in public. (I actually made this mistake in the past and [pushed my API keys to GitHub](https://github.com/timkley/tim-kleyersburg.de/blob/ecee30507d388362c2c6171196b382f0669ee762/api/twitter.php#L3-L4). I created new keys since then, so no problem there, but this was danger zone ☠️).

## But this could be solved by - insert server side language here -

Indeed, it could. But using a server side language also means you have to manage a server and write a backend to provide this functionality. And while I love using something like Laravel: if you just need a simple endpoint which returns some data, this might be overkill.

Instead, we'll use a serverless function on Cloudflare so achieve the same thing, but without the unneeded, and in my case unwanted, overhead.

## Here's what we're gonna do

1. Create a new account on Cloudflare
1. Install `wrangler`, Cloudflares CLI
1. Login and generate a new project
1. Set up a development environment
1. Write the code
1. Deploy 🎉

### Create a new account on Cloudflare

Or don't, if you already have one. Cloudflare offers a generous free tier for Workers of 100,000 free requests _per day_. This should be more than enough, even for ambitious hobby projects. Looking at my stats I had 6 request today. And it's early evening now. More than enough headroom for me!

### Install `wrangler`, Cloudflares CLI

Next you need to install [Cloudflare Wrangler](https://github.com/cloudflare/wrangler). Wrangler is Cloudlfares CLI tool you'll use to generate projects, manage secrets and deploy workers to your account.

Run the following command from your preferred shell to install `wrangler`.

```shell
npm i @cloudflare/wrangler -g
```

> For up to date information about the installation process please [refer to the Cloudflares docs](https://developers.cloudflare.com/workers/).

After you've successfully installed `wrangler` you'll want to log in so `wrangler` has access to your account and can publish new projects on your behalf. Run the following command to authenticate with Cloudlfare.

```shell
wrangler login
```

Now that you are logged in you can generate a new project with the following command:

```shell
wrangler generate your-worker
```

If you don't specify any other options the [default starter pack](https://github.com/cloudflare/worker-template) will be used. This default template uses JavaScript as its language of choice and its what we'll use to build our serverless proxy.

After creating your worker, change into the newly created directory `your-worker` (or the name you specified) and you are ready to go.

### Set up a development environment

Before actually writing any code we'll configure our project with our individual account id and set an appropriate name. This is needed for using wranglers `dev` command (allowing you to preview what you're doing) and also for deploying later on. All the hard work of running your function will happen on Cloudflares edge workers linked to your account which are routed to your local machine.

Go ahead and open the file `wrangler.toml` in your favorite editor. It should look something like this:

```
name = "helloworld"
type = "javascript"

account_id = ""
workers_dev = true
route = ""
zone_id = ""
```

Next, run `wrangler whoami` to get your account ID:

```
wrangler whoami

+--------------+----------------------------------+
| Account Name | Account ID                       |
+--------------+----------------------------------+
| Your Account | ${yourAccountId}				  |
+--------------+----------------------------------+
```

Copy the value of your account ID and change your `wrangler.toml` like this:

```
name = "helloworld" [tl! remove]
name = "your-worker-name" [tl! add]
type = "javascript"

account_id = "" [tl! remove]
account_id = "your-account-id" [tl! add]
workers_dev = true
route = ""
zone_id = ""
```

I'll take the following bit directly from Cloudflares [Get Started](https://developers.cloudflare.com/workers/get-started/guide/#5a-understanding-hello-world) guide to explain how a worker fundamentally works:

> Fundamentally, a Workers application consists of two parts:
> 1. An event listener that listens for `FetchEvents`, and
> 2. An event handler that returns a `Response` object which is passed to the event’s `.respondWith()` method.
> When a request is received on one of Cloudflare’s edge servers for a URL matching a Workers script, it passes the request to the Workers runtime. This dispatches a FetchEvent in the isolate where the script is running.

In fact, the following code, directly taken from the basic cloudflare template, does exactly these two things described above:

```js
// 1. listen for fetch events
addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request))
})

// 2. event handler which returns a Response object
async function handleRequest(request) {
  return new Response('Hello worker!', {
    headers: { 'content-type': 'text/plain' },
  })
}
```

If you want to preview this script you can now run `wrangler dev` to boot up a preview environment which you can access from localhost. This will deploy the worker to an edge worker and provide you with a local URL to see it in action.

```shell
wrangler dev
💁  watching "./"
👂  Listening on http://127.0.0.1:8787
```

You can now visit `http://127.0.0.1:8787` in a browser or use an API client like [Insomnia](https://insomnia.rest/products/insomnia) to inspect and debug your endpoint. Changes to your code are directly reflected so you don't have to manually redeploy your code to preview it.

### Write the code

We finally left the setup part behind us and are ready to write some actual code! If you are familiar with JavaScript, especially on the Node.js end, you should have no problems following the next steps.

Basically all we want to do is make a `fetch` request to a predefined endpoint and return the result. If you are impatient you can find the finished code on [GitHub](https://github.com/timkley/lastfm-cloudflare-worker).

We'll gradually build up our `handleRequest` method step by step so you can follow along.

> Note: For these steps to work without errors you'll need to provide the API key as a secret. To do this run the command `wrangler secret put LASTFM_API_KEY` and paste your key. If you don't have an API key you can obtain one from [Last.fm's developer portal](https://www.last.fm/api/account/create).

---

#### Step 1: Build the request URL which we'll fetch later

```js
// Note that we are using an `async` function. This will become important from step 2 onward
async function handleRequest(event) {
  // You can find the API documentation here: https://www.last.fm/api/show/user.getRecentTracks
  // We use template strings to interpolate our secret API key into the URL
  const url = `http://ws.audioscrobbler.com/2.0/?format=json&method=user.getrecenttracks&user=your-username&limit=1&api_key=${LASTFM_API_KEY}`
}
```

--- 

#### Step 2: Use `fetch` to get a response

```js
async function handleRequest(event) {
  const url = `http://ws.audioscrobbler.com/2.0/?format=json&method=user.getrecenttracks&user=your-username&limit=1&api_key=${LASTFM_API_KEY}`

  // we'll use `fetch` in combination with `await` so we don't have to manually resolve the returned `Promise`
  // this is why we defined the whole function as `async`, so we can use `await`
  const response = await fetch(url)

  // `fetch` will resolve to a [Response object](https://developer.mozilla.org/en-US/docs/Web/API/Response)
  // We will use the `json` method to return the responses results as a JavaScript object
  // note that we'll again use `await` since .json() returns a `Promise`

  const result = await response.json()
}
```

---

#### Step 3: Return a new response with the fetched data

```js
async function handleRequest(event) {
  const url = `http://ws.audioscrobbler.com/2.0/?format=json&method=user.getrecenttracks&user=your-username&limit=1&api_key=${LASTFM_API_KEY}`

  const response = await fetch(url)
  const result = await response.json()

  return new Response(
	// we'll use JSON.stringify() to convert the returned JavaScript object to a string which can be sent in a response
	JSON.stringify(response),
	  {
        headers: {
          // we'll set a CORS header to allow access to this resource from everywhere
          'Access-Control-Allow-Origin': '*'
        },
      }
	)
}
```

> This is the first time you should actually see a response in your browser or API client, be proud of yourself 🎉

---

#### Step 4: Cache and cleanup

Currently we will make a request to the Last.FM API every time our serverless function is invoked. This is excessive given that a new song can only be scrobbled every few minutes. To not overuse our worker we'll implement caching using the [Cache Runtime API](https://developers.cloudflare.com/workers/runtime-apis/cache/).

```js
async function handleRequest(event) {
	// Initialize the default cache
    const cache = caches.default

    // use .match() to see if we have a cache hit, if so return the caches response early
    let response = await cache.match(event.request)
    if (response) {
        return response
    }

	// we'll chain our await calls to get the JSON response in one line
    const lastfmResponse = await (await fetch(
        `http://ws.audioscrobbler.com/2.0/?format=json&method=user.getrecenttracks&user=timmotheus&limit=1&api_key=${LASTFM_API_KEY}`,
    )).json()

    response = new Response(JSON.stringify(response), {
        headers: {
            'Access-Control-Allow-Origin': '*',
			// We set a max-age of 300 seconds which is equivalent to 5 minutes.
			// If the last response is older than that the cache.match() call returns nothing and and a new response is fetched
            'Cache-Control': 'max-age: 300',
        },
    })

	// before returning the response we put a clone of our response object into the cache so it can be resolved later
    event.waitUntil(cache.put(event.request, response.clone()))

    return response
}
```

That should be it! We now have a functioning API proxy which we'll deploy in the next step.

## Deploy our code

Deploying our code with the `wrangler` CLI is as simple as running `wrangler publish`. Yep, that's it. If you don't need a custom domain this is all it takes to publish your code on Cloudflare. Read on if you want to use your own domain to deploy your worker.

### Using a custom domain

To use a custom domain you'll first need to make a few changes in the Cloudflare dashboard as well as your `wrangler.toml` file. We'll set up a new environment (called `production`) to provide the necessary configurations for deploying to our own domain.

Assuming you want to use a custom subdomain for your workers named `workers` you will first need to setup a new DNS record.

It should look like this:

![Screenshot of a Cloudflare workers DNS record](dns-record-for-cloudflare-workers.png)

This makes sure that routing works correctly.

Next, make the following changes to your `wrangler.toml`

```
name = "your-worker-name"
type = "javascript"

account_id = "your-account-id"
workers_dev = true
route = ""
zone_id = ""

[env.production] [tl! add]
route = "workers.your-domain.com/last-scrobble" [tl! add]
zone_id = "your-zone-id" [tl! add]
```

To find your zone id, log in to your Cloudflare account, choose your site and look on the right sidebar of your dashboard. Under the section "API" you will find your zone id. This makes sure the worker is published for the correct domain.

Next, you'll need to add the API key for Last.fm as a secret to the new environment. For this, run the command `wrangler secret put LASTFM_API_KEY --env production` and enter your key.

You are now ready to deploy your worker to your own domain running the command `wrangler publish --env production`!

---

I hope this helped you to get your first worker deployed to Cloudflare! If you don't want to do the work if yourself just fork the repo [of my own worker](https://github.com/timkley/lastfm-cloudflare-worker). If you have any question hit my up [on Twitter](https://twitter.com/timkley)