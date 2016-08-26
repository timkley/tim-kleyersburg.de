var _LastFmPrototype = require('./LastFM');
var _TwitterPrototype = require('./Twitter');

var lastFm = new _LastFmPrototype();
var twitter = new _TwitterPrototype();

lastFm.makeApiCall();
twitter.makeApiCall();