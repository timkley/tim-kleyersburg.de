---
date: 2022-04-02
title: 'Home Assistant with Docker on Raspberry Pi - the 2022 guide'
hero: 'hero-image.jpg'
metaDescription: 'An up to date guide how to set up a fresh installation of Home Assistant with Docker'
tags:
    - smarthome
    - docker
---

It finally happened: there was a Raspberry Pi 4 with 8GB RAM in stock that wasn't outrageously priced.

Hot tip: make sure you know where you've put your Micro SD card from your last bricked Pi so you don't have to wait for the delivery of a new card.

I'll assume you have basic knowledge around Linux (you should for example know how to connect to another computer with SSH), so forgive me if I don't explain everthing to the last detail. Feel free to message me on [Twitter](https://twitter.com/timkley) if you have any questions 🙂.

## The guide

These are the steps we'll have to go through to get a working installation of Home Assistant running on your local network.

1. Install Raspberry Pi OS with Raspberry Pi Imager
2. Connecting and updating your Raspberry Pi
3. Installing Docker and docker-compose
4. Running Home Assistant as Docker container

### 1. Install Raspberry Pi OS with Raspberry Pi Imager

Normally I'm not a big fan of installers. I like to understand what I'm doing and what's happening if I click something in a GUI. Most of the time you have a lot more control using the manual way.

In this case though I highly recommend using the Imager tool to install Raspberry Pi OS on your SD card. It saves you a lot of time and headaches with the first time configuration.

Go ahead and [download Raspberry Pi Imager](https://www.raspberrypi.com/software/) for your operating system.

{% image 'raspberry-pi-imager-home-screen.jpg', 'Screenshot of the home screen of Raspberry Pi Imager' %}
_Screenshot of the home screen of Raspberry Pi Imager_

1. Select the OS of your choice, I went with Raspberry Pi OS Lite 32 Bit. (I didn't test the next step with other Linux distros)
2. If you haven't already, insert the SD card in your computer and select it here.
3. Click on the little cog in the bottom right corner to configure the installation with the following settings:

{% image 'raspberry-pi-imager-settings.jpg', 'Screenshot of the advanced options of Raspberry Pi Imager' %}
_Screenshot of the advanced options of Raspberry Pi Imager_

I customized the following things:

-   updated hostname to `rpi` because it's shorter
-   enabled SSH and set an authorized key (if you already have a default key present this will be filled in automatically)
-   configured wireless LAN with my network details. I'm planning to wire the Pi up at some point, but not having to fumble around with cables made the setup easier for me
-   set the locale settings to my timezone and my preferred keyboard layout

Now, don't forget to press **Save**. You are now ready to write everything to your SD card. Depending on the speed of your card this may take a few minutes.

After this process finished insert the card in your Raspberry Pi and move on.

### 2. Connecting and updating your Raspberry Pi

After inserting the SD card turn on your Pi by attaching the power supply to it and give it 1 or 2 minutes to fully boot.

Now you can connect to it using your favorite terminal:

```sh
ssh pi@rpi.local
```

Change the username (`pi`) and the hostname (`rpi`) to what you have chosen in the options dialog. You can also use the IP address of the Pi. Take a look in the device list of your router to find out what IP was assigned to it.

To update the packages and your Pi to the latest version run the following command:

```sh
sudo apt update && sudo apt full-upgrade
```

[Official source](https://www.raspberrypi.com/documentation/computers/os.html#updating-and-upgrading-raspberry-pi-os)

**Important:** make sure to reboot before advancing to the next step. Unlike some experiences you've made a few years ago when installing device drivers, this isn't optional. If you skip it the installation of Docker will most likely fail.

```sh
sudo reboot
```

This will reboot your Pi and you are ready for the next step.

### 3. Installing Docker and docker-compose

#### 3.1 Installing Docker

Before installing `docker-compose` we'll use the simple install script from Docker itself to install Docker on our system. The following command is all that is needed to correctly install Docker on your Pi:

```sh
curl -sSL https://get.docker.com | sh
```

Because non-root user by default have no rights to run containers we'll add our current user (`pi`) to the `docker` user group so we don't need to use sudo every time we want to run a container.

```sh
sudo usermod -aG docker ${USER}
```

Run `groups ${USER}` to check if this has worked as expected. At the end of the line you should see the user group you just added.

#### 3.2 Installing `docker-compose`

The [official docs](https://docs.docker.com/compose/install/) explain how you can manually download the binary to get `docker-compose` installed on your linux machine.

But: `docker-compose` is also availabe as a Python package. I've found the installation with `pip3` much easier, but feel free to follow the official guide.

The first command installs the required dependencies for `python3` and `pip3`, the second installs `python3` and `pip3` itself.

```sh
sudo apt install libffi-dev libssl-dev python3-dev
sudo apt install python3 python3-pip
```

Your system is now ready to install `docker-compose` with `pip3`:

```sh
sudo pip3 install docker-compose
```

#### 3.3 Start containers on boot

I highly recommend to enable the Docker system service. This makes sure Docker is automatically started whenever you reboot your system.  
It will also start up containers that have a [restart-policy](https://docs.docker.com/compose/compose-file/#restart) of `always` or `unless-stopped.` We'll configure our Home Assistant container like this.

Run the following command to enable the Docker system service:

```sh
sudo systemctl enable docker
```

### 4. Running Home Assistant as Docker container

Now to the final part, running Home Assistant in a Docker container which you can than access from your local network.

Let's create a file called `docker-compose.yml` in a folder called `docker/homeAssistant` placed in your home folder, so our structure will look like this:

```
pi@rpi:~ $ tree -L 3
.
└── docker
    └── homeAssistant
        └── docker-compose.yml

2 directories, 1 file
```

Put the following contents in the `docker-compose.yml` file. You'll probably need to change the timezone to match yours. If you've used another directory structure make sure to also change your volumes configuration, too.

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

The last option `restart: unless-stopped` makes sure to boot our container back up after rebooting your Pi unless you have manuallly stopped the container.

Now, in theory, you should be ready to start up your container and begin configuring Home Assistant.

From the directory `/home/pi/docker/homeAssistant` run:

```sh
sudo docker-compose up -d
```

The `-d` flag runs the container in `detached` mode in the background.

If you see no errors, congratulations! You can now access the Home Assistant management backend by navigation to `http://rpi.local:8123` (if you have used another hostname adjust the URL accordingly). You can now start the onboarding process, for this I highly recommend the [official documentation](https://www.home-assistant.io/getting-started/onboarding).
