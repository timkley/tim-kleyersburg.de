---
date: 2023-03-12
title: Reconnect an unavailable Zigbee device in Home Assistant
excerpt: Learn how to reconnect an unavailable Zigbee device in Home Assistant.
tags: [quicktip, smarthome]
image: /articles/img/ogimages/reconnect-unavailable-zigbee-device-home-assistant.webp
---

If you have ever encountered a situation where a Zigbee device becomes unavailable in Home Assistant, you may be wondering how to easily reconnect it.

![Screenshot of some unavailable devices in the Home Assistant UI](unavailable-devices.png)

Although there's no apparent method to do this in the UI, it's surprisingly easy to accomplish!

## Steps to reconnect an unavailable Zigbee device

1. Open the Settings → Devices & Services page
2. Click on "Add integration"
3. Select "Add Zigbee device"
4. Put your unavailable device into pairing mode

Your device should now re-pair and be available again.

The advantage of this method is that you don't have to remove the device from Home Assistant first, which would break all existing automations.
