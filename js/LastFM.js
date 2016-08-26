var LastFm = function(options) {
    var _settings = {
        apiUrl: 'http://ws.audioscrobbler.com/2.0/?format=json',
        apiKey: '1d5d6d83a1c4e1a2705c4b0aa990c15b',
        method: 'user.getrecenttracks',
        user: 'timmotheus',
        fullApiUrl: ''
    }

    this.settings = _settings;
    this.results = {};
    
    this.assembleCallbackUrl();
};

LastFm.prototype.assembleCallbackUrl = function() {
    this.settings.fullApiUrl = this.settings.apiUrl + '&method=' + this.settings.method + '&user=' + this.settings.user + '&limit=1' + '&api_key=' + this.settings.apiKey;
}

LastFm.prototype.makeApiCall = function() {
    if (this.settings.fullApiUrl) {
        var self = this;

        var xhr = new XMLHttpRequest();
        xhr.open('GET', this.settings.fullApiUrl);
        xhr.send();
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                self.results = JSON.parse(xhr.responseText);
                self.fillHtml();
            }
        }
    }
}

LastFm.prototype.getMostRecentTrack = function() {
    return this.results.recenttracks.track[0];
}

LastFm.prototype.fillHtml = function() {
    var imageEl = document.querySelector('.card--scrobble .scrobble__image');
    var trackEl = document.querySelector('.card--scrobble .scrobble__track');
    var ArtistEl = document.querySelector('.card--scrobble .scrobble__artist');

    var lastTrack = this.getMostRecentTrack();

    var imageSrc = lastTrack.image[2]['#text'];

    imageEl.src = imageSrc;
    trackEl.textContent = lastTrack.name;
    ArtistEl.textContent = lastTrack.artist['#text'];

    document.querySelector('.card--scrobble').classList.remove('loading');
};
module.exports = LastFm;