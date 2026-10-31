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

describe('SEC-05 reviews', function () {
    it('accepts reviews only from customers who bought the product')->todo(issue: 21);
    it('validates the rating between 1 and 5')->todo(issue: 21);
});
