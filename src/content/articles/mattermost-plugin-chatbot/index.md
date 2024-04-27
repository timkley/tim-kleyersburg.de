---
date: 2023-06-26
title: 'Mattermost plugin to send a webhook when a bot is messaged'
metaDescription: 'How to use a plugin to send out a webhook when to chat with a bot account'
tags:
    - api
    - mattermost
---

I wanted to be able to message my bot in Mattermost and have him respond to me.

Since I've wanted to use OpenAI to generate text and already have a web service for this in place which is connected to a few other accounts I wanted to be able to generate the response in this service instead of having to write a plugin for Mattermost.

But for this to work I needed to be able to know when someone messaged my bot account.

Unfortunately the [Outgoing webhooks integration](https://developers.mattermost.com/integrate/webhooks/outgoing/) only works in public channels, not in direct messages.

So I decided to write a little plugin for Mattermost which sends a webhook to my service when someone messages the bot account.

## The plugin

I've open sourced the plugin [on GitHub](https://github.com/timkley/mattermost-plugin-bot-webhook). You can use this if you don't want to write your own plugin.

For those interested I'll explain the things which weren't clear to me when creating the plugin because there was no clear documentation on this (or I couldn't find it).

The plugin is based on the [mattermost-plugin-starter-template](https://github.com/mattermost/mattermost-plugin-starter-template), so I won't explain the basics of how to create a plugin but only the things which are specific to this plugin.

Everything important happens in the file `server/plugin.go`.

## Adding configuration

I wanted to be able to quickly change the webhook URL without having to recompile the plugin. For this I added a configuration option to the plugin.

You do this by adding a struct called `Configuration` to the plugin struct:

```go
type Configuration struct {
	BotUserID  string
	WebhookURL string
}
```

You can then add a new type for your plugin which embeds the Mattermost plugin and adds the configuration:

```go
type BotWebhookPlugin struct {
	plugin.MattermostPlugin
	configuration *Configuration
}
```

To react to messages we'll implement the `MessageHasBeenPosted` function. For easier reading I've removed the error handling from the code snippets.

```go
func (p *BotWebhookPlugin) MessageHasBeenPosted(post *model.Post) {
  // get the channel from the post
  // we need this to check if the message was sent to the bot
	channel, err := p.API.GetChannel(post.ChannelId)

  // if the post was by the bot ignore it
	if post.UserId == p.configuration.BotUserID {
		return
	}

  // check if the message was sent to the bot
  // the channel name in a direct channel looks like this:
  // <bot username>__<user id>
	if strings.Contains(channel.Name, p.configuration.BotUserID) {
    // convert the post to JSON
		jsonPayload, err := json.Marshal(post)

    // send a POST request to the webhook URL with the post as the body
		req, err := http.NewRequest("POST", p.configuration.WebhookURL, bytes.NewBuffer(jsonPayload))
		req.Header.Set("Content-Type", "application/json")
		client := &http.Client{}
		resp, err := client.Do(req)

		defer resp.Body.Close()

		return
	}
}
```

That's all there is to it. Every time someone messages the bot the plugin will send a webhook to the configured URL.
