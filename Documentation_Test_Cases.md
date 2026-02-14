# 8.1 Test Cases

A set of conditions or inputs designed to check whether a software feature or function works correctly or not is called a test case. A test case includes test data, execution steps, and the expected outcome. These test cases are used to verify that the system meets its requirements, identify any bugs, and ensure that the software is reliable and functions correctly.

### Table 8.1 Test Case 1: Patient Registration

| Test Case ID: 1 | Test Designed By: Antigravity |
| :--- | :--- |
| **Test Priority:** High | **Test Designed Date:** 17-01-2026 |
| **Module Name:** Patient Management | **Test Executed By:** Sabikun Nahar Lima |
| **Test Title:** New Patient Registration Verification | |
| **Description:** Verify that a new patient can successfully register with valid details. | |
| **Pre-Condition:** The patient must not already have a verified account with the same email. | |
| **Dependencies:** Database Connection, Mail Server (SMTP) | |

| Step | Test Steps | Test Data | Expected Result | Actual Result | Status | Note |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Navigate to Registration Page | URL: `/register` | Registration form is displayed. | Success | Pass | |
| 2 | Enter valid name starting with capital | Name: "Akash Ahmed" | Field accepts input. | Success | Pass | Matches regex `/^[A-Z]/` |
| 3 | Enter valid 11-digit phone number | Phone: "01712345678" | Field accepts input. | Success | Pass | |
| 4 | Enter valid email and address | Email: "akash@example.com", Address: "Dhaka" | Fields accept input. | Success | Pass | |
| 5 | Enter age, height, weight, and blood group | Age: 25, H: 170, W: 65, BG: "A+" | Fields accept input. | Success | Pass | |
| 6 | Enter password and confirm | Pass: "Akash@123" | Password is masked. | Success | Pass | Min 6 chars required |
| 7 | Click "Register" button | - | Success message: "Registration successful! Please check your email..." | Success | Pass | Verification token generated |

---

### Table 8.2 Test Case 2: Multi-Role Authentication

| Test Case ID: 2 | Test Designed By: Antigravity |
| :--- | :--- |
| **Test Priority:** High | **Test Designed Date:** 17-01-2026 |
| **Module Name:** User Authentication | **Test Executed By:** Sabikun Nahar Lima |
| **Test Title:** Role-Based Login Access Test | |
| **Description:** Verify that Admin, Doctor, and Patient are redirected to their respective dashboards. | |
| **Pre-Condition:** Valid accounts exist for each role in the database. | |
| **Dependencies:** JWT Authentication Middleware | |

| Step | Test Steps | Test Data | Expected Result | Actual Result | Status | Note |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Enter Admin credentials and login | Email: "admin@telehealth.com", Pass: "admin123" | Redirected to Admin Dashboard. | Success | Pass | |
| 2 | Enter Doctor credentials and login | Email: "dr.adeeva@example.com", Pass: "doctor123" | Redirected to Doctor Dashboard. | Success | Pass | |
| 3 | Enter Patient credentials and login | Email: "akash@example.com", Pass: "Akash@123" | Redirected to Patient Dashboard. | Success | Pass | |
| 4 | Click "Logout" | - | Token invalidated, redirected to Login. | Success | Pass | |

---

### Table 8.3 Test Case 3: Appointment Booking & Serial Assignment

| Test Case ID: 3 | Test Designed By: Antigravity |
| :--- | :--- |
| **Test Priority:** High | **Test Designed Date:** 17-01-2026 |
| **Module Name:** Appointment System | **Test Executed By:** Sabikun Nahar Lima |
| **Test Title:** Patient Appointment Booking with Auto-Serial | |
| **Description:** Verify that an appointment is booked and a serial number is assigned based on existing count. | |
| **Pre-Condition:** Patient is logged in; Doctor has available visiting hours. | |
| **Dependencies:** Doctor Schedules Table, Patients Table | |

| Step | Test Steps | Test Data | Expected Result | Actual Result | Status | Note |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Select Doctor and Date | Dept: "Cardiology", Doctor: "Dr. Adeeva Islam" | Doctor availability is shown. | Success | Pass | |
| 2 | Enter symptoms and notes | Reason: "Chest Pain" | Fields accept input. | Success | Pass | |
| 3 | Click "Book Appointment" | - | Appointment is created; Status: "pending_payment". | Success | Pass | |
| 4 | Verify Serial Number | - | Serial number is (Current Count + 1). | Success | Pass | AI Serial Logic |
| 5 | Verify Video Link | - | Jitsi link is prepended to 'notes' field. | Success | Pass | Room ID unique |

---

### Table 8.4 Test Case 4: SSLCommerz Payment Integration

| Test Case ID: 4 | Test Designed By: Antigravity |
| :--- | :--- |
| **Test Priority:** High | **Test Designed Date:** 17-01-2026 |
| **Module Name:** Financial Management | **Test Executed By:** Sabikun Nahar Lima |
| **Test Title:** Secure Payment via SSLCommerz | |
| **Description:** Verify that a patient can pay for an appointment using SSLCommerz. | |
| **Pre-Condition:** Appointment exists with "pending_payment" status. | |
| **Dependencies:** SSLCommerz Sandbox API, Payments Table | |

| Step | Test Steps | Test Data | Expected Result | Actual Result | Status | Note |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Select "Pay Now" on Appointment | Method: "SSLCommerz" | Redirected to SSLCommerz Hosted Page. | Success | Pass | |
| 2 | Complete payment on Gateway | bKash/Nagad/Card (Sandbox) | Success callback received by Laravel. | Success | Pass | |
| 3 | Check payment status | - | Status updated to "Complete" in system. | Success | Pass | |
| 4 | Verify Receipt | - | PDF receipt is generated with Transaction ID. | Success | Pass | |

---

### Table 8.5 Test Case 5: Medical Report Generation & Export

| Test Case ID: 5 | Test Designed By: Antigravity |
| :--- | :--- |
| **Test Priority:** Medium | **Test Designed Date:** 17-01-2026 |
| **Module Name:** Clinical Documentation | **Test Executed By:** Sabikun Nahar Lima |
| **Test Title:** Prescription Upload (Admin) & Download (Patient) | |
| **Description:** Verify that reports can be managed and exported as PDF. | |
| **Pre-Condition:** Patient had a consultation; Doctor has entered prescription details. | |
| **Dependencies:** dompdf library, File Storage | |

| Step | Test Steps | Test Data | Expected Result | Actual Result | Status | Note |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Doctor submits prescription | Medicines: "Napa 500", Advice: "Rest" | Data stored in prescriptions table. | Success | Pass | |
| 2 | Admin uploads medical report | File: "report_001.pdf" | File saved to `public/health_records`. | Success | Pass | |
| 3 | Patient views report list | - | Reports are listed with "Download" button. | Success | Pass | |
| 4 | Click "Download PDF" | - | Formatted PDF with Hospital Branding opens. | Success | Pass | Uses dompdf |
