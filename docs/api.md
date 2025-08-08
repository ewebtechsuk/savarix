# API Documentation

## Authentication
- `POST /api/login` – obtain a token by providing `email` and `password`.

Include the issued token as a Bearer token in the `Authorization` header for the requests below.

## Properties
- `GET /api/properties`
- `POST /api/properties`
- `GET /api/properties/{id}`
- `PUT /api/properties/{id}`
- `DELETE /api/properties/{id}`

## Tenancies
- `GET /api/tenancies`
- `POST /api/tenancies`
- `GET /api/tenancies/{id}`
- `PUT /api/tenancies/{id}`
- `DELETE /api/tenancies/{id}`

## Payments
- `GET /api/payments`
- `POST /api/payments`
- `GET /api/payments/{id}`
- `PUT /api/payments/{id}`
- `DELETE /api/payments/{id}`

## Webhooks
- `GET /api/webhooks`
- `POST /api/webhooks` – subscribe with `url` and `event`.
- `DELETE /api/webhooks/{id}`

Webhooks will receive POST requests when the corresponding event occurs. Currently supported event: `property.created`.
