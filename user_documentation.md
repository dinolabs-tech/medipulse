# MediPulse - User Documentation

This document provides a detailed overview of the MediPulse Pharmacy Management System, outlining the functionalities available to different user roles.

## 1. System Overview

MediPulse is a comprehensive system designed to manage pharmacy operations, including inventory, sales, patient records, prescriptions, and user management. It aims to streamline daily tasks and provide insights into pharmacy performance.

## 2. User Roles and Permissions

MediPulse defines several user roles, each with specific access levels and responsibilities to ensure secure and efficient operation.

### 2.1. Superuser

The Superuser role has the highest level of access and control over the entire system. This role is typically reserved for system administrators and is not managed through the standard user interface.

**Key Responsibilities & Features:**
*   **Full Administrative Control:** Access to all system features and configurations.
*   **User Management:** Can create, modify, and delete all user accounts, including other Admin users.
*   **System Configuration:** Manage core system settings and database configurations.
*   **Audit Log Access:** Full access to all system action logs.

### 2.2. Admin

The Admin role has extensive privileges, allowing them to manage most aspects of the pharmacy's operations and user accounts (excluding Superusers).

**Key Responsibilities & Features:**
*   **Dashboard Access:** View key performance indicators (KPIs) such as total profit, total sales count, total quantity sold, inventory sum, expected profit, total unique medicines, and total patients. Also view low stock, out of stock, expired, and soon-to-expire products, and recent sales.
*   **User Management:**
    *   **Add New Users:** Create new user accounts for Pharmacists, Assistants, and Cashiers.
    *   **Edit Existing Users:** Modify usernames, passwords, and roles of existing users (excluding Superusers).
    *   **Delete Users:** Remove user accounts (excluding Superusers).
*   **Medicine Management:**
    *   **Add New Medicines:** Register new pharmaceutical products with details like name, description, quantity, cost price, selling price, batch number, and expiry date.
    *   **Edit Existing Medicines:** Update details of existing medicines, including stock levels and pricing.
    *   **Delete Medicines:** Remove medicine records from the system.
*   **Patient Management:**
    *   **Add New Patients:** Register new patient records with personal and insurance details.
    *   **Edit Existing Patients:** Update patient information.
    *   **Delete Patients:** Remove patient records.
*   **Prescription Management:**
    *   **Add New Prescriptions:** Create new prescription records, linking patients and medicines.
    *   **Edit Existing Prescriptions:** Modify prescription details.
    *   **Delete Prescriptions:** Remove prescription records.
*   **Sales Management (Point of Sale - POS):**
    *   **Add Items to Cart:** Select medicines and specify quantities for sale.
    *   **Update Cart Items:** Adjust quantities of items in the cart.
    *   **Remove Items from Cart:** Delete items from the current sale.
    *   **Checkout:** Complete sales transactions, update inventory, and generate receipts.
    *   **View Sales History:** Access records of past sales.
*   **Supplier Management:** (Assumed based on `suppliers.php` file, but not explicitly reviewed) Manage supplier information.
*   **Purchase Order Management:** (Assumed based on `purchase_orders.php` file, but not explicitly reviewed) Create and manage purchase orders for medicines.
*   **Logging:** All actions performed by an Admin are logged for auditing purposes.

### 2.3. Pharmacist

The Pharmacist role is primarily responsible for managing medicines, patient prescriptions, and assisting with sales.

**Key Responsibilities & Features:**
*   **Dashboard Access:** Likely has access to view key performance indicators (KPIs) and inventory alerts (low stock, out of stock, expired, soon-to-expire products).
*   **Medicine Management:**
    *   **Add New Medicines:** Register new pharmaceutical products.
    *   **Edit Existing Medicines:** Update medicine details, especially stock and expiry dates.
    *   **Delete Medicines:** Remove medicine records.
*   **Patient Management:**
    *   **Add New Patients:** Register new patient records.
    *   **Edit Existing Patients:** Update patient information.
    *   **Delete Patients:** Remove patient records.
*   **Prescription Management:**
    *   **Add New Prescriptions:** Create new prescription records.
    *   **Edit Existing Prescriptions:** Modify prescription details.
    *   **Delete Prescriptions:** Remove prescription records.
*   **Sales Management (Point of Sale - POS):**
    *   **Add Items to Cart:** Select medicines and specify quantities for sale.
    *   **Update Cart Items:** Adjust quantities of items in the cart.
    *   **Remove Items from Cart:** Delete items from the current sale.
    *   **Checkout:** Complete sales transactions, update inventory, and generate receipts.
    *   **View Sales History:** Access records of past sales.
*   **Logging:** All actions performed by a Pharmacist are logged.

### 2.4. Assistant

The Assistant role supports the pharmacy operations, focusing on patient and prescription management, and potentially sales.

**Key Responsibilities & Features:**
*   **Dashboard Access:** May have limited access to view general dashboard information, but not sensitive financial data.
*   **Patient Management:**
    *   **Add New Patients:** Register new patient records.
    *   **Edit Existing Patients:** Update patient information.
    *   **Delete Patients:** Remove patient records.
*   **Prescription Management:**
    *   **Add New Prescriptions:** Create new prescription records.
    *   **Edit Existing Prescriptions:** Modify prescription details.
    *   **Delete Prescriptions:** Remove prescription records.
*   **Sales Management (Point of Sale - POS):**
    *   **Add Items to Cart:** Select medicines and specify quantities for sale.
    *   **Update Cart Items:** Adjust quantities of items in the cart.
    *   **Remove Items from Cart:** Delete items from the current sale.
    *   **Checkout:** Complete sales transactions, update inventory, and generate receipts.
*   **Logging:** All actions performed by an Assistant are logged.

### 2.5. Cashier

The Cashier role is primarily focused on handling sales transactions.

**Key Responsibilities & Features:**
*   **Dashboard Access:** May have limited access to view general dashboard information, primarily related to sales.
*   **Sales Management (Point of Sale - POS):**
    *   **Add Items to Cart:** Select medicines and specify quantities for sale.
    *   **Update Cart Items:** Adjust quantities of items in the cart.
    *   **Remove Items from Cart:** Delete items from the current sale.
    *   **Checkout:** Complete sales transactions, update inventory, and generate receipts.
*   **Logging:** All actions performed by a Cashier are logged.

## 3. General User Interface and Navigation

The MediPulse system features a user-friendly interface with a sidebar for navigation and a main content area for displaying information and forms.

*   **Sidebar:** Provides quick access to different modules like Dashboard, Users, Medicines, Patients, Prescriptions, Sales, Sales History, Purchase Orders, Suppliers, and Logs. The visible options in the sidebar will vary based on the user's role.
*   **Navbar:** Contains user-specific information, notifications, and logout options.
*   **Forms:** Used for adding and editing records (e.g., users, medicines, patients, prescriptions).
*   **Tables:** Display lists of records with options for editing and deleting.
*   **Search Functionality:** Available in sales for searching medicines and patients.

## 4. Important Notes

*   **Session Management:** The system uses secure session management (`secure_session.php`) to protect user data and maintain login status.
*   **Action Logging:** All significant user actions are logged (`components/functions.php`) for auditing and accountability.
*   **Database Interaction:** The system interacts with a MySQL database (`database/db_connection.php`) to store and retrieve all operational data.
*   **Receipt Generation:** Upon successful checkout, a receipt can be generated (`generate_receipt.php`).
