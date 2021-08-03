window.lastfm = () => ({
    artist: undefined,
    track: undefined,
    albumart: undefined,

    init() {
        fetch('https://ws.audioscrobbler.com/2.0/?format=json&method=user.getrecenttracks&user=timmotheus&limit=1&api_key=1d5d6d83a1c4e1a2705c4b0aa990c15b')
            .then(response => response.json())
            .then(result => {
                const track = result.recenttracks.track[0]
                this.artist = track.artist['#text']
                this.track = track.name
                this.albumart = track.image[2]['#text']
            })
    }
})

window.twitter = () => ({
    tweet: undefined,

    init() {
        fetch('https://workers.tim-kleyersburg.de/twitter-api')
            .then(response => response.json())
            .then(result => {
                let tweet = result[0]
                const urlRegex = /(https?:\/\/[^\s]+)/g;
                this.tweet = tweet.text.replace(urlRegex, function(url) {
                    return '<a class="transition hover:text-gray-700 border-b border-blue-400 hover:border-blue-600" href="' + url + '">' + url + '</a>';
                })
            })
    }
})