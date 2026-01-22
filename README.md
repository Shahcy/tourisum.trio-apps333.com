# Travel SaaS (Filament)

Multi-tenant SaaS with a super admin panel and a tenant portal.

## Requirements

- PHP 8.2+
- Composer
- Node.js (for asset builds)
- SQLite (default) or MySQL/Postgres
- GD extension enabled (for logo palette extraction)

## Local Setup

1) Install dependencies:
`
composer install
npm install
`

2) Copy env and set app domain:
`
copy .env.example .env
`
Edit .env:
- APP_URL=http://127.0.0.1:8000
- APP_DOMAIN=127.0.0.1
- ADMIN_SUBDOMAIN=admin

3) Storage link:
`
php artisan storage:link
`

4) Migrate:
`
php artisan migrate
`

5) Build assets:
`
npm run build
`

6) Run:
`
php artisan serve
`

## Panels

- Admin: /admin
- Portal: /portal/{tenant-domain}

## Tenant Branding

- Upload a company logo to auto-extract colors (GD required).
- Primary/secondary/accent colors are stored on the tenant record.
- Theme is applied automatically per tenant.

## Super Admin Access

- Only super_admin can access the admin panel and manage companies.
- Use the Users screen to create company admins and assign roles.

## Impersonation

- Super admin can impersonate a tenant admin.
- Use the "Back to Super Admin" action to exit.

## Notes

- Make sure GD is enabled in php.ini (extension=gd).
- If logo colors do not update, re-upload the logo after GD is enabled.
