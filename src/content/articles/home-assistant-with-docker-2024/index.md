---
date: 2022-04-02
updated_at: 2024-06-04
title: 'Home Assistant with Docker on Raspberry Pi - the 2024 guide'
hero: 'hero-image.jpg'
metaDescription: 'An up to date guide how to set up a fresh installation of Home Assistant with Docker'
tags:
    - smarthome
    - docker
---

Update 2024-06-04: updated the guide for the "new" year.

Finally, a Raspberry Pi 4 with 8GB RAM came into stock that wasn't outrageously priced.

Quick tip: remember where you've placed your Micro SD card from your last bricked Pi so you don't have to wait for the delivery of a new one.

I'm assuming you have basic knowledge of Linux (for example, how to connect to another computer with SSH), so forgive me if I don't explain everything down to the last detail. Don't hesitate to message me on X if you have any queries 🙂.

## The guide

We'll follow these steps to get a working installation of Home Assistant running on your local network:

1. Install Raspberry Pi OS with Raspberry Pi Imager.
2. Connect to and update your Raspberry Pi.
3. Install Docker.
4. Run Home Assistant as a Docker container.

### 1. Install Raspberry Pi OS with Raspberry Pi Imager

While I'm not typically a fan of installers, preferring to understand what's happening during each step, I highly recommend using the Imager tool for installing Raspberry Pi OS on your SD card in this instance. It saves time and prevents headaches during the initial configuration.

[Download Raspberry Pi Imager](https://www.raspberrypi.com/software/) for your operating system.

{% image 'raspberry-pi-imager-home-screen.jpg', 'Screenshot of the home screen of Raspberry Pi Imager' %}
_Screenshot of the home screen of Raspberry Pi Imager_

1. Select the OS of your choice; I opted for Raspberry Pi OS Lite 64 Bit (important as the Docker images we'll be using don't work on 32-bit systems).
2. If you haven't already, insert your SD card into your computer and select it here.
3. Click on the small cog in the bottom right corner to configure the installation with the following settings:

{% image 'raspberry-pi-imager-settings.jpg', 'Screenshot of the advanced options of Raspberry Pi Imager' %}
_Screenshot of the advanced options of Raspberry Pi Imager_

I customised a few things:

-   Updated hostname to rpi for brevity.
-   Enabled SSH and set an authorised key (if you already have a default key present, this will be filled in automatically).
-   Configured wireless LAN with my network details. I plan to hardwire the Pi eventually, but avoiding cables made setup easier.
-   Set locale settings to match my timezone and preferred keyboard layout.

Remember to press **Save** before proceeding. You are now ready to write everything to your SD card, which may take a few minutes depending on its speed.

Once complete, insert the card into your Raspberry Pi and continue.

### 2. Connecting and updating your Raspberry Pi

After inserting the SD card, power up your Pi by connecting the power supply. Allow it 1 or 2 minutes to fully boot.

You can then connect using your preferred terminal:

```sh
ssh pi@rpi.local
```

Change the username (`pi`) and hostname (`rpi`) according to what you selected in the options dialogue. Alternatively, use the IP address assigned by your router.

To update packages and upgrade your Pi to the latest version, run:

```sh
sudo apt update && sudo apt full-upgrade
```

[Official source](https://www.raspberrypi.com/documentation/computers/os.html#updating-and-upgrading-raspberry-pi-os)

Important: Ensure you reboot before continuing. This isn't optional; skipping this step will likely result in Docker installation failure.

```sh
sudo reboot
```

Your Pi will reboot, ready for the next step.

### 3. Installing Docker

We'll use Docker's straightforward install script. The following command is all that's needed:

```sh
curl -sSL https://get.docker.com | sh
```

By default, non-root users don't have rights to run containers, so we'll add our current user (`pi`) to the `docker` user group, eliminating the need for `sudo` every time we want to run a container.

```sh
sudo usermod -aG docker ${USER}
```

Run `groups ${USER}` to verify that this has worked. You should see the user group you just added at the end of the line.

I highly recommend enabling the Docker system service. This ensures Docker automatically starts whenever you reboot your system and will also start containers configured with a [restart-policy](https://docs.docker.com/compose/compose-file/#restart) of `always` or `unless-stopped.` We'll configure our Home Assistant container in this way.

Enable the Docker system service by running:

```sh
sudo systemctl enable docker
```

### 4. Running Home Assistant as Docker container

Finally, we're at the stage of running Home Assistant in a Docker container accessible from your local network.

Create a file called `docker-compose.yml` in a folder called `docker/homeAssistant` within your home folder, resulting in this structure:

```
pi@rpi:~ $ tree -L 3
.
└── docker
    └── homeAssistant
        └── docker-compose.yml

2 directories, 1 file
```

Input the following into the `docker-compose.yml` file. You'll likely need to change the timezone to match yours. If you've used a different directory structure, adjust your volumes configuration accordingly.

```yaml
---
version: '2.1'
services:
    homeassistant:
        image: lscr.io/linuxserver/homeassistant
        container_name: homeassistant
        network_mode: host
        environment:
            - PUID=1000
            - PGID=1000
            - TZ=Europe/Berlin
        volumes:
            - /home/pi/docker/homeAssistant/data:/config
        restart: unless-stopped
```

The last option, `restart: unless-stopped`, ensures our container reboots after restarting your Pi unless you've manually stopped it.

In theory, you're now ready to start your container and begin configuring Home Assistant.

From the directory `/home/pi/docker/homeAssistant`, run:

```sh
sudo docker compose up -d
```

The `-d` flag runs the container in `detached` mode in the background.

If you see no errors appear, congratulations! Access Home Assistant's management backend by navigating to `http://rpi.local:8123` (Adjust URL according to your hostname if different). You can now start the onboarding process; for this, I highly recommend referring to the [official documentation](https://www.home-assistant.io/getting-started/onboarding).
