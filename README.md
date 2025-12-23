# Basic auth system for intraserver api communication

[![Latest Version on Packagist](https://img.shields.io/packagist/v/Foodieneers/api-basic-auth.svg?style=flat-square)](https://packagist.org/packages/Foodieneers/api-basic-auth)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/Foodieneers/api-basic-auth/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Foodieneers/api-basic-auth/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/Foodieneers/api-basic-auth/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/Foodieneers/api-basic-auth/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/Foodieneers/api-basic-auth.svg?style=flat-square)](https://packagist.org/packages/Foodieneers/api-basic-auth)

This package enables secure API communication between two Laravel applications using HTTP Basic Authentication.

Install the package on **both applications** that need to communicate.

In your `.env` or `config/services.php`, define the following:

- **Inbound Password**  
  Used to authenticate **incoming** API requests from the other server.

- **Outbound Password**  
  Used when this application sends **outgoing** API requests to the other server.

- **Endpoint**  
  The full URL of the other server (e.g., `https://api.example.com`).


## Installation

You can install the package via composer:

```bash
composer require Foodieneers/api-basic-auth
```

In the `config/services.php` add the following

```php
'username' => [
    'endpoint' => 'http....' //full url for calls
    'inbound_password' => env('INBOUND_PASSWORD'),
    'outbound_password' => env('OUTBOUND_PASSWORD'),
]
```

You can define multiple `username` each of them with endpoint and inbound and outbound password.

## Usage

### Inbound Requests
For the inbound request use the `api.auth` middleware, followed by the username as specified in the config file.
```php
Route::get('/api')->middleware('api.auth:username');
```

This middleware will let all the request with username `username` and password `inbound_password`, as specified in the config files.
## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Foodieneers](https://github.com/Foodieneers)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
