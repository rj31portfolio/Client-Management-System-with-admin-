# 💼 Client Management System with Admin Panel

A complete **Client Management System** with an **Admin Panel**, built using **PHP**, **MySQLi**, **Bootstrap**, **HTML**, and **CSS**, with secure **authentication**, **Razorpay payment integration**, custom service plan creation, notifications, and report sharing features.

---

## 🚀 Features

### ✅ Authentication
- Secure login & registration (Admin & Client)
- Password hashing with bcrypt
- Forgot password (email/OTP)
- Session-based login

### 🛠 Admin Panel
- Dashboard overview with stats (clients, plans, payments)
- Manage clients (add, edit, delete)
- Create service plans (standard & custom)
- Upload project reports to specific clients
- Send notifications to clients
- Manage Razorpay payments & subscriptions
- Export client/payment data (CSV/PDF)
- View client activity logs

### 👨‍💻 Client Panel
- Dashboard with:
  - Active plan & expiry countdown
  - Notifications from admin
  - Performance reports (PDF, graphs)
  - Subscription status
- Choose/Subscribe to packages via Razorpay
- View/download invoices & payment history
- Access uploaded reports & projects

---

## 💳 Razorpay Integration
- Client selects a plan and proceeds to secure payment
- On success:
  - Plan activated & expiry date set
  - Notification sent
  - Invoice generated and saved
  - Transaction logged

---

## 🛠 Tech Stack
- **Backend:** PHP (MySQLi)
- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
- **Database:** MySQL
- **Payment Gateway:** Razorpay
- **Authentication:** Session-based + bcrypt

---
