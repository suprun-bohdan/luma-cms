# Webhook verification

Luma CMS signs outbound webhook bodies with HMAC-SHA256.

## Header format

```http
X-Luma-Signature: t=1710000000,v1=abc123...
```

- `t` — Unix timestamp when the request was signed
- `v1` — HMAC-SHA256 hex digest of `{t}.{raw_body}` using the webhook secret

Reject requests when the timestamp is older than 5 minutes.

## Payload shape

```json
{
  "event": "page.published",
  "occurred_at": "2026-05-31T12:00:00+00:00",
  "entity": "page",
  "id": 1,
  "slug": "home",
  "data": {
    "title": "Home",
    "status": "published",
    "published_at": "2026-05-31T12:00:00+00:00"
  },
  "delivery_id": "019e7dbf-f214-7004-afd6-0290148a0261"
}
```

## PHP verify example

```php
$header = $_SERVER['HTTP_X_LUMA_SIGNATURE'] ?? '';
preg_match('/t=(\d+),v1=([a-f0-9]+)/', $header, $matches);
[$_, $timestamp, $signature] = $matches;
$body = file_get_contents('php://input');
$expected = hash_hmac('sha256', $timestamp.'.'.$body, $secret);
if (! hash_equals($expected, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}
```

## JavaScript verify example

```javascript
import crypto from 'node:crypto'

function verify(secret, header, body) {
  const match = header.match(/t=(\d+),v1=([a-f0-9]+)/)
  if (!match) return false
  const [, timestamp, signature] = match
  const expected = crypto
    .createHmac('sha256', secret)
    .update(`${timestamp}.${body}`)
    .digest('hex')
  return crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected))
}
```

## Events

| Event | Trigger |
|-------|---------|
| `page.published` | Page publish action |
| `entry.published` | Entry publish action |
| `form.submission.created` | Public form submit (non-honeypot) |
