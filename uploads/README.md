# 🩸 Blood Donor Finder System

A PHP + MySQL web application to help people find blood donors by blood group and city. Built as a college mini-project.

## Features

- Donor registration & login (with password hashing)
- Search donors by blood group and city
- Donor profile management (edit, photo upload, delete)
- Forgot password (identity verification via email + phone)
- Admin dashboard with reports (donor counts by blood group/city)
- Donation eligibility tracker (90-day rule)
- CSRF protection, input validation, secure file uploads

## Tech Stack

- **Backend:** PHP (procedural, mysqli with prepared statements)
- **Database:** MySQL
- **Frontend:** Bootstrap 5, Bootstrap Icons, custom CSS
- **Server:** Apache (via XAMPP)

## How to Run Locally

1. Install XAMPP
2. Copy this folder into C:\xampp\htdocs\
3. Start Apache and MySQL from XAMPP Control Panel
4. Open http://localhost/phpmyadmin, import database.sql
5. Visit http://localhost/blood-donor/ in your browser

## Default Login

Admin Email: admin@blooddonor.com
Admin Password: admin123

## Future Scope

- OTP-based password reset via email/SMS
- Login attempt rate-limiting
- Full donation history log
- Database indexing for faster search at scale