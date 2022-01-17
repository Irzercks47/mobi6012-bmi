---
layout: page
title: Main Page
external_links:
  - title: Open App
    url: https://mobi6012-bmi.reinhart1010.id/
    icon: bi bi-github
    target: _blank
  - title: GitHub Repository
    url: https://github.com/reinhart1010/mobi6012-bmi/
    icon: bi bi-github
    target: _blank
---

# API Documentation for BMI React App (MOBI6012 project)

This is the official documentation for our [MOBI6012 - Web Design](https://curriculum.binus.ac.id/course/MOBI6012/) final project at [BINUS University](https://binus.ac.id) by:

+ 2301860053 - Reinhart Previano Koentjoro

This site is powered by [HAM](https://ham.reinhart1010.id), a Jekyll-based static wiki generator.

## Authentication
While this app doesn't implement all of the OAuth 2.0's specifications, all authentication process are done through passing [JSON Web Tokens (JWT)](https://jwt.io).

### Creating a new account (Sign Up)
```sh
curl -X POST https://api.mobi6012-bmi.reinhart1010.id/account.php \
  -F 'action=SIGN_UP' \
  -F 'name=John Doe' \
  -F 'email=user@example.com' \
  -F 'password=secret'
```

Body parameters are retrieved through `form-data`, which may consist of:

| Parameter | Required | Description |
|---|:-:|---|
| `action` | ✅ (**must** be `SIGN_UP`) | Signals the endpoint that the user would like to sign up. |
| `name` | ✅ | The user's name. |
| `email` | ✅ | The user's email address. |
| `password` | ✅ | The user's password. While the application backend hashes the password through bcrypt, it is recommended to hash the password first for enhanced security. |

**Valid response code:**

```js
{
  "status": "OK",   // Must be "OK"
  "data": "eyJ0..." // The JWT payload
}
```

### Log in to the account
#### Refresh the user's JWT
If a valid JWT is passed on to the endpoint, such as this:

```sh
curl -X POST https://api.mobi6012-bmi.reinhart1010.id/account.php \
  -F 'action=LOG_IN' \
  -H 'Authorization: Bearer ${TOKEN}'
```

The endpoint will generate a new JWT token to extend the user's session time on the app.

Body parameters are retrieved through `form-data`, which may consist of:

| Parameter | Required | Description |
|---|:-:|---|
| `action` | ✅ (**must** be `LOG_IN`) | Signals the endpoint that the user would like to log in. |

**Valid response code:**

```js
{
  "status": "OK",   // Must be "OK"
  "data": "eyJ0..." // The JWT payload
}
```

#### Log in with email and password
Or else, the endpoint will try to authenticate a user through email and password.

```sh
curl -X POST https://api.mobi6012-bmi.reinhart1010.id/account.php \
  -F 'action=LOG_IN' \
  -F 'email=user@example.com' \
  -F 'password=secret'
```

Body parameters are retrieved through `form-data`, which may consist of:

| Parameter | Required | Description |
|---|:-:|---|
| `action` | ✅ (**must** be `LOG_IN`) | Signals the endpoint that the user would like to log in. |
| `email` | ✅ | The user's email address. |
| `password` | ✅ | The user's password. While the application backend hashes the password through bcrypt, it is recommended to hash the password first for enhanced security. |

**Valid response code:**

```js
{
  "status": "OK",   // Must be "OK"
  "data": "eyJ0..." // The JWT payload
}
```

#### Get the user's BMI reports

```sh
curl -X GET https://api.mobi6012-bmi.reinhart1010.id/reports.php \
  -H 'Authorization: Bearer ${TOKEN}'
```

Additionally, the endpoint also accepts the following parameters (set inside the `form-data`)

| Parameter | Required | Description |
|---|:-:|---|
| `items` | 🚫 (Defaults to `15`) | The number of items shown per page. |
| `page` | 🚫 (Defaults to `1`) | The page number. |

**Valid response code:**

```js
{
  "status": "OK",   // Must be "OK"
  "data": [
    {
      "id": 1,                                    // The report ID
      "user_id": 2,                               // The user ID
      "height": 165,                              // 165 cm (1.65 m)
      "weight": 555,                              // 55.5 kg
      "timestamp_created": "2021-11-11 12:34:56", // In UTC and SQL format
      "timestamp_updated": "2021-11-11 12:34:56"  // In UTC and SQL format
    }
  ]
}
```

#### Add a new BMI report

```sh
curl -X POST https://api.mobi6012-bmi.reinhart1010.id/reports.php \
  -H 'Authorization: Bearer ${TOKEN}'
```

The endpoint requires the following parameters (set inside the `form-data`)

| Parameter | Required | Description |
|---|:-:|---|
| `height` | ✅ | The user's height in centimeters. |
| `weight` | ✅ | The user's height in hectograms (1/10 kilograms). For example, `487` for 48.7 kilograms. |

**Valid response code:**

```js
{
  "status": "OK",   // Must be "OK"
  "data": {
    "id": 1,                                    // The report ID
    "user_id": 2,                               // The user ID
    "height": 165,                              // 165 cm (1.65 m)
    "weight": 555,                              // 55.5 kg
    "timestamp_created": "2021-11-11 12:34:56", // In UTC and SQL format
    "timestamp_updated": "2021-11-11 12:34:56"  // In UTC and SQL format
  }
}
```
