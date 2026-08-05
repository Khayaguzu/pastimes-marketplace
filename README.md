# Pastimes Marketplace

Pastimes is a PHP and MySQL marketplace for buying and selling pre-owned clothing. It demonstrates full-stack web development through account approval, product listings, image uploads, a shopping cart, checkout, order history, messaging, and administrative workflows.

> This is an educational portfolio project. It uses simulated payments and must not be deployed to process real payment details or personal information without a complete security review.

## Main features

- User registration, login, account approval, and suspension
- Seller product submission with administrative moderation
- Product browsing, filtering, and detail pages
- Shopping cart and checkout workflow
- Buyer order history
- User and administrator messaging
- Administrative dashboards for users, products, and orders
- Responsive layouts for desktop and mobile screens

## Technology

- PHP 7.4 or later
- MySQL 5.7 or later
- HTML5, CSS3, and JavaScript
- MySQLi prepared statements for corrected authentication flows
- Font Awesome icons and Google Fonts

## Security improvements

The corrected portfolio version includes:

- Modern password hashing through `password_hash()` and `password_verify()`
- Automatic upgrading of legacy MD5 demo hashes after a valid login
- Prepared statements in registration and authentication flows
- Session identifier regeneration after successful authentication
- Generic database and authentication errors
- Environment-based database configuration
- Exclusion of uploaded files, local credentials, reports, and development metadata from Git
- A documented security-reporting process

The remaining application should still receive a complete review of every state-changing request, upload path, and database query before any production use.

## Local setup

### Requirements

- WAMP, XAMPP, or another Apache, PHP, and MySQL environment
- PHP 7.4 or later with the MySQLi extension
- MySQL 5.7 or later

### Installation

1. Clone the repository into the web server document directory.

```bash
git clone https://github.com/Khayaguzu/pastimes-marketplace.git
```

2. Create a MySQL database named `clothingstore`.

3. Import `myClothingStore.sql` through phpMyAdmin or the MySQL command line.

```bash
mysql -u root -p clothingstore < myClothingStore.sql
```

4. Configure the database with environment variables when your server supports them:

```text
PASTIMES_DB_HOST=localhost
PASTIMES_DB_USER=root
PASTIMES_DB_PASSWORD=your_password
PASTIMES_DB_NAME=clothingstore
```

For a default local WAMP or XAMPP installation, the application falls back to `localhost`, `root`, an empty password, and `clothingstore`.

5. Ensure the `uploads` directory is writable by the development web server.

6. Open `http://localhost/pastimes/` in a browser.

## Project structure

```text
pastimes-marketplace/
├── css/                    # Shared styling
├── includes/               # Shared header and footer
├── uploads/                # Runtime product images, excluded from Git
├── admin_*.php             # Administration workflows
├── cart.php                # Shopping cart
├── checkout.php            # Checkout details
├── DBConn.php              # Database configuration
├── login.php               # User authentication
├── messages.php            # User messaging
├── order_history.php       # Previous purchases
├── register.php            # Account creation
├── shop.php                # Product catalogue
├── upload-item.php         # Seller listing workflow
└── myClothingStore.sql     # Database schema and sample data
```

## Recruiter walkthrough

1. Register a user and review the pending-account workflow.
2. Approve the account from the administrator dashboard.
3. Upload and approve a clothing item.
4. Browse the catalogue and add the item to the cart.
5. Complete the simulated checkout and inspect the order history.
6. Review the messaging and administrative management features.

## Known limitations

- Checkout is a demonstration and does not connect to a payment provider.
- Some older database operations still require conversion to prepared statements.
- CSRF protection and centralized authorization middleware remain roadmap items.
- Uploaded images require further defence-in-depth controls for production deployment.
- Automated integration tests are not yet included.

## Roadmap

- Convert every database operation to prepared statements
- Add CSRF tokens to all state-changing forms
- Centralize authorization and output escaping helpers
- Add PHPUnit integration tests
- Add Docker-based local development
- Add screenshots and a short demonstration video

## Responsible use

Use only synthetic data during evaluation. Do not enter real card numbers, passwords used elsewhere, or sensitive personal information.

## Contributors

- Khaya Guzu
- Naoyuki Higaki
