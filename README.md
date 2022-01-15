<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Beipac

Laravel Mpesa Customer-to-Business-C2B stk push integration.


## Prerequisites

- PHP version 7.4.12
- composer 1.10.15 or higher.
- Laravel 8^
- MySQL dB

To check if installed on your machine run ```composer -v``` on terminal
To check php version on your terminal run ```php -version```

### Getting Started

1. Setting Repo.

- ```mkdir project```
- ```cd project```
- ```git clone https://github.com/Teatoller/beipac.git```
- ```cd beipac```
- ```git checkout develop```

v. Open your choice editor  (for vscode run ```code .``` on terminal)

2. Make sure you have the latest .env file as per the `example .env`
3. To get Mpesa details register an application with sign up for an 
account with Safaricom Daraja [daraja API](https://developer.safaricom.co.ke/) and
register an application name. Then use the credential of the application
registered.
4. Follow instructions on ngrok to generate a call back https url [ngrok](https://ngrok.com/)
5. On the beipac terminal run ```php artisan serve```

6. Use postman to run the test as illustrated in the [pull request](https://github.com/Teatoller/beipac/pull/1)


## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
