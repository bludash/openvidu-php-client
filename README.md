# openvidu-php-client
openViduPhpClient gives you simple access to your openVidu server via a PHP client. Its a PHP library wrapping OpenVidu Server REST API. 

Very simple, very basic. No JQuery, no Bootstrap. Just pure HTML, CSS and JS.

Currently it only supports the most basic features like session and connection. Feel free to contribute!

# Install

Put everything onto your PHP webserver. Nothing else required.

# Usage example

1. To establish a video call with one or more participants, just enter your openVidu server credentials in openvidu-php-client.php:

```php
const OPENVIDU_API_URL = 'https://openvidu.example.com';
const OPENVIDU_API_SECRET = 'YOUR SECRET';
const OPENVIDU_API_PORT = 443;
const OPENVIDU_API_USER = 'OPENVIDUAPP';
```
2. And with your browser go to "http://localhost:4443/openvidu.html" or "http://example.com:443/openvidu.html"

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/bludash/openvidu-php-client. This project is intended to be a safe, welcoming space for collaboration, and contributors are expected to adhere to the [Contributor Covenant](http://contributor-covenant.org) code of conduct.

[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-2.0-4baaaa.svg)](code_of_conduct.md)

## License

The code is available as open source under the terms of the [MIT License](https://opensource.org/licenses/MIT).

## Code of Conduct

Everyone interacting in the OpenVidu project’s codebases, issue trackers, chat rooms and mailing lists is expected to follow the [code of conduct](https://github.com/[USERNAME]/openvidu-php-client/blob/master/CODE_OF_CONDUCT.md).
