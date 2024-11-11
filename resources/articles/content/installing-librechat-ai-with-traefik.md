---
date: 2024-04-25
title: How to install Librechat AI with Docker and Traefik
excerpt: Quick tutorial on how to install Librechat AI with Docker and Traefik
tags: [ai]
image: /articles/img/ogimages/installing-librechat-ai-with-traefik.webp
---

[Librechat.ai](https://librechat.ai/) is an open source AI chat platform that you can host yourself. In this tutorial, we will install Librechat AI using Docker and Traefik.

I've had some hoops to jump through to get everything working, so I thought I'd share the steps I took to get it up and running.

This guide assumes you are using an external network called `web` to route all your traffic through Traefik.

## Step 1: Follow the official installation guide

The official installation guide can be found [here](https://docs.librechat.ai/install/installation/docker_compose_install.html#quick-start-tldr). Follow the guide to set up the required environment variables and start the Docker containers.

### Adjust the `docker-compose.override.yml` file

```yaml
version: '3.4'

services:
    api:
        labels:
            - 'traefik.enable=true'
            - 'traefik.http.routers.librechat.rule=Host(`your.domain.example.com`)'
            - 'traefik.http.routers.librechat.tls=true'
            - 'traefik.http.routers.librechat.tls.certresolver=lets-encrypt'
            - 'traefik.http.services.librechat.loadbalancer.server.port=3080'
        networks:
            - web
            - librechat_default
        volumes:
            - ./librechat.yaml:/app/librechat.yaml

networks:
    web:
        external: true
    librechat_default:
        external: true
```

### Adjust the `.env` file

To get the keys you may use the following script, place it in a file and run the file with `node file.js`:

```javascript
const crypto = require('crypto')

// Generate a 32-byte key (64 characters in hex)
const key = crypto.randomBytes(32).toString('hex')

// Generate a 16-byte IV (32 characters in hex)
const iv = crypto.randomBytes(16).toString('hex')

// Generate a 32-byte key (64 characters in hex)
const jwt = crypto.randomBytes(32).toString('hex')

// Generate a 32-byte key (64 characters in hex)
const jwt2 = crypto.randomBytes(32).toString('hex')

console.log(`CREDS_KEY=${key}`)
console.log(`CREDS_IV=${iv}`)
console.log(`JWT_SECRET=${jwt}`)
console.log(`JWT_REFRESH_SECRET=${jwt2}`)
```

After that you can adjust the `.env` file:

```bash
HOST=localhost
PORT=3080

DOMAIN_CLIENT=https://your.domain.example.com
DOMAIN_SERVER=https://your.domain.example.com

OPENAI_API_KEY=your-key
ASSISTANTS_API_KEY=your-key
APP_TITLE="a fancy title"

CREDS_KEY=f151369e25852102edfa394fd034df5ac492c2a0028acaa51260402916488c65
CREDS_IV=13272260bd313fd2d032ddcd70b75769
JWT_SECRET=4d10e5b41de2d88a819b0e4b8600d1834c25356a44323cfb1bd3a8e839688b04
JWT_REFRESH_SECRET=4f73e40aadf694f829b9980d0224cd14783d791f5744627af9d94ed71dc34943

ALLOW_REGISTRATION=true
```

## Starting the containers

After adjusting the files, you can start the containers with the following command:

```bash
docker-compose up -d
```

That should be it! You should now be able to access Librechat AI at `https://your.domain.example.com`.
