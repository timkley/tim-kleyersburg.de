import axios from 'axios';

export default {
    data() {
        return {
            tweet: undefined,
            result: undefined
        }
    },
    created() {
        axios.get('/api/twitter.php')
            .then((result) => {
                const tweet = result.data[0];
                this.result = tweet;
                this.tweet = this.urlify(tweet.text);
            });
    },
    methods: {
        urlify(text) {
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            return text.replace(urlRegex, function(url) {
                return '<a class="transition hover:text-gray-700 border-b border-blue-400 hover:border-blue-600" href="' + url + '">' + url + '</a>';
            })
        }
    },
    render() {
        return this.$scopedSlots.default({
            tweet: this.tweet
        });
    }
}
