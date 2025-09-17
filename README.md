# Laravel Modular RBAC Panel

A modular **Role-Based Access Control (RBAC)** admin panel built with Laravel, supporting modules, roles, permissions, and customer-facing APIs including cart, orders, and payments.

---

## 📦 Features

* Modular architecture for scalability and maintainability
* Role-Based Access Control (RBAC) for admins
* User management (Admins, Customers)
* Customer APIs:

    * Authentication & Registration via OTP
    * Profile management
    * Address management
    * Cart management
    * Order management (auto invoice and payment)
    * Payment management
    * Bank webhook handling
* Admin panel with CRUD operations for all entities
* Postman API collection included for easy testing

---

## ⚡ Installation

1. Clone the repository:

```bash
git clone https://github.com/Erfan-Mohamadi/laravel-modular-rbac-panel.git
cd laravel-modular-rbac-panel
```

2. Install dependencies:

```bash
composer install
npm install
```

3. Copy `.env.example` to `.env` and configure your environment variables:

```bash
cp .env.example .env
```

4. Generate the application key:

```bash
php artisan key:generate
```

5. Run migrations and seed the database:

```bash
php artisan migrate --seed
```

6. Serve the application:

```bash
php artisan serve
```

---

## 📞 Modules

* **Admin**: Manage users, roles, permissions
* **Customer**: APIs for registration, profile, cart, order, and payment
* **Order**: Handles orders, invoices, and payment integration
* **Store**: Product management
* **Others**: Notifications, shipping, etc.

---

## 📬 Customer API (Postman Collection)

This project includes a complete **Customer API** for authentication, profile, address, cart, order, and payment management. You can import the Postman collection to quickly test all endpoints:

[Download Postman Collection](postman/Customer%20API.postman_collection.json)

### 🔹 Base URL

```
{{base_url}} = http://localhost:8000/api
```

---

### 1️⃣ Authentication

| Endpoint                        | Method | Description                      | Body / Headers                                                                                   |
| ------------------------------- | ------ | -------------------------------- | ------------------------------------------------------------------------------------------------ |
| `/customer/send-otp`            | POST   | Send OTP to mobile               | `{ "mobile": "09999999999" }`                                                                    |
| `/customer/verify-otp-register` | POST   | Verify OTP and register customer | `{ "customer_id": {{customer_id}}, "otp": "380338", "name": "Ali md", "password": "secret123" }` |
| `/customer/resend-otp`          | POST   | Resend OTP                       | `{ "customer_id": {{customer_id}} }`                                                             |
| `/customer/login`               | POST   | Customer login                   | `{ "mobile": "09999123259", "password": "secret123" }`                                           |
| `/customer/logout`              | POST   | Logout                           | Header: `Authorization: Bearer {{token}}`                                                        |

---

### 2️⃣ Location Data

| Endpoint                                     | Method | Description            |
| -------------------------------------------- | ------ | ---------------------- |
| `/customer/provinces`                        | GET    | Get all provinces      |
| `/customer/provinces/{{province_id}}/cities` | GET    | Get cities by province |
| `/customer/cities`                           | GET    | Get all cities         |

---

### 3️⃣ Customer Profile

| Endpoint            | Method | Description    | Body / Headers                                        |
| ------------------- | ------ | -------------- | ----------------------------------------------------- |
| `/customer/profile` | GET    | View profile   | Header: `Authorization: Bearer {{token}}`             |
| `/customer/profile` | PUT    | Update profile | `{ "name": "Ali Ahmad", "email": "ali@example.com" }` |

---

### 4️⃣ Address Management

