# Security policy

## Supported version

This repository is an educational portfolio project. Security improvements are applied to the latest version on the `main` branch.

## Reporting a vulnerability

Do not publish credentials, personal information, or exploit details in a public issue. Contact the repository owner privately with the affected file, reproduction steps, impact, and a suggested remediation if available.

## Security design notes

- Database settings can be provided through environment variables.
- New passwords use PHP's `password_hash()` and `password_verify()` APIs.
- Legacy MD5 demo hashes are upgraded automatically after a successful login.
- Authentication queries use prepared statements.
- Sessions receive a new identifier after authentication.
- Uploaded files and local environment files are excluded from version control.

The application remains a learning project and has not undergone a professional penetration test. Do not deploy it to process real payments or personal information without a complete security review.
