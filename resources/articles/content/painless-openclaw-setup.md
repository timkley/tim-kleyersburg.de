---
date: 2026-01-25
title: Setup OpenClaw on a Hetzner VPS for 24/7 access
excerpt: Painless guide on how to correctly set up OpenClaw (formerly Clawdbot / Moltbot) to get your personal 24/7 assistant
tags: [ai]
---

> **Note:** This project was originally released as **Clawdbot**, then renamed to **Moltbot**, and is now called **OpenClaw**. If you followed this guide under one of the previous names, the steps are the same — only the package and command names have changed. This guide has been updated to reflect the current name.

OpenClaw seems to be the biggest hype in January 2026. Many people talk about it, the amount of development poured into it and the speed it's developing is crazy.

The documentation is thorough, but even with Claude Code I didn't come to a working solution without manually intervening.

So what follows is a quick walkthrough on how to get OpenClaw working on a fresh Ubuntu 24.04 VPS from Hetzner.

## 1. Create VPS

Log into your cloud provider and create a simple VPS. 2 vCPUs and 4GB of RAM is more than enough to start. I used the cheapest Hetzner option:

![Screenshot of the Hetzner server creation panel](hetzner-vps.png)

## 2. Basic VPS setup

We'll start with logging into the server, updating, and creating a dedicated user to use with OpenClaw.

```bash
ssh root@your-server-ip # enter the password you received via email

apt update && apt upgrade -y # update packages
# if prompted to reboot, do it.

adduser openclaw # create the new user. You can leave all details empty, but don't forget to set a strong password
usermod -aG sudo openclaw # add the user to the sudoers group so we can use `sudo`

su - openclaw # change to the new user
```

## 3. Securing your VPS

Before installing OpenClaw, we should secure the server with some basic security measures. These steps will protect your VPS from common attacks.

### Setup SSH key authentication

First, let's set up SSH key authentication so you can log in without a password. Run this on your **local machine**:

```bash
# Generate SSH key if you don't have one already
ssh-keygen -t ed25519 -C "your_email@example.com"

# Copy your public key to the server
ssh-copy-id openclaw@your-server-ip
```

Test the SSH key login by opening a new terminal and connecting:

```bash
ssh openclaw@your-server-ip
```

If you can log in without entering a password, the key authentication is working.

### Disable password authentication

Now that key-based authentication is working, let's disable password login. This prevents brute force attacks.

```bash
# Edit SSH configuration
sudo vim /etc/ssh/sshd_config
```

Find and update these lines (uncomment if needed):

```
PasswordAuthentication no
PubkeyAuthentication yes
PermitRootLogin no
```

Save the file (`:wq`) and restart SSH:

```bash
sudo systemctl restart ssh
```

### Install fail2ban

Fail2ban monitors login attempts and automatically blocks IP addresses that show malicious behavior.

```bash
sudo apt install fail2ban -y

# Create a local configuration file
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Edit the configuration
sudo vim /etc/fail2ban/jail.local
```

Find the `[sshd]` section and ensure it looks like this:

```
[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log
maxretry = 5
bantime = 3600
```

Start and enable fail2ban:

```bash
sudo systemctl start fail2ban
sudo systemctl enable fail2ban

# Check status
sudo fail2ban-client status sshd
```

### Setup UFW firewall

Configure a basic firewall to only allow necessary connections:

```bash
# Install UFW
sudo apt install ufw -y

# Allow SSH (important - do this first!)
sudo ufw allow ssh

# Enable the firewall
sudo ufw enable

# Check status
sudo ufw status
```

If you plan to expose OpenClaw through a web interface later, you can allow HTTP/HTTPS:

```bash
sudo ufw allow http
sudo ufw allow https
```

## 4. Installing prerequisites

We need some packages for OpenClaw to work properly. Verify that you are logged in as the `openclaw` user or you might run into permission problems.

### Node

