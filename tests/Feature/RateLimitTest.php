<?php

/*
| Each test request gets a fresh session unless it sends a session cookie,
| so these tests send a fixed session ID, like a real browser would.
*/
function asSession(string $letter)
{
    return test()->withCookie(config('session.cookie'), str_repeat($letter, 40));
}

it('allows 120 requests per minute for one session, then answers 429', function () {
    for ($i = 0; $i < 120; $i++) {
        asSession('a')->get('/')->assertOk();
    }

    asSession('a')->get('/')->assertStatus(429);
});

it('gives another session its own counter', function () {
    for ($i = 0; $i < 120; $i++) {
        asSession('a')->get('/');
    }

    asSession('b')->get('/')->assertOk();
});

it('caps all sessions from one IP at 600 per minute', function () {
    // 600 requests spread over 6 sessions, none of them over their own limit.
    foreach (['a', 'b', 'c', 'd', 'e', 'f'] as $letter) {
        for ($i = 0; $i < 100; $i++) {
            asSession($letter)->get('/results/download');
        }
    }

    asSession('g')->get('/results/download')->assertStatus(429);
});
