Crell\ApiProblem
================

[![Build Status](https://secure.travis-ci.org/Crell/ApiProblem.png)](http://travis-ci.org/crell/ApiProblem)

This library provides a simple and straightforward implementation of the
[IETF API-Problem specification][1], currently in draft 7.

API-Problem is a simple specification for formatting error responses from RESTful
APIs on the web.  This library provides a simple and convenient way to interact
with that specification.  It supports generating and parsing API-Problem messages,
in both JSON and XML variants.

## Generating responses

What's that you say?  Someone sent your API a bad request?  Tell them it's a problem!

```php
$problem = new ApiProblem("You do not have enough credit.", "http://example.com/probs/out-of-credit");
// Defined properties in the API have their own setter methods.
$problem
  ->setDetail("Your current balance is 30, but that costs 50.")
  ->setInstance("http://example.net/account/12345/msgs/abc");
// But you can also support any arbitrary extended properties!
$problem['balance'] = 30;
$problem['accounts'] = array(
  "http://example.net/account/12345",
  "http://example.net/account/67890"
);

$json_string = $problem->asJson();

// Now send that JSON string as a response along with the appropriate HTTP error
// code.  Also check out asXml() for the angle-bracket fans in the room.

```

Or, even better, you can subclass ApiProblem for a specific problemType (since
the type and title are supposed to go together and be relatively fixed), then
just populate your own error-specific data.  Just like extending an exception!

## Receiving responses

Are you sending messages to an API that is responding with API-Problem errors?
No problem!  You can easily handle that response like so:

```php
use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\RequiredPropertyNotFoundException;

$problem = ApiProblem::fromJson($some_json_string);
$title = $problem->getTitle();
$type = $problem->getType();
// Great, now we know what went wrong, so we can figure out what to do about it.
```

(It works for fromXml(), too!)

## Installation

The preferred method of installation is via Packagist as this provides the PSR-0
autoloader functionality. The following composer.json will download and install
the latest version of the ApiProblem library into your project.

```json
{
    "require": {
        "crell/api-problem": "1.7"
    }
}
```

See the [Composer documentation][2] for more details.

Alternatively, clone the project and install into your project manually.


## License

This library is released under the MIT license.  In short, "leave the copyright
statement intact, otherwise have fun."  See LICENSE for more information.

## Contributing

Pull requests accepted!  The goal is complete conformance with the IETF spec.
This library will be updated as needed for future drafts.

[1]: http://tools.ietf.org/html/draft-nottingham-http-problem-07
[2]: http://getcomposer.org/