In my testing I found `npm` to be the most reliable package manager, but you can also try `pnpm`. You can find the up-to-date install instructions at [nodejs.org/en/download](https://nodejs.org/en/download).

```bash
# Install Node
# Download and install nvm:
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash

# in lieu of restarting the shell
\. "$HOME/.nvm/nvm.sh"

# Download and install Node.js:
nvm install 24

# Verify the Node.js version:
node -v # Should print "v24.13.0".

# Verify npm version:
npm -v # Should print "11.6.2".
```

### Homebrew

[Homebrew](https://brew.sh/) is a package manager. It is needed by some skills (instructions used by OpenClaw to enhance its abilities).

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### Tailscale (optional)

[Tailscale](http://tailscale.com/) is a Zero Trust identity-based connectivity platform. It can be used to securely connect your local computer with your OpenClaw gateway making it possible for the gateway to do things on your local computer.
I currently don't have a use case for this, so you might also skip it.

```bash
curl -fsSL https://tailscale.com/install.sh | sh
sudo tailscale up
```

## 5. OpenClaw

Now the fun begins 🦞. Let's install OpenClaw and onboard you.

### Installation
```bash
npm i -g openclaw
```

This might take a while, but that's it. You've installed OpenClaw and are ready to onboard.

### Onboarding

Run `openclaw onboard`. It will take you through its interactive onboarding guide. Below are the settings I used but be aware that OpenClaw is very actively developed, so settings might change quickly. I've left some comments.

```
◆  I understand this is powerful and inherently risky. Continue?
│  Yes
|
◆  Onboarding mode
│  ○ QuickStart
│  ● Manual (Configure port, network, Tailscale, and auth options.)
|  # Use manual mode for more control
|
◆  What do you want to set up?
│  ● Local gateway (this machine) (Gateway reachable (ws://127.0.0.1:18789))
│  ○ Remote gateway (info-only)
|  # It might seem counterintuitive at first, but you want the Gateway to run locally on the VPS
|
◆  Workspace directory
│  /home/openclaw/clawd
|
◆  Model/auth provider
│  ● OpenAI (Codex OAuth + API key)
│  ○ Anthropic
│  ○ MiniMax
│  ○ Qwen
│  ○ Synthetic
│  ○ Google
│  ○ Copilot
|  ...
|  # Choose what's right for you! I've used my Claude Code subscription to authenticate. Refer to the documentation if needed.
|
◆  Gateway port
│  18789
|
◆  Gateway bind
│  ● Loopback (127.0.0.1)
│  ○ LAN (0.0.0.0)
│  ○ Tailnet (Tailscale IP)
│  ○ Auto (Loopback → LAN)
│  ○ Custom IP
|
◆  Gateway auth
│  ○ Off (loopback only)
│  ● Token (Recommended default (local + remote))
│  ○ Password
|
|  # this will give a token you may use to access the dashboard
◆  Tailscale exposure
│  ○ Off
│  ● Serve (Private HTTPS for your tailnet (devices on Tailscale))
│  ○ Funnel
|  # choose "off" if you don't use Tailscale
|
◆  Reset Tailscale serve/funnel on exit?
│  ○ Yes / ● No
|  # choose "no" if you used "Serve" as Tailscale exposure
|
◆  Configure chat channels now?
│  ● Yes / ○ No
|  # You probably want to do this. Having access via chat makes the setup much more flexible.
|  # Choose your favorite provider. When in doubt just use Telegram, they have the easiest setup for a chat bot.
│
◇  Skills status ────────────╮
│                            │
│  Eligible: 13              │
│  Missing requirements: 38  │
│  Blocked by allowlist: 0   │
│                            │
├────────────────────────────╯
│
◆  Configure skills now? (recommended)
│  ● Yes / ○ No
|  # Choose `Yes` and select all Skills you want to have installed with `Spacebar` and confirm with `Enter`
|  # If unsure just skip it for now, you can always configure this later
│
◆  Preferred node manager for skill installs
│  ● npm
│  ○ pnpm
│  ○ bun
│
◆  Set GOOGLE_PLACES_API_KEY for goplaces?
│  ○ Yes / ● No
|  # Unless you have API keys just answer no to all the API key questions
│
◆  Enable hooks?
│  ◼ Skip for now
│  ◻ 🚀 boot-md
│  ◻ 📝 command-logger
│  ◻ 💾 session-memory
|  # Skip, unless you know what you are doing (or read the manual)
│
◆  Install Gateway service (recommended)
│  ● Yes / ○ No
│
◆  Gateway service runtime
│  ● Node (recommended) (Required for WhatsApp + Telegram. Bun can corrupt memory
on reconnect.)
│
◆  How do you want to hatch your bot?
│  ● Hatch in TUI (recommended)
│  ○ Open the Web UI
│  ○ Do this later
|  # After confirming this you will be put into a TUI (Terminal User Interface) to "hatch" your new assistant. Have fun!
```

### Telegram

After the gateway has started (the onboarding setup should have shown you a success message) you can message your bot if you created one with `/start` to pair your Telegram chat sessions to your OpenClaw gateway. Everything is explained in the chat.

## 6. Use cases and configuration

You might be tempted to configure workflows, automations, or other features via the Dashboard UI. While this works for some things, I highly recommend using the chat interface instead.

Simply describe what you want to achieve. OpenClaw is remarkably good at understanding intent and will guide you through the setup process. For example, I've long wanted a summary of my bookmarked tweets. I tend to hit the bookmark icon and then forget to review them later. I asked OpenClaw to send me a daily digest, and it walked me through the entire process: setting up `bird` (the CLI for interacting with X), configuring the skill, and even sending a test message to confirm everything worked.

If there's something you want to accomplish, just ask. It genuinely feels like having a personal assistant. Rather than responding with "I'm sorry, I can't do that," it proposes a plan to achieve your desired outcome.
