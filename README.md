# JUIT Alumni site — prototype

This repository contains a legacy PHP site with alumni registration and contact
forms. It is a prototype, not a verified production service. The interface
uses the JUIT name; the repository does not establish institutional
authorization, ownership, or affiliation. No user research, service metrics,
deployment, or alumni outcomes were verified.

## Current behavior

- Registration (`JUITAlumni-master/one.php` → `two.php`) stores the submitted
  roll number, name, email, mobile number, and address in MySQL.
- Contact (`contact.php` → `contactdata.php`) stores the sender name, email,
  and message in MySQL.
- The address-update form is a front-end form only; no server-side submission
  flow was found.
- `second.php` was a demo that echoed a submitted password. It is now disabled;
  the project does not implement authentication.

The form data is personal information. A database write does not imply consent,
access controls, a retention/deletion policy, or a secure operational service.
Use synthetic data for development until those owner decisions are made.

## Local setup

Requirements: PHP 8.2 or newer with `mysqli`, and MySQL 8.0 or compatible.

1. Create a database and apply the included schema:

   ```sql
   CREATE DATABASE alumni CHARACTER SET utf8mb4;
   ```

   Then run `JUITAlumni-master/database/schema.sql` against that database.
2. Create a dedicated database account for the application; do not use the
   MySQL root account. Grant it only the `INSERT` privilege on `student` and
   `contact`.
3. Provide the database settings through the PHP process environment:

   ```powershell
   $env:ALUMNI_DB_HOST = "127.0.0.1"
   $env:ALUMNI_DB_USER = "alumni_app"
   $env:ALUMNI_DB_PASSWORD = Read-Host -MaskInput "Database password"
   $env:ALUMNI_DB_NAME = "alumni"
   $env:ALUMNI_DB_PORT = "3306"
   php -S 127.0.0.1:8000 -t JUITAlumni-master
   ```

   Do not commit credentials or expose this development server to a public
   network. The application intentionally fails closed when database
   configuration is absent.

## Product and delivery gaps

Before deployment, define the intended alumni user roles, consent and data
handling, operational owner, and a measurable success criterion. Validate the
flows with representative users and document findings rather than assuming
the forms solve an established need. The legacy pages, including the address
update screen, still need accessibility, privacy, deployment, and end-to-end
review.

## Checks

Pull requests and pushes to `master` run PHP syntax checks and MySQL-backed
integration tests for form validation, safe SQL handling, persistence, and the
disabled demo endpoint.
