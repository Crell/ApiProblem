Crell\ApiProblem
================

This library provides a simple and straightforward implementation of the
[IETF API-Problem specification][1], currently in draft 3.

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
  ->setProblemInstance("http://example.net/account/12345/msgs/abc");
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

## Receiving responses

Are you sending messages to an API that is responding with API-Problem errors?
No problem!  You can easily handle that response like so:

```php
use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\RequiredPropertyNotFoundException;

try {
  $problem = ApiProblem::fromJson($some_json_string);
  $title = $problem->getTitle();
  $problemType = $problem->getProblemType();
  // Great, now we know what went wrong, so we can figure out what to do about it.
}
catch (RequiredPropertyNotFoundException $e) {
  // Uh oh, we received a badly-formed API-Problem! Log it and go yell at the
  // developers on the other end.
}
```

(It works for fromXml(), too!)

## Installation

The preferred method of installation is via Packagist as this provides the PSR-0
autoloader functionality. The following composer.json will download and install
the latest version of the ApiProblem library into your project.

```json
{
    "require": {
        "crell/api-problem": "*"
    },
    "minimum-stability": "dev"
}
```

You can also specify a specific release tag.  See the [Composer documentation][2]
for more details.

Alternatively, clone the project and install into your project manually.


## License

This library is released under the MIT license.  In short, "leave the copyright
statement intact, otherwise have fun."  See LICENSE for more information.

## Contributing

Pull requests accepted!  The goal is complete conformance with the IETF spec.
This library will be updated as needed for future drafts.

[1]: http://tools.ietf.org/html/draft-nottingham-http-problem-03
[2]: http://getcomposer.org/
