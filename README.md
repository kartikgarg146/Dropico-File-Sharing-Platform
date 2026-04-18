# 📦 Dropico — Secure File Sharing Platform

> A lightweight, self-hosted file sharing system built with PHP & MySQL. Upload files, generate unique share links, and track downloads — all from a clean, modern dashboard.

---

## ✨ Features

- 🔐 **Secure Authentication** — User registration and login with session management
- 📁 **File Uploads** — Support for PDF, DOCX, PNG, JPG, and TXT formats
- 🔗 **Unique Share Links** — Auto-generated token-based URLs for every uploaded file
- 📊 **Download Counter** — Track how many times each file has been downloaded
- 🗑️ **Owner-Only Deletion** — Only the uploader can delete their own files
- 💅 **Modern UI** — Responsive design with clean gradients, works on all screen sizes

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8+ |
| Database | MySQL |
| Frontend | HTML5 / CSS3 |
| Local Environment | XAMPP / WAMP |

---

## 📂 Project Structure

```
uploads/
├── .gitkeep          # Keeps the uploads directory tracked by Git
├── .gitignore        # Ignores uploaded user files from version control
├── README.md         # You are here
├── index.php         # Main dashboard — lists user's uploaded files
├── register.php      # User registration page
├── logout.php        # Session termination
├── download.php      # Handles token-based file downloads & counter increment
├── welcome.php       # Landing / welcome page for guests
└── style.css         # Global stylesheet
```

---

## 🚀 Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) or [WAMP](https://www.wampserver.com/) installed
- PHP 8.0 or higher
- MySQL 5.7 or higher

### Installation

1. **Clone the repository** into your local server's web root:

   ```bash
   # For XAMPP
   git clone https://github.com/yourusername/dropico.git C:/xampp/htdocs/dropico

   # For WAMP
   git clone https://github.com/yourusername/dropico.git C:/wamp64/www/dropico
   ```

2. **Create the database:**

   Open phpMyAdmin (`http://localhost/phpmyadmin`) and run the following SQL:

   ```sql
   CREATE DATABASE dropico;
   USE dropico;

   CREATE TABLE users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     username VARCHAR(50) NOT NULL UNIQUE,
     email VARCHAR(100) NOT NULL UNIQUE,
     password VARCHAR(255) NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );

   CREATE TABLE files (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT NOT NULL,
     original_name VARCHAR(255) NOT NULL,
     stored_name VARCHAR(255) NOT NULL,
     token VARCHAR(64) NOT NULL UNIQUE,
     downloads INT DEFAULT 0,
     uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
   );
   ```

3. **Configure the database connection:**

   Update your DB credentials in the relevant PHP files (or a central `config.php` if you have one):

   ```php
   $host = 'localhost';
   $dbname = 'dropico';
   $username = 'root';
   $password = '';
   ```

4. **Ensure the `uploads/` directory is writable:**

   ```bash
   chmod 755 uploads/   # Linux/macOS
   ```

5. **Start Apache & MySQL** from your XAMPP/WAMP control panel, then visit:

   ```
   http://localhost/dropico/welcome.php
   ```

---

## 🔗 How Share Links Work

Each uploaded file is assigned a cryptographically random token. The share link looks like:

```
http://localhost/dropico/download.php?token=a3f9c2e1b7...
```

- Anyone with this link can download the file
- The download counter increments on each access
- Only the file owner can delete it from their dashboard

---

## 🔒 Security Notes

- Passwords are hashed using `password_hash()` (bcrypt)
- File tokens are generated with `bin2hex(random_bytes(32))`
- Uploaded files are stored with randomized names to prevent direct access guessing
- Only whitelisted file types are accepted (PDF, DOCX, PNG, JPG, TXT)

> **Note:** This project is intended for local/development use. For production deployment, add HTTPS, configure proper file permissions, and consider rate limiting on upload/download endpoints.

---

## 🤝 Contributing

Contributions are welcome! Feel free to open an issue or submit a pull request.

1. Fork the project
2. Create your feature branch: `git checkout -b feature/cool-feature`
3. Commit your changes: `git commit -m 'Add cool feature'`
4. Push to the branch: `git push origin feature/cool-feature`
5. Open a Pull Request

---

<p align="center">Built with ☕ and PHP — <em>Dropico</em></p>
