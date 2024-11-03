---
date: 2024-04-25
title: Set up Typesense on Ubuntu 22.04 including SSL
hero: image.jpg
excerpt: Learn how to easily setup Typesense including SSL certificates on a cheap VPS
image: /articles/img/ogimages/setting-up-typesense-on-a-vps.webp
---

Typesense is a fast, typo-tolerant search engine that is easy to set up and use. In this tutorial, we will set up Typesense on a VPS and configure it to use SSL certificates. We will use a cheap VPS from Hetzner, but you can use any VPS provider that you prefer.

## Preqrequisites

Before we start, you will need the following:

-   A VPS with Ubuntu 22.04 with at least 1GB of RAM and 1 CPU core
-   A domain name that you can point to your VPS
-   A basic understanding of Linux and the command line

## Step 1: Install Typesense

After your server is spun up, SSH into it and run the following commands:

```html +parse
<x-alert>
    Make sure to check the [official documentation](https://typesense.org/docs/guide/install-typesense.html#linux-binary) for the latest version of Typesense.
</x-alert>
```

```bash
# Update the package list and the packages
apt update && apt upgrade -y

# Install Typesense
# x64
curl -O https://dl.typesense.org/releases/26.0/typesense-server-26.0-linux-amd64.tar.gz
tar -xzf typesense-server-26.0-linux-amd64.tar.gz

# arm64
curl -O https://dl.typesense.org/releases/26.0/typesense-server-26.0-linux-arm64.tar.gz
tar -xzf typesense-server-26.0-linux-arm64.tar.gz
```

## Step 2: Set up SSL

Before proceeding, make sure that your domain is pointing to your VPS.

To set up SSL, we will use Certbot to generate SSL certificates for our domain. Run the following commands:

```bash
apt install snapd
snap install core && snap refresh core
snap install --classic certbot
certbot certonly --standalone -d your.domain.example.com
```

This will also set up auto renewal of your certificates. To make sure that Typesense will use the new certificates you will need to restart the service when the certificates have been renewed.

With your preferred text editor, open the Certbot renewal configuration file `/etc/letsencrypt/renewal/your.domain.example.com.conf`.

Add the following line to the file:

```bash
renew_hook = systemctl reload typesense-server.service
```

## Step 3: Configure Typesense

Open the Typesense configuration file `/etc/typesense/typesense-server.ini` with your preferred text editor and change the lines with the comments. All other lines can be left as they are.

```ini
[server]

api-address = 0.0.0.0
api-port = 443 # Adjust the port
api-key = api-key # Set a secure API Key
log-dir = /var/log/typesense
ssl-certificate = /etc/letsencrypt/live/your.domain.example.com/fullchain.pem # Path to the SSL certificate
ssl-certificate-key = /etc/letsencrypt/live/your.domain.example.com/privkey.pem # Path to the SSL certificate key
```

## Step 4: Adjust firewall

As a security measure, you should adjust the firewall to only allow incoming traffic on the ports you need. Run the following commands:

```bash
ufw allow 443
ufw allow 80
ufw allow 22
ufw enable
```

This ensures, that only traffic from these ports are allowed.

## Step 5: Start Typesense

You are now ready to start Typesense!

Run the command below to start Typesense:

```bash
systemctl start typesense-server.service
```

You may check if everything worked by opening the health check URL in your browser: `https://your.domain.example.com/health`.

If everything worked, you should see a JSON response like this:

```json
{
	"ok": true
}
```
