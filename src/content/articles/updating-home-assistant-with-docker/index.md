---
date: 2022-06-06
title: 'How to update your docker-containers running Home Assistant'
hero: 'hero-image.jpg'
metaDescription: 'Updating Home Assistant with docker-compose is quick and easy'
tags:
    - smarthome
    - quicktip
---
{% from 'macros.njk' import alert %}

From time to time you'll want to update your Home Assistant instance you've [previously set up](/articles/home-assistant-with-docker-2022/) to the latest version.

Using docker compose this is really simple, you just have to run two simple commands and need a little patience. If you've used [my guide](/articles/home-assistant-with-docker-2022/) to run Home Assistant this works with nearly no downtime.

## How to update your containers

SSH into the server running your Home Assistant instance and navigate to the folder where you've saved the `docker-compose.yml` file to.

### Updating the docker images

Run the following command:

```sh
docker-compose pull
```

This will pull the latest images used in your `docker-compose.yml` file.  
While you wait, why dont't you read [the official documentation](https://docs.docker.com/compose/reference/pull/)? 🙂

{% set content %}
This will _not_ interrupt the running containers just yet, so your Home Assistant instance is still available through this process.
{% endset %}

{{ alert(content) }}

### Recreate the Home Assistant instance

When this process is finished you can now recreate the containers running the same command as for the first time you started your containers: 

```sh
docker-compose up -d
```

This will recreate the containers with the newest images that got pulled before.

Now revisit your Home Assistant dashboard and enjoy the newest version 😎