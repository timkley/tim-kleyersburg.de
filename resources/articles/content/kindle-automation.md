---
date: 2025-11-22
title: Using Kindle and Home Assistant for my favorite automation
excerpt: Learn how you can use Home Assistant and your kindle to automate your night time routine.
tags: [smarthome]
---

I love reading before bed, but I always forget the time and have to find the right button to turn off the bedside lamp. This is one of those tiny annoyances that's perfect for home automation.

The beauty of this solution is its simplicity: the Kindle only connects to Wi-Fi when you turn it on, making it a perfect trigger for automation.

## How it works

When you activate your Kindle, it connects to your Wi-Fi network. Home Assistant detects this through a device tracker and starts a 10-minute timer. After the timer expires, your bedroom lights gradually fade out.

I added a time condition to prevent the lights from turning off too early in the evening - nobody wants their lights to go out at 7 PM just because they picked up their Kindle.

## Setting up the automation

Create an automation with the following configuration:

```yaml
alias: "Bedroom: Turn off night lamp after 10 minutes of reading"
description: ""
mode: single
triggers:
  - entity_id:
      - device_tracker.kindle
    to: home
    trigger: state
conditions:
  - condition: time
    before: "23:59:58"
    after: "21:00:01"
actions:
  - delay:
      hours: 0
      minutes: 10
      seconds: 0
      milliseconds: 0
  - data:
      transition: 5
    target:
      area_id: bedroom
    action: light.turn_off
```

**Important**: Replace `bedroom` with your bedroom area ID, or use specific entity IDs if you haven't configured areas in Home Assistant.

The `transition: 5` parameter gradually dims the lights over 5 seconds, preventing the jarring experience of sudden darkness when you're likely already half asleep.

## Setting up device tracking for your Kindle

If your Kindle doesn't appear as a device tracker yet, you'll need to add it:

1. Open Settings → Devices & Services
2. Check if your router integration is set up (most routers are supported)
3. Your Kindle should appear as a device once it connects to Wi-Fi
4. Note the entity ID (likely something like `device_tracker.kindle`)

That's it! A simple automation that makes bedtime just a little bit better.
