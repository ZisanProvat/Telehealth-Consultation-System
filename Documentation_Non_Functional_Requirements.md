# Project Documentation: Non-Functional Requirements

## 3.2.2 Non-Functional Requirements

These requirements define the quality attributes, performance constraints, and security measures of the **Web-Based Intelligent Telehealth Framework for Remote Clinical Consultations and Patient Management**.

### 1. Security
*   **Data Protection:** To ensure patient confidentiality and overall system security, all sensitive data—particularly user passwords—must be hashed using the **Bcrypt** algorithm before being stored in the database.
*   **Access Control:** The system must enforce strict role-based access control (RBAC). Critical administrative and clinical routes must be protected by middleware to ensure that users (e.g., patients) cannot access unauthorized areas (e.g., admin dashboards or clinical records of other patients).
*   **CSRF Protection:** To prevent Cross-Site Request Forgery attacks, all state-changing forms and API requests within the web application must include valid CSRF tokens.
*   **Secure API Communication:** All communication between the React frontend and the Laravel backend must be conducted over secure protocols to protect data in transit.

### 2. Performance & Efficiency
*   **Response Time:** The framework is optimized for efficiency, with a target response time where web pages and dashboard components load within **2-3 seconds** under normal network conditions.
*   **Concurrency:** The system must be capable of handling multiple concurrent users (doctors, patients, and administrative staff) accessing the database and real-time consultation features simultaneously without data corruption or performance bottlenecks.

### 3. Usability
*   **User Interface (UI):** The interface must be clean, professional, and highly intuitive. It is designed to require minimal training for doctors and staff, ensuring that the focus remains on healthcare delivery rather than navigating the software.
*   **Cross-Device Responsiveness:** The application must be fully responsive, providing a seamless experience across desktop monitors, tablets, and smartphones to facilitate consultations on various devices.

### 4. Reliability & Availability
*   **System Uptime:** As a telehealth service, the framework should be available **24/7**, allowing patients to book appointments and doctors to manage their schedules at any time.
*   **Data Integrity:** The database architecture must maintain strict referential integrity. For instance, the system must ensure that an appointment cannot exist without a valid patient and doctor record, preventing orphaned or inconsistent data.
*   **Real-time Stability:** The integration for video consultations (e.g., Jitsi/WebRTC) must remain stable during sessions to ensure uninterrupted clinical communication.
