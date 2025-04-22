
# Request Management System

A PHP-based web application for managing user requests with authentication and real-time chat functionality.

## Features

- User Authentication (Login/Register)
- Request Management
- User Dashboard
- Real-time Chat
- Profile Management

## Project Structure

```
├── assets/  
│   ├── css/ (Bootstrap and custom styles)
│   ├── js/ (JavaScript scripts)
│   ├── img/ (Icons and logos)
├── includes/  
│   ├── header.php (Common header)
│   ├── footer.php (Common footer)
│   ├── db.php (MySQL connection)
│   ├── auth.php (Authentication functions)
├── classes/  
│   ├── User.php (User management class)
│   ├── Request.php (Request management class)
├── pages/  
│   ├── login.php (User login)
│   ├── register.php (User registration)
│   ├── dashboard.php (Request listing)
│   ├── profile.php (User profile)
│   ├── create_request.php (Create new requests)
│   ├── chat.php (Real-time chat)
└── config.php (Global configurations)
```

## Setup

1. Configure your database settings in `config.php`
2. Create a MySQL database named `request_system`
3. Import the database schema (if provided)
4. Start the PHP server: `php -S 0.0.0.0:8000`

## Requirements

- PHP 8.0+
- MySQL 5.7+
- Modern web browser

## Authentication

The system uses session-based authentication with the following features:
- Secure password hashing
- Session management
- Login/logout functionality
- Registration with email verification

## Database Structure

The system uses MySQL with the following main tables:
- users
- requests
- messages

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request
