# IfpaClient

**IfpaClient** is a php client library
for retrieving data from the International Flipper Pinball Association (IFPA).
For more information about the IFPA api,
see https://www.ifpapinball.com/api/.

## Usage
Install via `composer require RobinLassonde/IfpaClient`.

Obtain an IFPA api key at https://www.ifpapinball.com/api.

Example usage:
```php
$ifpa_client = new IfpaClient(YOUR_API_KEY, new CurlRequestFactory());

// Get player number 25606
$ifpa_client->getPlayer('25696');

// List all players whose full name includes "Foo Bar"
$ifpa_client->listPlayerIdsByNameSegment('Foo Bar');

// List all players with email address "foo@example.com"
$ifpa_client->listPlayerIdsByEmail('foo@example.com');
```

If there is an IFPA api endpoint you'd like to use that isn't yet supported by
IfpaClient, then you can use IfpaHttpCaller directly, or better yet send a pull
request or file an issue.

## License
IfpaClient is free software distributed under the terms of the MIT license.
