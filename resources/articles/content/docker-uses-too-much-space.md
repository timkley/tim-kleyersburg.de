---
date: 2023-01-06
title: Clear up space used by Docker with a built-in command
hero: hero-image.jpg
excerpt: Keep your Docker host running smoothly and efficiently removing unnecessary data and free up space.
tags: [docker, quicktip]
image: /articles/img/ogimages/docker-uses-too-much-space.webp
---

## Introduction

If you've been using Docker for a while, you may have noticed that it can quickly use up a lot of disk space. Especially after testing out a few images, updating existing ones you may find your host slowly but surely filling up.

In this quick tip, I'll show you how to use the built-in command `docker system prune` to clear up space used by Docker.

## What does `docker system prune` do?

To quote the [official documentation](https://docs.docker.com/engine/reference/commandline/system_prune/): this command "removes unused data".

In practice, this means that it will remove:

- all stopped containers
- all networks not used by at least one container
- all dangling images
- all build cache

I've found, that especially dangling images can use up a lot of space. If you are using my [quicktip for updating your Home Assistant installation](/articles/updating-home-assistant-with-docker) this can quickly use up a lot of space on your SD card.

### Available options

`--all, -a` - Remove all unused images not just dangling ones

`--force, -f` - Do not prompt for confirmation

`--volumes` - Prune volumes

```html +parse
<x-alert>
    Be careful when using the `--volumes` option. This will remove all volumes that are not used by any containers and could potentially delete data you need!
</x-alert>
```

## How to use it

To use the command, simply run it in your terminal with your wanted options. For example, to remove all unused images and dangling containers, you would run:

```bash
docker system prune --all
```

## Conclusion

Using the built-in command `docker system prune` is a quick and easy way to clear up space used by Docker. It's a good idea to run it every once in a while to keep your host running smoothly and efficiently. On my Raspberry Pi I run it after updating my existing containers.
