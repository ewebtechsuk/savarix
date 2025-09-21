# API Documentation

## Authentication
- `POST /api/login` – obtain a token by providing `email` and `password`.

Include the issued token as a Bearer token in the `Authorization` header for the requests below.

## Contacts
- `GET /api/contacts`
- `POST /api/contacts`
- `GET /api/contacts/{id}`
- `PUT /api/contacts/{id}`
- `DELETE /api/contacts/{id}`

Contacts represent landlords, tenants, applicants and contractors. When creating a record the
payload must include the `type` (e.g. `tenant`, `landlord`) and `name`; optional fields such as
`email`, `phone`, `address`, `notes`, `first_name` and `last_name` are accepted.

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

Payments belong to a tenancy (`tenancy_id`) and capture the `amount`, optional `status`
(`pending`, `completed`, etc.) and any third-party reference (`stripe_reference`).

## Webhooks
- `GET /api/webhooks`
- `POST /api/webhooks` – subscribe with `url` and `event`.
- `DELETE /api/webhooks/{id}`

Webhooks will receive POST requests when the corresponding event occurs. Currently supported event: `property.created`.
