import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'local', // can be any string for local/redis
    cluster: 'mt1', // required by Pusher.js, value doesn't matter for local
    // wsHost: window.location.hostname,
    wsHost: 'localhost',
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
}); 