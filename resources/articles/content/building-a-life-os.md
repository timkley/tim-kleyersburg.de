---
date: 2025-01-09
draft: true
title: Building A Life OS
excerpt: A Life OS is a system that helps you manage your life. Learn how I used Laravel to built a system that helps me manage my life.
image: /articles/img/ogimages/building-a-life-os.webp
---

- how I define Life OS
- motiviation behind building a Life OS
- how I built it
- what I learned
- next steps

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

## What I built

I've written about how I used [EleventyJS](/articles/eleventyjs-is-great) to rebuild my site as a static site and integrate a blog. While that worked great at first, I've found myself wanting more and more features that are not possible with a static site. Additionally, after the honeymoon phase I didn't enjoy using JavaScript for everything as much as I thought I would.

So I decided to go back to my roots and use Laravel. I've been using Laravel for years. It's a great framework that allows me to build things quickly and efficiently.

So I created a new Laravel project and started building my Life OS.

What tipped me over to finally do it was the release of [Prezet](https://prezet.com). It's a package that allows for easy markdown blogging in Laravel. Since all the articles I've written before are in markdown it was a great fit and made the switch even easier.


