---
date: 2022-07-11
title: Simple and GDPR compliant website analytics with Umami
hero: hero-image.jpg
excerpt: Learn how to setup Umami and Traefik with some simple docker-compose scripts
tags: [analytics, docker]
image: /articles/img/ogimages/simple-analytics-with-umami.webp
---

When I first started writing my blog I used [getinsights.io](https://getinsights.io/) for analytics to see how much traffic my site got. Seeing the first visitors finding articles on search engines was very motivating for me.

Using Google Analytics was never an option for me. I don't need half of its features and I didn't want to jump through the GDPR hoops.

What I wasn't happy about when using Insights was the interface and that it still used Google services (Firebase) under the hood. It was also hard to find where the company is seated which is a small red flag for me.  
At some point I discovered [Umami](https://umami.is/). Umami is a self-hosted, privacy-focused analytics tool which I wanted to try for some time now.

I'll describe the steps I took to setup Umami on a Digital Ocean droplet, using [Traefik](https://doc.traefik.io/traefik/) as a proxy, providing very easy routing and HTTPS.

## First things first: Setting up a VPS

I'll use [DigitalOcean](https://digitalocean.com) to create a small droplet. To make my life easier I'll use the Docker preset with the following settings:

![Configuration of the Digital ocean droplet](digitalocean-droplet.png)

After a few minutes our droplet is ready and we can connect to it via SSH.

## Defining the setup

Here's a list of things I've wanted, so I could later reuse this droplet for other small docker services:

-   Simple way of adding new services later on
-   No hassle with HTTPS certificate creation or renewal
-   Separation of services, if possible no duplication of "management" services (like Traefik)

To achieve this I used two docker-compose files in this simple setup:

-   One for the Traefik reverse proxy
-   Another one for Umami itself

## The code

### Directory structure

After logging into the VPS I first created two directories. One for Traefik and one for Umami:

```
├── traefik
└── umami
```

### Setting up Traefik

Next, we'll start by setting up Traefik. For this, I followed [this tutorial](https://www.digitalocean.com/community/tutorials/how-to-use-traefik-v2-as-a-reverse-proxy-for-docker-containers-on-ubuntu-20-04) from DigitalOcean to get started. I'd recommend you first read the things I've done differently before following that tutorial so you can decide for yourself which way you like better.

Instead of using a long docker command I've chosen to use a docker-compose file instead. That way I don't have remember a long-ass command.

Here is the content of that file:

```yaml
version: '3'
services:
    reverse-proxy:
        image: traefik:v2.8
        ports:
            # The HTTP port
            - '80:80'
            - '443:443'
        volumes:
            # So that Traefik can listen to the Docker events
            - /var/run/docker.sock:/var/run/docker.sock:ro
            - ./acme.json:/etc/traefik/acme.json
            - ./traefik.toml:/etc/traefik/traefik.toml:ro
            - ./traefik_dynamic.toml:/etc/traefik/traefik_dynamic.toml:ro
        networks:
            - web

networks:
    web:
        external: true
```

Note that we've defined the network `web` in here. `external: true` means that this is a network managed by Docker itself and not generated while using the `up` command provided by docker-compose. To create this network run `docker network create web` before starting the service.

You also need to bind this network to your service so that Traefik will be able to communicate in this network.

If you've followed the linked tutorial above you can now run `docker-compose up -d`. Using the `-d` flag runs your containers detached in the background.

## Setting up Umami

Now onto setting up Umami. Fortunately most of the work is already done because Umami provides a [docker-compose file](https://github.com/umami-software/umami/blob/c5d775ce721d178af6d2ab2b959d245cb0457fdb/docker-compose.yml) for us. But we will need to make a few changes so Umami can work correctly with Traefik.

Switch into the previously created `umami` folder on your VPS and create the following file, naming it `docker-compose.yml`:

```yaml
version: '3'
services:
    umami:
        image: ghcr.io/mikecao/umami:postgresql-latest
        labels:
            - 'traefik.http.routers.umami.rule=Host(`your-domain.com`)'
            - 'traefik.http.routers.umami.tls=true'
            - 'traefik.http.routers.umami.tls.certresolver=lets-encrypt'
        environment:
            DATABASE_URL: postgresql://umami:umami@db:5432/umami
            DATABASE_TYPE: postgresql
            HASH_SALT: change-this-to-a-random-string
            TRACKER_SCRIPT_NAME: protocol
        depends_on:
            - db
        restart: always
        networks:
            - backend
            - web
    db:
        image: postgres:12-alpine
        environment:
            POSTGRES_DB: umami
            POSTGRES_USER: umami
            POSTGRES_PASSWORD: umami
        volumes:
            - ./sql/schema.postgresql.sql:/docker-entrypoint-initdb.d/schema.postgresql.sql:ro
            - umami-db-data:/var/lib/postgresql/data
        restart: always
        networks:
            - backend

volumes:
    umami-db-data:

networks:
    backend:
        external: false
    web:
        external: true
```

There are a few changes here in comparison to the original file. I'll explain them in order of appearance and what you'll have to change for it to work correctly for your own setup.

### Traefik Labels

```yaml
labels:
    - 'traefik.http.routers.umami.rule=Host(`your-domain.com`)'
    - 'traefik.http.routers.umami.tls=true'
    - 'traefik.http.routers.umami.tls.certresolver=lets-encrypt'
```

These labels will be read by Traefik to configure the router for Umami dynamically when its containers are started. The `umami` part in the beginning of the string creates a new router for Umami to use.

#### `traefik.http.routers.umami.rule=Host(your-domain.com)`

This label creates a `Host` rule. This means, that if the incoming traffic matches the host `your-domain.com` it will be forwarded to Umami. Please change this to the domain you plan to use and make sure you've added an A-record which is pointing to the IP address of your VPS.

#### `traefik.http.routers.umami.tls=true`

This label specifies that we want TLS activated.

#### `traefik.http.routers.umami.tls.certresolver=lets-encrypt`

And this label tells Traefik to resolve the certificate with Let's Encrypt.

### Environment values

```yaml
environment:
    HASH_SALT: change-this-to-a-random-string
    TRACKER_SCRIPT_NAME: protocol
```

`DATABASE_URL` and `DATABASE_TYPE` haven't changed because the defaults are fine in this scenario.

`HASH_SALT` is used to generate unique values, replace this with a random string.

`TRACKER_SCRIPT_NAME` can be used to rename the tracker script. By default it is called `umami.js` which unfortunately is blocked by default by some adblockers or the privacy-focused browser [Brave](https://brave.com). I renamed mine to `protocol` which seems to work fine.

### Networks

This step is very important for everything to work correctly. If you've attempted setting up Traefik before and ran in 502 Bad Gateway or 504 Bad Gateway errors, this should help you.

Lets start with the root level network definitions:

```yaml
networks:
    backend:
        external: false
    web:
        external: true
```

Instead of just one network `web`, which we need so Traefik can route the web traffic to the Node server, we also have need a second network.

Normally docker-compose creates a default network for our services to communicate with each other. But since we defined the networks ourselves, this default network is not created. This unfortunately means that the Node server now has no way to communicate with the database service.

That's why we defined `backend` as a second network. You can see that we set this one explicitly to _not_ be external. This is the default but when I come back to this configuration in a few weeks or months I don't have to guess the value.

In addition to defining these networks we also have to configure which services use these networks. In our case, we have two services: `umami` is the service that is running the Node server and therefore responsible for serving the dashboard and tracking script.  
And `db` runs the database server.

`umami` needs to join the `web` and `backend` network. `db` only needs `backend`.

Our network diagram now looks like this:

```yaml
traefik:
    reverse-proxy:
        networks:
            - web

umami:
    umami:
        networks:
            - web
            - backend
    db:
        networks:
            - backend
```

This ensures that Traefik can route traffic to the Umami frontend and also, that Umami can communicate internally with its database.

Since the `backend` network is an internal network this also ensures that the database service is not reachable publicly which is good for security.

## All together now!

You can now run `docker-compose up -d` from the directory the docker-compose file for Traefik is located as well as for Umami. After a few seconds you should be able to visit the Umami dashboard on the domain you've connected.

Once again many thanks to [my brother](https://github.com/pitkley) for explaining new things to me ❤️.
