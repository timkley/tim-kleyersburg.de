---
date: 2025-10-31
title: Building A Life OS - Part 2
excerpt: A Life OS is a system that helps you manage your life. What have I done recently in my Life OS?
image: /articles/img/ogimages/building-a-life-os.webp
tags: [life-os]
---

## Quick recap

[Last time](/articles/building-a-life-os) I've explained my motiviation behind building a project like this, what features I've built (like a notification system for the digital timetable of my kid) and what my plans were for the future.

Over half a year later and I still use many of the things I've built, discarded some, and removed things that didn't work.

## What worked

All features I've implemented initially (digital timetable with notifications, vocabulary tests, goal tracking, bookmarks) are still in place.  
Although recently there weren't many vocabulary tests done (learning methods changed a bit, grammar coming in).

But: [I don't use an LLM anymore](https://github.com/timkley/tim-kleyersburg.de/commit/7365e75935e927353cdd0210312a327472b1c55b) to create motivational messages for goal tracking. Initially, the thought of having a "personal assistant" sounded great, but after implementation it was just plain boring. That might be a skill issue on my part, but I've removed it completely for now.

The goal tracking itself still motivates me to stay on track. To make it more rewarding I've created an XP system which rewards streaks.  
Although I must say this isn't motivating me as much as I've hoped, so I might remove it in the near future.

That's one of the aspects of this project that I like most: it feels like a living organism adjusting to my needs. Maybe this also feeds a hidden god-complex, I don't know.

## What didn't work

In the closing words of my [last post](/articles/building-a-life-os) I had the plan to build a daily digest, which I did.  
But turns out, this is also not as useful as I hoped, so I quickly removed it again. The problem I've faced was that in regard to fulfil the requirement of the aforementioned personal assistant, the timing would need to be much better. I don't want to see nearly identical messages each day at 9 am, but when I'm ready to consume it. Maybe I need a Home Assistant integration to make this better.

## What is new

I've added _a bunch_ of new stuff since February.

### Gym tracker

Before, I was using the app my gym provided to track my sessions. I also used [Alpha Progression](https://alphaprogression.com/) for maybe a year. The gym provided system wasn't very user friendly and I didn't like that some simple functions in Alpha Progression needed a subscription.

So like any good programmer I underestimated the complexity massively and built my own gym session tracker.

![Screenshot of the tracking of a session](grind-tracking.png)

I started pretty small. Just having training plans, exercises and a way to track each set. One thing important for me was to have a clear indicator when to progress. It's a simple thing but helps me get better. Alpha Progress called this "smart progression hints" or something like that. Nothing about this has to be smart. If I've hit the upper bound of my rep range I put on more weight. Easy as that.

### Packlist Generator 

I grep up in a household where we had a 4 pages long Excel sheet to plan what to take with us on vacation. This causes irreversible brain damage to a little human being, causing them to write a program they can use to track what to pack for a journey.

![Screenshot of a journey](gear-journey.png)

The interesting part wasn't the items itself but the business logic what should be packed. Each journey can have multiple properties (like `traveling-with-kid`, `business-trip`) which is be used to match to specific items.

![Screenshot of the item properties](gear-item-properties.png)

Each property has a matching function defining if the item should be included in the journey. For properties like `warm-weather` or `rain-expected` I needed a Weather API implementation. But most APIs only give you a forecast of around 14 days. While this might sound like there are APIs with a more extensive forecast that's unfortunately not the case. The most limiting factor is the weather models, getting less and less precise farther into the future.

But I didn't want to plan journeys only 2 weeks ahead. While this would be sufficient in most cases, solving the problem of looking farther into the future was the problem I wanted to solve.

I'm using [Open-Meteo](https://open-meteo.com/) to get the weather. They have great APIs, good documentation and on top of that are free.

If the journey is planned more days ahead I'm using an average from the past five years to assume what the weather might me.

```php
    /**
     * @param  array{latitude:float,longitude:float}  $coordinates
     * @return Collection<int, DayForecast>
     */
    private static function fetchHistoricalAverageForecast(array $coordinates, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        $historicalStartDate = $start->subYears(self::HISTORICAL_YEARS_TO_AVERAGE);
        $historicalEndDate = $end->subYears(1);

        $params = [
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum',
            'start_date' => $historicalStartDate->format('Y-m-d'),
            'end_date' => $historicalEndDate->format('Y-m-d'),
        ];

        $response = self::makeApiCall(self::HISTORICAL_API_URL, $params);

        if (! $response || ! isset($response['daily'])) {
            return collect();
        }

        return self::processHistoricalData($response['daily'], $start, $end);
    }
```

No idea yet how precise this will be, but for a rough estimate it should suffice. I always have the possibility to recreate the list if needed.

### LastFM importer

Over the last 18 years I've accumulated [over 215k scrobbles](https://www.last.fm/user/Timmotheus) on Last.fm. Apart from maybe Google this is one of the services I use the longest and without much interruptions.

In the last months I more and more lean towards the idea of owning my own data, so I wrote an importer for Last.fm.

Because of the way the [Last.fm API](https://www.last.fm/api) works there unfortunately is no easy way to export everything.  
Pages are counted from present to past and there is no way to reverse this logic. This could mean that a scrobble will change the currently first page the second.

But I think the solution I came up with is simple and elegant:

```php
public function handle(LastFm $lastFm): void
{
    $localScrobbles = Scrobble::query()->count();

    $latestScrobble = $lastFm->getRecentTracks(1);

    // Get the latest scrobble to get the total scrobbles from the API response
    $totalScrobbles = (int) data_get($latestScrobble, '@attr.total');

    // If we have the same amount of scrobbles locally, bail
    if ($totalScrobbles === $localScrobbles) {
        return;
    }

    // calculate the total number of pages
    $totalPages = (int) ceil($totalScrobbles / self::LIMIT);

    // calculate the difference to know which page to get
    $pageToFetch = max($this->page ?? (int) ceil($totalPages - ($localScrobbles / self::LIMIT)), 1);

    // get the scrobbles and reject all with the exact same date
    $allScrobbles = collect(data_get($lastFm->getRecentTracks(limit: self::LIMIT, page: $pageToFetch), 'track'));
    $scrobbles = $allScrobbles->reject(fn ($scrobble) => ! data_get($scrobble, 'date.uts'));

    // format and save to db
    $data = $scrobbles->map(function ($scrobble) {
        return [
            'artist' => data_get($scrobble, 'artist.#text'),
            'album' => data_get($scrobble, 'album.#text'),
            'track' => data_get($scrobble, 'name'),
            'played_at' => Carbon::createFromTimestamp(data_get($scrobble, 'date.uts')),
            'payload' => json_encode($scrobble),
        ];
    })->filter();

    Scrobble::query()->upsert($data->toArray(), ['artist', 'track', 'played_at']);

    // if we are on the first page, bail
    if ($pageToFetch === 1) {
        return;
    }

    // recursive logic, if we had Scrobbles get the next page
    // if not, fetch the same page to make sure we now have all Scrobbles
    self::dispatch(($scrobbles->count() ? $pageToFetch - 1 : $pageToFetch));
}
```

### Todo Management

It started like a simple todo list tutorial. But I've had some ideas and concepts in mind that manifested over the years of usage of other tools.

- **There are no overdue tasks.** A task is either due today, or not. I can't do something yesterday so don't punish me for not meeting a deadline but let it gracefully roll over.
- **Infinite sub tasks.** It's known that big tasks fuel procrastination because you don't know where to start. So I wanted a system that is able to handle a task no matter how big, by giving me the possibility to break it down into as small sub tasks as needed.
- **Notes integration.** The border between notes and tasks is sometimes very thin. Oftentimes a finished task becomes a note or some task spawns from a note. So every task in my system can be a note or a task and it can be switched around as needed. Apart from completing they have the same parts:
    - description
    - links
    - attachments
    - comments
    - subtasks / subnotes

The first [version in April 2025](https://github.com/timkley/tim-kleyersburg.de/commit/163adf1fd8d6ad4afe62a03d9bbf57b6c0342b1c) didn't have that much functionality. It was a very basic todo list with only the notes added.  
But it evolved and evolved as I saw what worked and what didn't work. A few months in I moved all my notes from Obsidian over and haven't looked back since.

Some time in the future I want to tackle the personal assistant again, using all my notes and tasks as context.

### Printer System using a receipt printer

This one is the most controversial project I've done - at least when talking about it in my social circle. While nobody ever really sees most of the things I'm programming: the receipt printer on my desk _will_ rise questions.

I've read this great post by Laurie: [A receipt printer cured my procrastination](https://www.laurieherault.com/articles/a-thermal-receipt-printer-cured-my-procrastination). It resonated so much with how I see task management, e.g. he also advocates for breaking big tasks down into smaller ones.  
So I've started researching receipt printers, how they can be connected and how I could print things on it.

What a fun rabbit hole! Building stuff on the web fascinates me since nearly 20 years. But building stuff that interacts with the _physical world_ had some kind of magic feeling. I can now press a little print button in my task management and it prints the task nearly instantly. Regardless of where I am.

How I've built the necessary parts will be a post of its own. But let me tell you: having a physical representation of a task helped me very much staying on top of my todos. More like any other system before. I highly recommend it - if you are able to live with the snarky comments you most likely will get.
