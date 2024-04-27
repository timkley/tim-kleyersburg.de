---
date: 2022-08-31
title: 'Create a Mattermost bot that can message specific users'
hero: 'hero-image.jpg'
metaDescription: 'Using the Mattermost API this bot will be able to send messages to any user'
tags:
    - api
    - automation
    - mattermost
---

You can use a Mattermost bot and the Mattermost API to automate writing messages to your users. I couldn't find any tutorials how to make it seem like the bot messaged you or one of your users. If you want to achieve exactly that, this article is for you.

## Create a bot integration

As an admin, select the grid icon in the top left of your Mattermost instance and select "Integrations".

<div class="max-w-sm mx-auto">
    {% image 'integrations.png', 'The grid icon dropdown in a Mattermost app' %}
</div>

---

You will have a couple of options to choose from:

{% image 'integrations-bot-account.png', 'The integrations menu of Mattermost' %}

You'll want to select "Bot Accounts" and add a new bot account by clicking on "Add Bot Account". Next, you will have to fill out a form. If you need more information about each of these fields, or bot accounts in general, I highly recommend the [official documentation](https://docs.mattermost.com/integrations/cloud-bot-accounts.html).

{% image 'mattermost-bot-form.png', 'A form you have to fill out to create a Mattermost bot account' %}

After saving the form you will receive the bot token. Copy and save this someplace safe as you will not see this again. You can always generate a new token but will have to replace the token everywhere you have used it.

{% image 'mattermost-bot-token.png', 'The success message includes your bots token' %}

---

Now to the fun part: sending messages with your shiny new bot! We will use Mattermost's [REST API](https://api.mattermost.com/) to achieve this.

To access the API you will need the token you got in the previous step. Every request should send the following header, also known as Bearer or Token authentication: `Authorization: Bearer your-token`.  
The base url for the API at the time of writing this article is:  
`https://your-mattermost-url.com/api/v4`.

To send a message from a bot to a user you will now have to make two API requests: one that gives you the correct channel id and another one which actually sends the message.

### Getting the bot and the user id

Writing a message from a bot to a user basically means that you send a message to the private channel shared by these two entities. Every channel in Mattermost has its own unique id. And direct messages are nothing else as a private channel between two people. 

If we want to get the channel id for the combination of the bot and the user we will need their own entity ids to query the API for the correct channel id.

Getting the user id is simple: you can just open the System Console, and click on "Users" in the submenu "User Management". The user id is directly below the name of your users.

But a bot is no normal user! So the user list unfortunately doesn't show the bot account. But you can use the bot token to use the endpoint <span class="text-indigo-600 dark:text-indigo-300 font-semibold">GET</span>  `/users` to retrieve all users. You can then filter down the result to the username of your bot.  
Alternatively, if you have a lot of users, use the endpoint <span class="text-green-600 dark:text-green-300 font-semibold">POST</span>  `/users/search`.

Use this JSON payload to search for your bot:

```json
{
	"term": "name-of-your-bot"
}
```

Great! Now that you have the id of your bot and your user we can move on to the next step.

### Getting the direct channel id

Use the endpoint <span class="text-green-600 dark:text-green-300 font-semibold">POST</span> `/channels/direct` to get the correct channel id to send our message to.

Using the obtained bot and user id we can now send the following JSON payload to the named endpoint.

```json
[
	"bot-id",
	"user-id"
]
```

**Response**



```json
{
  "id": "this-is-the-channel-id",
  "name": "first-user-id__second-user-id",
  ...
}
```

Note down this id, that is the unique identifier of the private channel between the bot and the specified user.

### Sending a message

Now we have all we need to finally send a message.

Lets now use the endpoint <span class="text-green-600 dark:text-green-300 font-semibold">POST</span> `/posts` to create a new message.

The minimal payload for this looks like this:

```json
{
	"channel_id": "this-is-the-channel-id",
	"message": "Very thoughtful message."
}
```

If everything worked you will get a status `201 Created` back.

### Real life example

At my work we are using such an integration to make users aware of problems with their project planning. There is a cronjob running every 15 minutes which checks the tasks of every user. If it detects new problems we can use the associated mail address to find the user, find the private channel of the bot and the user, and send them some meaningful message and a link to the planning tool to show what actual problems occurred.