| Endpoint                             | Method | Description           | Body / Headers                                                                                                                                             |
| ------------------------------------ | ------ | --------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `/customer/addresses`                | GET    | List all addresses    | Header: `Authorization: Bearer {{token}}`                                                                                                                  |
| `/customer/addresses`                | POST   | Add new address       | `{ "title": "Home", "province_id": 1, "city_id": 1, "district": "District 1", "postal_code": "1234567890", "address_line": "123 Main Street, Apt 4B" }`    |
| `/customer/addresses/{{address_id}}` | GET    | View specific address | Header: `Authorization: Bearer {{token}}`                                                                                                                  |
| `/customer/addresses/{{address_id}}` | PUT    | Update address        | `{ "title": "Work", "province_id": 1, "city_id": 2, "district": "District 2", "postal_code": "0987654321", "address_line": "456 Business Ave, Suite 10" }` |
| `/customer/addresses/{{address_id}}` | DELETE | Delete address        | Header: `Authorization: Bearer {{token}}`                                                                                                                  |

---

### 5️⃣ Cart Management

| Endpoint                     | Method | Description         | Body / Headers                            |
| ---------------------------- | ------ | ------------------- | ----------------------------------------- |
| `/customer/cart`             | GET    | List all cart items | Header: `Authorization: Bearer {{token}}` |
| `/customer/cart`             | POST   | Add item to cart    | `{ "product_id": 11, "quantity": 2 }`     |
| `/customer/cart/{{cart_id}}` | GET    | View cart item      | Header: `Authorization: Bearer {{token}}` |
| `/customer/cart/{{cart_id}}` | PUT    | Update cart item    | `{ "quantity": 2 }`                       |
| `/customer/cart/{{cart_id}}` | DELETE | Remove cart item    | Header: `Authorization: Bearer {{token}}` |

---

### 6️⃣ Order Management

| Endpoint                                       | Method | Description                           | Body / Headers                                                     |
| ---------------------------------------------- | ------ | ------------------------------------- | ------------------------------------------------------------------ |
| `/customer/orders`                             | GET    | List all orders                       | Header: `Authorization: Bearer {{token}}`                          |
| `/customer/orders`                             | POST   | Create order (auto-invoice + payment) | `{ "shipping_id": {{shipping_id}}, "address_id": {{address_id}} }` |
| `/customer/orders/{{order_id}}`                | GET    | Order details                         | Header: `Authorization: Bearer {{token}}`                          |
| `/customer/orders/{{order_id}}/retry-payment`  | POST   | Retry failed payment                  | Header: `Authorization: Bearer {{token}}`                          |
| `/customer/orders/{{order_id}}/payment-status` | GET    | Get payment status                    | Header: `Authorization: Bearer {{token}}`                          |
| `/customer/orders/{{order_id}}`                | DELETE | Cancel unpaid order                   | Header: `Authorization: Bearer {{token}}`                          |

---

### 7️⃣ Payment Management

| Endpoint                                    | Method | Description              | Body / Headers                             |
| ------------------------------------------- | ------ | ------------------------ | ------------------------------------------ |
| `/customer/payments`                        | GET    | List all payments        | Header: `Authorization: Bearer {{token}}`  |
| `/customer/payments/{{payment_id}}`         | GET    | Payment details          | Header: `Authorization: Bearer {{token}}`  |
| `/customer/payments/process/{{invoice_id}}` | POST   | Process payment manually | Header: `Authorization: Bearer {{token}}`  |
| `/customer/payments/verify`                 | POST   | Verify payment           | `{ "transaction_id": {{transaction_id}} }` |

---

### 8️⃣ Bank Webhook (Public)

| Endpoint                     | Method | Description                                      |                                                                                                                                               |
| ---------------------------- | ------ | ------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------- |
| `/customer/payments/webhook` | POST   | Bank payment callback. Public endpoint (no auth) | `{ "transaction_id": {{transaction_id}}, "status": "success", "tracking_code": "123456789012", "message": "Payment completed successfully" }` |

---

### 🔑 Variables

* `{{token}}` – Bearer token after login
* `{{customer_id}}` – Generated after OTP verification
* `{{cart_id}}` – Cart item ID
* `{{address_id}}` – Customer address ID
* `{{province_id}}` – Province ID for filtering cities
* `{{order_id}}` – Order ID
* `{{payment_id}}` – Payment ID
* `{{invoice_id}}` – Invoice ID
* `{{transaction_id}}` – Transaction ID for payment verification
* `{{shipping_id}}` – Shipping method ID
