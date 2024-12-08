---
date: 2024-12-08
title: Setup Firecrawl with Docker and Traefik
excerpt: Learn how to set up Firecrawl with Docker and Traefik, including a simple header authentication.
tags: [docker]
image: /articles/img/ogimages/setup-firecrawl-with-docker-and-traefik.webp
---

## What is Firecrawl?

> Turn websites into LLM-ready data  
Power your AI apps with clean data crawled from any website. It's also open-source.

[Source](https://www.firecrawl.dev/)

It's a project that enables you to scrape and crawl websites and get well-formatted data. It also handles JavaScript rendering, which is a common issue when scraping websites. And the best part: it's open source and you can host it yourself.

## Installing Firecrawl with Docker

Firecrawl provides everything you need to get started quickly. You may use the `docker-compose.yaml` file provided in their [GitHub repository](https://github.com/mendableai/firecrawl/blob/d316d52c963eeb2f05e7624204e969fb73f43e9b/docker-compose.yaml).

Make sure to clone the whole repository as the `docker-compose.yaml` file references other files in the repository.

Copy the `.env.example` file from the directory `apps/api` into the same directory where you have the `docker-compose.yaml` file and rename it to `.env`. If you don't have reasons to change anything the default values work fine.

## Integrating with Traefik

If, like me, you are using [Traefik](https://traefik.io/) as a reverse proxy, use the following example configuration to configure Traefik with labels.

This assumes you are using an external network called `web` to route all your traffic through Traefik. 

I've used the [headerauthentication](https://github.com/omar-shrbajy-arive/headerauthentication) plugin by [omar-shrbajy-arive](https://github.com/omar-shrbajy-arive). This plugin allows you to define a specific header key and value that must be present in the request to access Firecrawl. You can also use it without authentication, but I prefer and recommend not to leave a web scraper open to the internet for anyone to use. 

To use this plugin, add the following configuration to your Traefik static configuration file (`traefik.toml`).

```toml
[experimental.plugins.headerauthentication]
  moduleName = "github.com/omar-shrbajy-arive/headerauthentication"
  version = "v1.0.3"
```

Now you can use the following `docker-compose.yaml` file to set up Firecrawl with Traefik, it is the exact same as the one provided by Firecrawl, but with the Traefik labels added.

```yaml
name: firecrawl

x-common-service: &common-service
  build: apps/api
  networks:
    - backend
  extra_hosts:
    - "host.docker.internal:host-gateway"

services:
  playwright-service:
    build: apps/playwright-service
    environment:
      - PORT=3000
      - PROXY_SERVER=${PROXY_SERVER}
      - PROXY_USERNAME=${PROXY_USERNAME}
      - PROXY_PASSWORD=${PROXY_PASSWORD}
      - BLOCK_MEDIA=${BLOCK_MEDIA}
    networks:
      - backend

  api:
    <<: *common-service
    labels:
      - "traefik.http.middlewares.firecrawl.plugin.headerauthentication.header.name=Authorization"
      - "traefik.http.middlewares.firecrawl.plugin.headerauthentication.header.key=Bearer ${BEARER_TOKEN}"
      - "traefik.http.routers.firecrawl.rule=Host(`firecrawl.your-domain.com`)"
      - "traefik.http.routers.firecrawl.tls=true"
      - "traefik.http.routers.firecrawl.tls.certresolver=lets-encrypt"
      - "traefik.http.routers.firecrawl.middlewares=firecrawl"
    environment:
      REDIS_URL: ${REDIS_URL:-redis://redis:6379}
      REDIS_RATE_LIMIT_URL: ${REDIS_URL:-redis://redis:6379}
      PLAYWRIGHT_MICROSERVICE_URL: ${PLAYWRIGHT_MICROSERVICE_URL:-http://playwright-service:3000}
      USE_DB_AUTHENTICATION: ${USE_DB_AUTHENTICATION}
      PORT: ${PORT:-3002}
      NUM_WORKERS_PER_QUEUE: ${NUM_WORKERS_PER_QUEUE}
      OPENAI_API_KEY: ${OPENAI_API_KEY}
      OPENAI_BASE_URL: ${OPENAI_BASE_URL}
      MODEL_NAME: ${MODEL_NAME:-gpt-4o}
      SLACK_WEBHOOK_URL: ${SLACK_WEBHOOK_URL}
      LLAMAPARSE_API_KEY: ${LLAMAPARSE_API_KEY}
      LOGTAIL_KEY: ${LOGTAIL_KEY}
      BULL_AUTH_KEY: ${BULL_AUTH_KEY}
      TEST_API_KEY: ${TEST_API_KEY}
      POSTHOG_API_KEY: ${POSTHOG_API_KEY}
      POSTHOG_HOST: ${POSTHOG_HOST}
      SUPABASE_ANON_TOKEN: ${SUPABASE_ANON_TOKEN}
      SUPABASE_URL: ${SUPABASE_URL}
      SUPABASE_SERVICE_TOKEN: ${SUPABASE_SERVICE_TOKEN}
      SCRAPING_BEE_API_KEY: ${SCRAPING_BEE_API_KEY}
      HOST: ${HOST:-0.0.0.0}
      SELF_HOSTED_WEBHOOK_URL: ${SELF_HOSTED_WEBHOOK_URL}
      LOGGING_LEVEL: ${LOGGING_LEVEL}
      FLY_PROCESS_GROUP: app
    depends_on:
      - redis
      - playwright-service
    ports:
      - "3002:3002"
    command: [ "pnpm", "run", "start:production" ]
    networks:
      - web

  worker:
    <<: *common-service
    environment:
      REDIS_URL: ${REDIS_URL:-redis://redis:6379}
      REDIS_RATE_LIMIT_URL: ${REDIS_URL:-redis://redis:6379}
      PLAYWRIGHT_MICROSERVICE_URL: ${PLAYWRIGHT_MICROSERVICE_URL:-http://playwright-service:3000}
      USE_DB_AUTHENTICATION: ${USE_DB_AUTHENTICATION}
      PORT: ${PORT:-3002}
      NUM_WORKERS_PER_QUEUE: ${NUM_WORKERS_PER_QUEUE}
      OPENAI_API_KEY: ${OPENAI_API_KEY}
      OPENAI_BASE_URL: ${OPENAI_BASE_URL}
      MODEL_NAME: ${MODEL_NAME:-gpt-4o}
      SLACK_WEBHOOK_URL: ${SLACK_WEBHOOK_URL}
      LLAMAPARSE_API_KEY: ${LLAMAPARSE_API_KEY}
      LOGTAIL_KEY: ${LOGTAIL_KEY}
      BULL_AUTH_KEY: ${BULL_AUTH_KEY}
      TEST_API_KEY: ${TEST_API_KEY}
      POSTHOG_API_KEY: ${POSTHOG_API_KEY}
      POSTHOG_HOST: ${POSTHOG_HOST}
      SUPABASE_ANON_TOKEN: ${SUPABASE_ANON_TOKEN}
      SUPABASE_URL: ${SUPABASE_URL}
      SUPABASE_SERVICE_TOKEN: ${SUPABASE_SERVICE_TOKEN}
      SCRAPING_BEE_API_KEY: ${SCRAPING_BEE_API_KEY}
      HOST: ${HOST:-0.0.0.0}
      SELF_HOSTED_WEBHOOK_URL: ${SELF_HOSTED_WEBHOOK_URL}
      LOGGING_LEVEL: ${LOGGING_LEVEL}
      FLY_PROCESS_GROUP: worker
    depends_on:
      - redis
      - playwright-service
      - api
    command: [ "pnpm", "run", "workers" ]

  redis:
    image: redis:alpine
    networks:
      - backend
    command: redis-server --bind 0.0.0.0

networks:
  backend:
    driver: bridge
  web:
    external: true
```

Please create an `.env` file which holds the value of the authorization bearer. For example:

```bash
BEARER_TOKEN=your-token
```

## Conclusion

You should now have a fully functional Firecrawl instance running on your server. Make sure to point your domain to the server.
