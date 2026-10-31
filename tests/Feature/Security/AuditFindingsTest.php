<?php

/*
| Security findings from docs/audit/initial-audit.md.
|
| Each finding is a pending test linked to its issue. When a module is
| rewritten, the matching test is implemented first, fails against the old
| behaviour, and then passes against the new one.
*/

describe('SEC-01 privilege escalation', function () {
    it('rejects profile updates from guests')->todo(issue: 17);
    it('forbids a user from updating another user')->todo(issue: 17);
    it('ignores the admin flag in profile update payloads')->todo(issue: 17);
});

describe('SEC-02 SQL injection in checkout', function () {
    it('resolves the cart from the session, never from the URL')->todo(issue: 18);
});

describe('SEC-03 IDOR on cart items', function () {
    it('forbids changing or deleting another user\'s cart item')->todo(issue: 19);
    it('rejects zero, negative and above-stock quantities')->todo(issue: 19);
});

describe('SEC-04 CSRF on checkout', function () {
    it('places orders only through a CSRF-protected POST')->todo(issue: 20);
    it('does not create two orders on double submit')->todo(issue: 20);
});

describe('SEC-05 reviews', function () {
    it('accepts reviews only from customers who bought the product')->todo(issue: 21);
    it('validates the rating between 1 and 5')->todo(issue: 21);
});

describe('SEC-06 authentication', function () {
    it('throttles repeated failed logins')->todo(issue: 22);
    it('regenerates the session on login and invalidates it on logout')->todo(issue: 22);
});
