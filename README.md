# Academy System 🎓
> **Comprehensive Educational Management Platform**

**Academy System** is a professional full-stack web application designed to streamline the management of educational institutions. It provides a unified dashboard for administrators, instructors, and students to manage courses, track academic progress, and enhance the overall learning experience through an intuitive interface.

---

## 🚀 Key Features
* **User Authentication:** Secure login and registration system for students and administrators.
* **Dynamic Dashboard:** Real-time statistics and data management for administrative tasks.
* **Course Management:** Full **CRUD** operations (Create, Read, Update, Delete) for lessons and courses.
* **Database Integration:** Robust data handling using **MySQL** with optimized relational schemas.
* **Responsive Design:** Fully optimized for all screen sizes (Mobile, Tablet, and Desktop).
* **Interactive UI:** Seamless user experience powered by **Vanilla JavaScript**.

---

## 🛠 Tech Stack
* **Frontend:** HTML5, CSS3, JavaScript (ES6+).
* **Backend:** PHP (Server-side logic).
* **Database:** MySQL.
* **Deployment:** Live version hosted at [academy-system.page.gd](https://academy-system.page.gd).

---

## 📸 Project Preview
| Desktop Interface | Mobile Interface |
| :--- | :--- |
| ![Desktop View](https://via.placeholder.com/400x250?text=Desktop+Preview) | ![Mobile View](https://via.placeholder.com/150x250?text=Mobile+Preview) |

---

## ⚙️ Installation & Setup
To run this project locally, follow these steps:

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/your-username/academy-system.git](https://github.com/your-username/academy-system.git)
    ```
2.  **Environment Setup:**
    * Move the project folder to your local server directory (e.g., `htdocs` for XAMPP).
3.  **Database Configuration:**
    * Open **phpMyAdmin** and create a database named `academy_db`.
    * Import the `.sql` file found in the `/database` directory.
    * Update your connection settings in the `config.php` file:
    ```php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "academy_db";
    ```
4.  **Launch:**
    * Navigate to `http://localhost/academy-system` in your browser.
