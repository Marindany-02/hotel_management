# hotel_management
# 🏨 Hotel Management System with POS Module

A web-based **Hotel Management System** built using **PHP** and **MySQL** with an integrated **Point of Sale (POS)** module. This system streamlines hotel operations including room booking, customer check-in/check-out, billing, and real-time sales tracking.

## 📌 Features

### 🏨 Hotel Management
- Room booking and availability management
- Customer check-in and check-out
- Room category, pricing, and amenities setup
- Invoice and billing management
- Admin dashboard with real-time stats

### 💳 Point of Sale (POS)
- Category-based product listing (e.g., Food, Drinks, Services)
- Add-to-cart functionality
- Dynamic receipt generation
- Order checkout and sales recording
- Stock quantity management

### 🔐 Authentication
- Admin login system
- Secure password storage (hashed)

## 🛠️ Tech Stack

- **Backend:** PHP (Procedural & OOP)
- **Database:** MySQL
- **Frontend:** HTML, CSS, Bootstrap, JavaScript
- **Tools:** XAMPP, phpMyAdmin, Git

## ⚙️ Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/Marindany-02/hotel_management.git
   ```

2. **Import the Database**
   - Open `phpMyAdmin`
   - Create a database named `hotel_management`
   - Import the `hotel_management.sql` file from the project root directory

3. **Configure Database Connection**
   - Locate the DB config file (e.g., `config/db.php`)
   - Set your DB credentials:
   ```php
   $conn = new mysqli("localhost", "root", "", "hotel_management");
   ```

4. **Run the Application**
   - Start Apache and MySQL from XAMPP
   - Open your browser and navigate to:
     ```
     http://localhost/hotel_management
     ```

## 🔑 Login Credentials

| Role   | Username | Password  |
|--------|----------|-----------|
| Admin  | admin    | 12345  |


> You can update these in the database under the `users` or `admin` table.

## 📸 Screenshots

*Coming soon: screenshots of the dashboard, POS interface, booking panel, and invoice generator.*

## ✍️ Author

**Hillary Kipngeno Marindany**  
- [GitHub](https://github.com/Marindany-02)  
- [LinkedIn](https://www.linkedin.com/in/hillary-marindany)

## 📄 License

This project is open for educational and personal use. For commercial usage, please contact the author.
