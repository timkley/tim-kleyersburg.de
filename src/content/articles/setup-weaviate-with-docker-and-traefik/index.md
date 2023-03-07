---
date: 2023-03-07
title: 'Setup Weaviate with Docker and Traefik'
hero: 'hero-image.jpg'
metaDescription: 'Learn how to set up Weaviate with Docker and Traefik, including a simple header authentication.'
tags:
    - docker
    - ai
---

{% from 'macros.njk' import alert %}

## What is Weaviate?

> Weaviate is an open-source vector search engine.
It allows you to store data objects and vector embeddings from your favorite ML-models, and scale seamlessly into billions of data objects.

[Source](https://weaviate.io/)

In other words, it's a search engine for machine learning models. It's a helpful tool for building your own AI applications. You can use it to store and search for data objects using vector embeddings. It also integrates directly with OpenAI, allowing you to use their embedding models directly upon importing text.  
This enables you to do very easy near-text-searches because the query is also automatically embedded.

## Installing Weaviate with Docker

To install Weaviate with Docker, you can use the official [Docker Compose Configurator](https://weaviate.io/developers/weaviate/installation/docker-compose). This will provide you with a `docker-compose.yml` file that if perfectly pre-configured to your liking.

Just remember to set a persistent volume if you don't want to lose your data when you restart the container.

## Integrating with Traefik

If, like me, you are using [Traefik](https://traefik.io/) as a reverse proxy, you can use the following `docker-compose.yml` file to set up Weaviate with Traefik.

This assumes you are using an external network called `web` to route all your traffic through Traefik. The rest of the file was created using the above-mentioned configurator. Depending on your preferences the `environment` section may vary.

I've used labels to configure Traefik for this container.

```yaml
---
version: '3.4'
services:
  weaviate:
    image: semitechnologies/weaviate:1.17.4
    ports:
    - 8080:8080
    restart: on-failure:0
    volumes:
      - /var/weaviate:/var/lib/weaviate
    labels:
      - "traefik.http.routers.weaviate.rule=Host(`weaviate.your-host.com`)"
      - "traefik.http.routers.weaviate.tls=true"
      - "traefik.http.routers.weaviate.tls.certresolver=lets-encrypt"
    environment:
      OPENAI_APIKEY: $OPENAI_APIKEY
      QUERY_DEFAULTS_LIMIT: 25
      AUTHENTICATION_ANONYMOUS_ACCESS_ENABLED: 'true'
      PERSISTENCE_DATA_PATH: '/var/lib/weaviate'
      DEFAULT_VECTORIZER_MODULE: 'text2vec-openai'
      ENABLE_MODULES: 'text2vec-openai,generative-openai'
      CLUSTER_HOSTNAME: 'node1'
    networks:
      - web

networks:
  web:
    external: true
```

### Adding a simple header authentication

I didn't want to expose Weaviate to the internet without some kind of authentication. While Weaviate supports [OIDC authentication](https://weaviate.io/developers/weaviate/configuration/authentication), this seemed overkill for my use case. I just wanted to add a simple header authentication to prevent unauthorized access.

To achieve this, I've used the plugin [headerauthentication](https://github.com/omar-shrbajy-arive/headerauthentication) by [omar-shrbajy-arive](https://github.com/omar-shrbajy-arive). This plugin allows you to define a specific header key and value that must be present in the request to access Weaviate.

To use this plugin, you need to add the following to your Traefik static configuration file, assuming you are using the `toml` format:

```toml
[experimental.plugins.headerauthentication]
  moduleName = "github.com/omar-shrbajy-arive/headerauthentication"
  version = "v1.0.3"
```

Then you can add the following to your `docker-compose.yml` file:

```yaml
---
version: '3.4'
services:
  weaviate:
    image: semitechnologies/weaviate:1.17.4
    ports:
      - 8080:8080
    restart: on-failure:0
    volumes:
      - /var/weaviate:/var/lib/weaviate
    labels:
      - "traefik.http.routers.weaviate.rule=Host(`weaviate.wacg.dev`)"
      - "traefik.http.routers.weaviate.tls=true"
      - "traefik.http.routers.weaviate.tls.certresolver=lets-encrypt"
      - "traefik.http.routers.weaviate.middlewares=weaviate@docker" # [tl! add]
      - "traefik.http.middlewares.weaviate.plugin.headerauthentication.Header.name=X-TOKEN" # [tl! add]
      - "traefik.http.middlewares.weaviate.plugin.headerauthentication.Header.key=${HEADER_TOKEN}" # [tl! add]
    environment:
      OPENAI_APIKEY: $OPENAI_APIKEY
      QUERY_DEFAULTS_LIMIT: 25
      AUTHENTICATION_ANONYMOUS_ACCESS_ENABLED: 'true'
      PERSISTENCE_DATA_PATH: '/var/lib/weaviate'
      DEFAULT_VECTORIZER_MODULE: 'text2vec-openai'
      ENABLE_MODULES: 'text2vec-openai,generative-openai'
      CLUSTER_HOSTNAME: 'node1'
    networks:
      - web

networks:
  web:
    external: true
```

This configures a new middleware called `weaviate` using the `headerauthentication` plugin. It also adds a new environment variable called `HEADER_TOKEN` that will be used to set the value of the header key.

Please create an `.env` file which holds the value of the header key. For example:

```bash
HEADER_TOKEN=your-token
```

## Conclusion

You now have a fully functional Weaviate instance running on your server. You can use it to build your own AI applications.