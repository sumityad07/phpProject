# Student Development & Employability Portal 🎓

A full-stack, AI-powered academic management system designed to track student performance, manage attendance, and provide predictive insights using the **Google Gemini API**. Built with PHP, MySQL, and a premium Glassmorphism UI aesthetic.

## ✨ Features

* **Multi-Role Authentication:** Secure login and registration for Students, Faculty, and Administrators.
* **Smart Dashboard:** Role-specific dashboards presenting relevant academic metrics and actions.
* **Attendance Tracking:** Faculty and Admins can log daily attendance; Students can view their attendance history.
* **Assessment Management:** Faculty and Admins can upload marks for quizzes, midterms, and finals.
* **AI Performance Analytics (Gemini):** Integrates with the Google Gemini API to analyze student data and provide real-time, actionable insights, predictions, and risk factor identification.
* **Admin Control Panel:** Dedicated panel to manage system configurations, dynamically update the AI API key, and oversee system health.
* **Premium UI/UX:** Responsive, modern interface featuring glassmorphism, dynamic data visualization (Chart.js), and CSS micro-animations.

## 🛠️ Technology Stack

* **Frontend:** HTML5, Vanilla CSS (Glassmorphism), JavaScript (Chart.js)
* **Backend:** PHP 8+
* **Database:** MySQL (PDO for secure database interactions)
* **AI Integration:** Google Gemini REST API (`gemini-2.5-flash`)

---

## 🚀 Setup & Installation Instructions

Follow these steps to run the project locally on your machine using **XAMPP** (or any similar LAMP/WAMP stack).

### 1. Prerequisite Setup
1. Download and install [XAMPP](https://www.apachefriends.org/).
2. Start the **Apache** and **MySQL** modules from the XAMPP Control Panel.
3. Clone or download this repository into your XAMPP `htdocs` folder:
   ```bash
   # Your path might be C:\xampp\htdocs\sidhiProject
   git clone https://github.com/sumityad07/phpProject.git sidhiProject
   ```

### 2. Database Configuration
1. Open your browser and go to `http://localhost/phpmyadmin`.
2. Create a new database named **`student_portal`**.
3. Import the `database.sql` file provided in the root directory into this new database to set up the necessary tables (`users`, `students`, `attendance`, `marks`).

### 3. API Key Configuration
To enable the AI features, you need a Google Gemini API key.
1. Obtain an API key from [Google AI Studio](https://aistudio.google.com/).
2. You can configure this key in two ways:
   * **Method 1 (UI):** Register an Admin account in the application, log into the Admin Dashboard, and paste your API key in the configuration panel.
   * **Method 2 (Manual):** Open `includes/config.php` and replace `'YOUR_GEMINI_API_KEY_HERE'` with your actual key.

### 4. Magic Setup Script
To ensure your database is perfectly configured and to add test data quickly:
1. Open your browser and navigate to:
   ```
   http://localhost/sidhiProject/setup.php
   ```
2. This script will automatically:
   * Ensure your database supports the `admin` role.
   * Seed **Mock Attendance Data** for all registered students.
   * Run a diagnostic test on your Gemini API key to confirm it is working (`HTTP 200 OK`).

### 5. Running the Application
1. Go to `http://localhost/sidhiProject/index.php`.
2. Click **Login** or **Register** to create a Student, Faculty, or Admin account.
3. Explore the dashboards, mark attendance, and generate AI reports!

## 📂 Project Structure
* `/api/` - Backend API endpoints for Authentication, Actions, and Gemini Integration.
* `/assets/` - CSS files and JavaScript (Chart.js configurations).
* `/includes/` - Reusable components (Header, Footer, DB connection, Config).
* Root files (`*.php`) - The primary frontend pages (Dashboards, Login, Register, Attendance, Marks).
