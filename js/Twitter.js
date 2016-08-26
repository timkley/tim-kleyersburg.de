var Twitter = function(options) {
    var _settings = {
        apiUrl: '/api/twitter.php',
    }

    this.settings = _settings;
    this.results = {};
};

Twitter.prototype.makeApiCall = function() {
    if (this.settings.apiUrl) {
        var self = this;

        var xhr = new XMLHttpRequest();
        xhr.open('GET', this.settings.apiUrl);
        xhr.send();

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                self.results = JSON.parse(xhr.responseText);
                self.fillHtml();
            }
        }
    }
}

Twitter.prototype.fillHtml = function() {
    var TextEl = document.querySelector('.card--tweet .tweet__text');

    TextEl.textContent = this.results[0].text;

    document.querySelector('.card--tweet').classList.remove('loading');
};
module.exports = Twitter;