# ApiProblem

[![Build Status](https://travis-ci.org/Crell/ApiProblem.svg?branch=master)](https://travis-ci.org/Crell/ApiProblem)

This library provides a simple and straightforward implementation of the IETF Problem Details for HTTP APIs, [RFC 7807][1].

[RFC 7807][1] is a simple specification for formatting error responses from RESTful APIs on the web.  This library provides a simple and convenient way to interact with that specification.  It supports generating and parsing [RFC 7807][1] messages, in both JSON and XML variants.

## Generating responses

What's that you say?  Someone sent your API a bad request?  Tell them it's a problem!

```php
use Crell\ApiProblem\ApiProblem;

$problem = new ApiProblem("You do not have enough credit.", "http://example.com/probs/out-of-credit");
// Defined properties in the API have their own setter methods.
$problem
  ->setDetail("Your current balance is 30, but that costs 50.")
  ->setInstance("http://example.net/account/12345/msgs/abc");
// But you can also support any arbitrary extended properties!
$problem['balance'] = 30;
$problem['accounts'] = [
  "http://example.net/account/12345",
  "http://example.net/account/67890"
];

$json_string = $problem->asJson();

// Now send that JSON string as a response along with the appropriate HTTP error
// code and content type which is available via ApiProblem::CONTENT_TYPE_JSON.
// Also check out asXml() and ApiProblem::CONTENT_TYPE_XML for the angle-bracket fans in the room.
```

Or, even better, you can subclass ApiProblem for a specific problem type (since the type and title are supposed to go together and be relatively fixed), then just populate your own error-specific data.  Just like extending an exception!

## Sending Responses

You're probably using [PSR-7][3] for your responses. That's why this library includes a utility to convert your `ApiProblem` object to a PSR-7 `ResponseInterface` object, using a [PSR-17][4] factory of your choice.  Like so:

```php
use Crell\ApiProblem\HttpConverter;

$factory = getResponseFactoryFromSomewhere();

// The second paramter says whether to pretty-print the output.
$converter = new HttpConverter($factory, true);

$response = $converter->toJsonResponse($problem);
// or
$response = $converter->toXmlResponse($problem);
```

That gives back a fully-functional and marked Response object, ready to send back to the client.

## Receiving responses

Are you sending messages to an API that is responding with API-Problem errors? No problem!  You can easily handle that response like so:

```php
use Crell\ApiProblem\ApiProblem;

$problem = ApiProblem::fromJson($some_json_string);
$title = $problem->getTitle();
$type = $problem->getType();
// Great, now we know what went wrong, so we can figure out what to do about it.
```

(It works for fromXml(), too!)

## Installation

Install ApiProblem like any other Composer package:

    composer require crell/api-problem

See the [Composer documentation][2] for more details.

## License

This library is released under the MIT license.  In short, "leave the copyright statement intact, otherwise have fun."  See LICENSE for more information.

## Contributing

Pull requests accepted!  The goal is complete conformance with the IETF spec.

[1]: https://tools.ietf.org/html/rfc7807
[2]: http://getcomposer.org/
[3]: https://www.php-fig.org/psr/psr-7/
[4]: https://www.php-fig.org/psr/psr-17/
