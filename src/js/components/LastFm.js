import axios from 'axios';

export default {
    data() {
        return {
            artist: undefined,
            track: undefined,
            albumArt: undefined
        }
    },
    created() {
        axios.get('https://ws.audioscrobbler.com/2.0/?format=json&method=user.getrecenttracks&user=timmotheus&limit=1&api_key=1d5d6d83a1c4e1a2705c4b0aa990c15b')
            .then((result) => {
                const track = result.data.recenttracks.track[0];
                this.artist = track.artist['#text'];
                this.track = track.name;
                this.albumArt = track.image[2]['#text'];
            });
    },
    render() {
        return this.$scopedSlots.default({
            artist: this.artist,
            track: this.track,
            albumArt: this.albumArt
        });
    }
}
