---
date: 2025-02-07
title: Building A Life OS
excerpt: A Life OS is a system that helps you manage your life. Learn how I used Laravel to built a system that helps me manage my life.
image: /articles/img/ogimages/building-a-life-os.webp
tags: [life-os]
---


## My personal definition of a Life OS

The term "Life OS" is often associated with a note taking system helping you manage your life. It does so by giving you structures to organize your thoughts, tasks, and goals.

For me, that's not enough. An Operating System should be able to _manage_ your life, not just _help you manage_ it. It should be able to automate tasks, remind you of important things and be truly made for you.

In my opinion a note taking system falls short of that. It's a great tool to help you manage your thoughts, but it's not a Life OS.

## Motivation behind building a Life OS

For multiple months I've juggled with the thought of creating a system that is able to combine all the different digital sources that are part of my life.

The tipping point for me was when my kid got into a new school and got a digital school schedule, namely [Webuntis](https://webuntis.com/). It contains information about the classes, homework and upcoming tests. The concept is great but the UX is, sorry Untis-Team, terrible.

- There are no notifications
- You don't see all homeworks or tests at a glance but have to check _each day individually_
- abbreviations are used that are not explained anywhere

But: they have an API*. So I thought: why not build a system that combines all the different sources of information that are part of my life? I love working with APIs or with data from different sources that do things.

So I started building a Life OS.

_* The API sucks, also. But that's a different story._

## What's inside

I'm using the [TALL stack](https://tallstack.dev/), which stands for Tailwind CSS, Alpine.js, Laravel and Livewire. I've been using the stack professionally for some time now, so I'm very comfortable with it.

For content management and blogging in markdown I'm using [Prezet](https://prezet.com). It's a package that allows for easy markdown blogging in Laravel. Since all the articles I've written before are in markdown it was a great fit and made the switch even easier.

### Features

To this day I've implemented the following features:

#### Webuntis Integration

- it uses the API to fetch all the needed data like the classes, homeworks, tests and news
- displays the data in a nice way
- sends me a Discord notification if a class was cancelled or a new test was added or new news was published

#### Vocabulary Tests

This is for my kid. And a little bit for me, too. To create a good vocabulary learning system takes a lot of time if you do it analog. So I created a system that allows me to create vocabulary tests and my kid to learn them.

- I can add all current words with their translations
- I can create tests with a certain amount of words
- The system keeps track of the score by subtracting wrong from right answers, therefore allowing us to focus on words that are harder to remember

#### Goal Tracking

Although there are so many goal tracking apps out there I was never quite satisfied with some part of them. Either the notifications where bothering me, or the UX was not good enough, or the features were too much or too little.

So I created a goal tracking system that allows me to create goals, track them and get notifications if I haven't worked on them for a certain amount of time.

I'm using LLMs for creating the messages, incorporating the weather so it feels more like a personal coach instead of a dumb notification.

Next steps are the implementation of streaks to motivate me even more.

#### Bookmarks

I'm not the most organized person when it comes to bookmarks. Most of the time I just keep some tabs open, never read them, close them and when I need them can't remember where I've seen them.

I've created a simple module to put in the URL for something I don't want to forget. I'm then using a scraper to extract all information from the page to give me a little more information what the page is about.

### The future

I'm planning to add more features to the system. I'm thinking a daily digest which uses multiple sources like my iCloud reminders, calendar events, the weather, and the news to give me a daily overview of what's happening and what I have to do. And also which goals I've missed yesterday and motivate me to achieve them today.
