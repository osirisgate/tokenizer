# 🌐 Osirisgate Tokenizer

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E=8.2-8892BF.svg)](https://www.php.net/releases/8.2/)

> Powerful and extensible token generation service built with modern PHP. Provides secure, time-bound, typed token management following Osirisgate Core architecture standards, supporting both simple built-in tokens and JSON Web Tokens (JWT).

---

## 📦 Installation

```bash
composer require osirisgate/tokenizer
```

## ✅ Requirements

* PHP 8.2 or higher
* For JWT functionality, you'll need a private key file (e.g., `private.pem`) for signing.

## ✨ Features

* **Built-in Token Generation:**
    * Generate short-lived or persistent tokens for any resource (user, device, etc.).
    * Support for OTP-style tokens (randomized uppercase codes).
    * Token reuse logic for non-expired tokens.
    * Fully testable with in-memory repository.
    * Repository interface abstraction (`TokenRepositoryInterface`) to store token into your database.
* **JWT Token Generation:**
    * Generate JSON Web Tokens (JWT) for secure authentication and authorization.
    * Supports RSA algorithm for signing JWTs.
    * Generates associated refresh tokens for obtaining new JWTs.
    * Provides functionality to revoke JWTs by invalidating refresh tokens.
    * Includes robust JWT validation with payload and header checks.
    * Repository interface abstraction for refresh tokens (`RefreshTokenRepositoryInterface`) to store refresh token into your database.

## 🧑‍💻 How to use it?

### 1. Create a buildin tokenizer instance (for non-JWT tokens)

Repository permit to store tokens in a database. You can implement your own repository by implementing the `TokenRepositoryInterface`.

```php
use Osirisgate\Component\Tokenizer\BuildInToken\Repository\InMemory\InMemoryTokenRepository;
use Osirisgate\Component\Tokenizer\BuildInToken\TokenizerService;

$repository = new InMemoryTokenRepository(); # Replace with your own implementation of TokenRepositoryInterface
$tokenizer = new TokenizerService($repository);

# Timezone is optional, default is UTC
# TokenTtl is in seconds, default is 3600
# TokenType is the subject for which the token is generated

$userId = uniqid('user_');
$result = $tokenizer->generate(
    userId: $userId,
    tokenType: 'email_verification_token',
    length: 10,
    tokenTtl: 3600,
    timezone: 'Europe/Paris'
);

/*
Output:
[
  "value" => "d98895fb30"
  "expires_at" => "2025-05-21 21:45:01 Europe/Paris (+02:00)"
]
*/

$result = $tokenizer->generate(
    userId: $userId,
    tokenType: 'email_verification_token',
    length: 5,
);

/*
Output (example):
[
  "value" => "e07a6"
  "expires_at" => "2025-05-21 19:49:56 UTC (+00:00)"
]
*/

// To validate the token:
$this->tokenizer->isValid(tokenType: 'email_verification_token', tokenValue: $result['value']);

// You can also explicitly not delete token if is valid:
$this->tokenizer->isValid(tokenType: 'email_verification_token', tokenValue: $result['value'], deleteIfValid: false);

// Or validate the token with userId:
$isValid = $tokenizer->isValidForUser(
    userId: $userId,
    tokenType: 'email_verification_token',
    tokenValue: $result['value']
);

// Do not delete the token explicitly:
$isValid = $tokenizer->isValidForUser(
    userId: $userId,
    tokenType: 'email_verification_token',
    tokenValue: $result['value'],
    deleteIfValid: false,
);

// You can also delete the token by type and value: (\Osirisgate\Component\Tokenizer\BuildInToken\Exception\UnknownTokenException will be thrown if the token is not found in the repository.)
$tokenizer->delete(
    tokenType: 'email_verification_token',
    tokenValue: $result['value']
);
```

### 2. Create a JWT tokenizer instance (for JWT tokens)

**Ensure you have a private key file (`private.pem`) in your project's `jwt` directory (or specify the correct path).**

Repository permit to store refresh tokens in a database. You can implement your own repository by implementing the `RefreshTokenRepositoryInterface`.

```php
use Osirisgate\Component\Tokenizer\JwtToken\Repository\InMemory\InMemoryRefreshTokenRepository;
use Osirisgate\Component\Tokenizer\JwtToken\Strategy\RsaJwtToken\RsaJwtTokenizer;

$repository = new InMemoryRefreshTokenRepository(); # Replace with your own implementation of RefreshTokenRepositoryInterface

# Timezone is optional, default is UTC

$jwtTokenizer = new RsaJwtTokenizer(
    refreshTokenRepository: $repository,
    jwtPrivateKeyFile: dirname(__DIR__, 2) . '/jwt/private.pem',
    jwtPassphrase: '4d25d0ac969134d25b8f28b2fbd1c930a30b576d3e53751ee693138aea3fdb92',
    timezone: 'Europe/Paris'
);

$userId = uniqid('user_');
$payload = [
    'email' => 'user@example.com',
    'roles' => ['ROLE_USER'],
];
$header = [
    'custom' => 'some-value',
];

$tokenResult = $jwtTokenizer->generate($userId, $payload, $header);

[
  "token" => Osirisgate\Component\Tokenizer\JwtToken\Entity\JwtToken^ {#194
    -value: "eyJ0eXAiOiJKV1QiLCJjdXN0b20iOiJzb21lLXZhbHVlIiwiYWxnIjoiUlMyNTYifQ.eyJpYXQiOjE3NDc4NTUyMDQsImV4cCI6MTc0Nzg1ODgwNCwic3ViIjoidXNlcl82ODJlMjc2NDJjNzVjIiwiZGF0YSI6eyJlbWFpbCI6InVzZXJAZXhhbXBsZS5jb20iLCJyb2xlcyI6WyJST0xFX1VTRVIiXX19.hEHhOLkSmf9e67gPyFv2fpBmTCxoIwFZ4EmmcT_B8HciqWJY7cm6m8BH3EHaP2_bg3ujl5tBUfc9VtGglJO9nJAHt5qcsnl4ahz_CNHFwboqENtvN4nfWJ6gvWV44fE6aIGDxmAlY-b4gnmdTvvdkxHaMeDDnox1vS1Lr9OZt7UKCU7QpAaSe_JUU3F90AQT2qkegZ-Opa_mhqkNhWKQBMlrLdQEMQ4MzGQWIeWzrKPwW14FDXlfHMjEBmCLpeDMFN52HvPy8whlrynd3JCE7omx3nHv_pyjV4OToqZbwTfuCE4HdiS7wgDDvjMjSHGLWvRtdKYk4L1qWc_JzwbBfA"
    -expiresAt: DateTimeImmutable @1747858804 {#568
      date: 2025-05-21 22:20:04.182111 Europe/Paris (+02:00)
    }
  }
  "refresh_token" => Osirisgate\Component\Tokenizer\JwtToken\Entity\RefreshToken\RefreshToken^ {#201
    #id: "682e27642d3ee"
    -isValidEntity: true
    -errorContext: []
    -userId: "user_682e27642c75c"
    -value: Osirisgate\Component\Tokenizer\JwtToken\ValueObject\RefreshTokenValue^ {#551
      -value: "b066f2df3259c9efa2f742bfb4a7ddd6"
    }
    -expiresAt: DateTimeImmutable @1748287204 {#191
      date: 2025-05-26 21:20:04.185445 Europe/Paris (+02:00)
    }
  }
]

$jwtToken = $tokenResult['token']->value();
$refreshToken = $tokenResult['refresh_token']->value();

echo "Generated JWT: " . $jwtToken . "\n";
echo "Generated Refresh Token: " . $refreshToken . "\n";

// Later, to validate the JWT:
use Osirisgate\Component\Tokenizer\JwtToken\Exception\InvalidJwtTokenException;
use Osirisgate\Component\Tokenizer\JwtToken\Exception\InvalidJwtTokenOwnerException;

try {
    $decodedPayload = $jwtTokenizer->validate($jwtToken, $userId, $payload, $header);
    print_r($decodedPayload);
    /*Osirisgate\Component\Tokenizer\JwtToken\ValueObject\TokenPayload^ {#591
      -data: array:2 [
        "email" => "user@example.com"
        "roles" => array:1 [
          0 => "ROLE_USER"
        ]
      ]
      -header: array:3 [
        "typ" => "JWT"
        "custom" => "some-value"
        "alg" => "RS256"
      ]
      -isValid: true
      -error: null
      -userId: "user_682e288a7aa0a"
    }*/

    echo "JWT is valid for user: " . $decodedPayload->userId() . "\n";
} catch (InvalidJwtTokenException $e) {
    echo "Invalid JWT: " . $e->format()['message'] . "\n";
    # Example errors:
    print_r($e->format());
    /*[
      'status' => Status::ERROR->getValue(),
      'error_code' => StatusCode::UNAUTHORIZED->getValue(),
      'message' => 'invalid.jwt.token.payload',
      'details' => [
          'error' => 'invalid.value',
          'key' => 'email',
          'value' => 'jeanq@example.com',
      ],
    ],*/
    
    /*[
      'status' => Status::ERROR->getValue(),
      'error_code' => StatusCode::UNAUTHORIZED->getValue(),
      'message' => 'invalid.jwt.token.payload',
      'details' => [
          'error' => 'unexpected.key',
          'key' => 'type',
      ],
    ]*/
    
    /*[
      'status' => Status::ERROR->getValue(),
      'error_code' => StatusCode::UNAUTHORIZED->getValue(),
      'message' => 'invalid.jwt.token.header',
      'details' => [
          'error' => 'invalid.value',
          'key' => 'custom',
          'value' => 'invalid',
      ],
    ]*/
    
    /*[
      'status' => Status::ERROR->getValue(),
      'error_code' => StatusCode::UNAUTHORIZED->getValue(),
      'message' => 'invalid.jwt.token',
      'details' => [
          'error' => 'Expired token',
      ],
    ]*/
} catch (InvalidJwtTokenOwnerException $e) {
    echo "Invalid JWT Owner: " . $e->format()['message'] . "\n";
    print_r($e->format());
    /*[
      'status' => Status::ERROR->getValue(),
      'error_code' => StatusCode::UNAUTHORIZED->getValue(),
      'message' => 'invalid.jwt.token.owner',
      'details' => [
          'user_id' => 'user_682dd421acb0cINVALID_ID',
      ],
    ]*/
}
```

### 3. Revoke a JWT token

```php
// To revoke the JWT (invalidate the refresh token):

try {
    $jwtTokenizer->revoke($userId);
} catch(UserHasNoRefreshTokenException $exception) {
    print_r($exception->format());
}
```

## 🧪 Run Tests

```bash
vendor/bin/phpunit tests
```

## 🧰 Developer Tools

### ✅ Code style

```bash
composer run phpcs-fix
```

### 🧠 Static analysis

```bash
composer run phpstan
```

Or run both checks at once:

```bash
make check-code-quality
```

## 📜 License

This package is licensed under the [MIT License](LICENSE).

## 👤 Author

**Ulrich Geraud AHOGLA** Software Engineer — Osirisgate

If you have any questions or suggestions, please contact me via [developer@osirisgate.com](mailto:developer@osirisgate.com)
