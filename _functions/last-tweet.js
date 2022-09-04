const axios = require('axios')
const {TWITTER_BEARER_TOKEN} = process.env

exports.handler = async function (event, context) {
    const twitterResponse = await axios.get(
        'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=timkley&count=1',
        {
            headers: {
                Authorization: `Bearer ${TWITTER_BEARER_TOKEN}`,
            },
        },
    )

    return {
        statusCode: 200,
        headers: {
            'Access-Control-Allow-Origin': '*',
            'Cache-Control': 'max-age: 300',
            'Content-Type': 'application/json; charset=utf-8',
        },
        body: JSON.stringify(twitterResponse.data[0])
    }
}