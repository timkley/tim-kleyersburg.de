const axios = require('axios')
const {LASTFM_API_KEY} = process.env

exports.handler = async function (event, context) {
    const lastfmResponse = await axios.get(
        `http://ws.audioscrobbler.com/2.0/?format=json&method=user.getrecenttracks&user=timmotheus&limit=1&api_key=${LASTFM_API_KEY}`,
    )

    return {
        statusCode: 200,
        headers: {
            'Access-Control-Allow-Origin': '*',
            'Cache-Control': 'max-age: 300',
            'Content-Type': 'application/json; charset=utf-8',
        },
        body: JSON.stringify(lastfmResponse.data.recenttracks.track[0])
    }
}