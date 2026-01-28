# 🏥 Telehealth Consultation System: Project Mastery Guide

This guide is your persistent "cheat sheet" for your presentation. It explains the project's logic and architecture in simple terms.

---

## 🗺️ The "Golden Pattern"
Almost every feature (Appointment, Login, Prescription) in this project follows this **4-Step Bridge**:

1.  **UI (Frontend)**: React captures user input (e.g., `Login.js`).
2.  **API Bridge**: The request travels through the route (e.g., `api.php`).
3.  **The Chef (Backend Logic)**: A Controller (e.g., `AdminController.php`) processes the data.
4.  **Warehouse (Database)**: A Model (e.g., `Admin.php`) saves/reads from the MySQL database.

---

## 🛡️ Day 1: Authentication Logic (The Front Door)

### Lesson 1: How Login Works
When you log in, the system verifies your identity and gives you a "Digital ID Card" (Token).

#### 1. The Frontend Page
- **File**: `hospital_frontend\src\pages\Login.js`
- **關鍵 Line (Line 35)**: `const res = await axiosClient.post(endpoint, { email, password });`
- **Explanation**: This line "posts" (sends) your credentials to the backend server. The `endpoint` changes depending on if you are a Patient, Doctor, or Admin.

#### 2. The Route Traffic Controller
- **File**: `hospital_api\routes\api.php`
- **關鍵 Line (Line 59)**: `Route::post('admin/login', [AdminController::class, 'login']);`
- **Explanation**: This tells Laravel: "If someone sends a request to `/admin/login`, send them to the `login` room in the `AdminController`."

#### 3. The Backend Controller (The Logic)
- **File**: `hospital_api\app\Http\Controllers\AdminController.php`
- **和關鍵 Logic (Lines 21-25)**: 
  ```php
  $admin = Admin::where('email', $request->email)->first(); // Finding the user
  if (!$admin || !Hash::check($request->password, $admin->password)) { // Verifying password
      return response()->json(['message' => 'Invalid credentials'], 401); // Refusing access
  }
  ```
- **Explanation**: This is the most important part. It looks for the email in the table, then compares your typed password with the "secret" (hashed) password in the database.

#---

## 📝 Lesson 3: The PDF/Print Bridge (Hidden Iframe Technique)

When the user clicks "Download", they expect a PDF. However, generating PDFs on the server can be slow. Your project uses a clever **Frontend Trick**:

### How it Works (The 3-Step Trick)
- **File**: `DoctorReports.js`, `Prescriptions.js`, `Appointments.js`
1.  **Invisible Paper**: The code creates a "hidden iframe" (an invisible browser window).
2.  **Instant Writing**: It writes raw HTML/CSS (the design of the receipt/report) into that invisible window.
3.  **The Printer Call**: It tells that window to `print()`. This opens the browser's "Save as PDF" dialog instantly.

### Why this is smart for your Presentation:
If the judge asks: *"Did you use a library like DomPDF?"*, you can say:
> "No, I implemented a **Client-Side Print Bridge**. It uses a hidden iframe to generate high-quality PDFs directly from HTML/CSS, which is faster and reduces server load."

---

### Refinements for Presentation:
- **One-Page Layout**: We optimized the CSS (margins, font-sizes, and padding) to ensure the report fits perfectly on a single A4 sheet. This makes it look more professional during a demo.
- **Doctor ID Inclusion**: The report now clearly displays the `Doctor ID` (e.g., `D-101`) alongside their name, helping admins track performance more accurately.

---

---

## 📝 Lesson 4: Model Relationships (Linking Appointments to Doctors)

How does the Receipt know the Doctor's specialization? It uses a **Relationship**.

### 1. The Warehouse (Model)
- **File**: `Appointment.php`
- **Logic**: We added `public function doctor() { return $this->belongsTo(Doctor::class); }`.
- **Presentation Tip**: Explain that this "links" the tables. It's like saying "Every appointment belongs to one doctor."

### 2. The API Bridge (Eager Loading)
- **File**: `AppointmentController.php`
- **Logic**: We changed `Appointment::all()` to `Appointment::with('doctor')->get()`.
- **Reason**: This is called **"Eager Loading"**. It fetches the doctor's info *at the same time* as the appointment, making the app faster because it only makes one trip to the database.

---

---

## 📝 Lesson 5: Datewise Filtering (Dynamic Statistics)

When an Admin selects a custom date range (e.g., last 7 days), the system must show statistics *only* for that range.

### 1. The Logic Shift
- **File**: `DoctorReportController.php`
- **Old Way**: The "Total Patients" card showed *every patient ever registered*.
- **New Way**: It now calculates: `$allAppointments->pluck('patient_id')->unique()->count()`.
- **Presentation Tip**: If the judges ask how you handle dynamic dates, you can explain that you **filtered the data twice**: once to get the appointments for that period, and then again to extract unique Patient IDs and Doctor IDs from *those* appointments.

---

---

## 📝 Lesson 6: Secure Password Logic (Frontend & Backend Sync)

Why did we have to update both `Register.js` and `PatientController.php`? It's about **Defense in Depth**.

### 1. The Frontend Guard (Visual Feedback)
- **File**: `Register.js`
- **Logic**: Prevents the form from being sent if the password is simple.
- **Why**: It's fast and gives the user instant feedback without waiting for the server.

### 2. The Backend Vault (The Real Security)
- **File**: `PatientController.php`
- **Logic**: Re-validates the password before it hits the database.
- **Why**: Hackers can skip your frontend and send data directly to your API. Validation here is the **Final Shield**.

### 3. The Golden Rule of Storage
- **Code**: `Hash::make($password)`
- **Concept**: Never store "plain text". If the DB is leaked, hackers only see hashed gibberereish, not real passwords.

---

## 🛠️ Practice Tracker
- [x] **Task #1**: Modified error message in `AdminController.php`.
- [x] **Task #2**: Modified Registration Token length in `PatientController.php`.
- [x] **Task #3**: Fixed and Refined Doctor Report downloads.
- [x] **Task #4**: Fixed Datewise Report Metrics (Custom Range).
- [x] **Task #5**: Added Doctor ID & Specialist to Patient Receipts.
- [x] **Task #6**: Fixed Password Complexity Rules (8 Chars + Upper + Symbol).

## 📝 Lesson 2: Registration Logic (Patient Enrollment)

### 🧐 Line-by-Line Breakdown (`PatientController.php`)

**Lines 59 - 62**:
- **Line 59**: `$patient->verification_token = Str::random(64);`
    - **What it does**: Generates a long, random "Secret Key". This is what goes into the link sent to your email.
- **Line 60**: `$patient->email_verified_at = null;`
    - **What it does**: Explicitly marks the user as **"Not Verified"** in the database.
- **Line 62**: `$patient->save();`
    - **What it does**: **CRITICAL**. This is the command that actually pushes all the data into the MySQL database. Without this line, nothing is saved!

### 1. The Form (Frontend)
- **File**: `hospital_frontend\src\pages\Register.js`
- **Key Logic**: Uses React `useState` to capture input and `axiosClient.post('/register', ...)` to send it to the server.

### 2. The Logic (Backend)
- **File**: `hospital_api\app\Http\Controllers\PatientController.php`
- **關鍵 Logic (Validation)**:
  ```php
  'name' => 'required|string|regex:/^[A-Z]/'
  ```
  - **Explanation**: This `regex` (Regular Expression) ensures the name starts with a **Capital Letter**. If a user types "zisan", it will fail. If they type "Zisan", it passes.
- **關鍵 Logic (Security)**:
  ```php
  $patient->password = Hash::make($req->input('password'));
  ```
  - **Explanation**: Never store real passwords! `Hash::make` creates a one-way secure scramble.

---





## 🎨 Design Mastery: CSS & Tailwind Guide

This section explains the visual "DNA" of your project. If you want to change colors or layouts, look for these classes.

### 🏠 Home.js (The Landing Page)
- **The "Card Overlap" (Lines 338-342)**:
  - `bg-gradient-to-br from-cyan-500 to-blue-600`: Creates the colorful top half of the doctor card.
  - `absolute -bottom-12 sm:-bottom-16`: Pushes the profile photo halfway out of the header.
  - `rounded-full overflow-hidden`: Ensures the doctor's square photo is cropped into a perfect circle.
- **Background Glow (Line 424-426)**:
  - `bg-cyan-500 blur-3xl opacity-10`: Creates the modern "soft light" effect in the About section. It looks like a high-end designer made it!

### 🧭 Navbar.js (Global Navigation)
- **Top Stickiness (Line 89)**:
  - `fixed top-0 w-full z-50`: Keeps the Navbar at the very top even when you scroll.
  - `bg-gradient-to-r from-slate-900 via-blue-900 to-slate-900`: A professional tri-color gradient that matches the Telehealth theme.
- **Active Scrolling (Line 23)**:
  - `element.scrollIntoView({ behavior: "smooth" })`: This is why the page "slides" nicely when you click "About" or "Doctors."
- **Conditional Visibility (Line 88)**:
  - `isAuthPage = location.pathname === "/login" || ...`: This logic hides all menu links and buttons on the login/register pages so the user isn't distracted while signing in.

### 📁 DoctorDashboard.js (The Physician's Hub)
- **The Data Fetching (Line 35 & 48)**:
  - `fetchTodayAppointments`: Filters appointments by today's date to show the count.
  - `fetchDoctorData`: Loads the full profile from `/doctors/{id}` to fill the cards.
- **The Responsive Layout (Line 109)**:
  - `${isSidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'}`: Dynamically adjusts the page margin when you click the sidebar toggle.
- **The Visual Banner (Line 163-176)**:
  - `bg-gradient-to-r`: Creates the multi-color premium background.
  - `absolute ... rounded-full`: These are the "Animated Blobs" that grow slightly (`scale-110`) on hover.
  - `backdrop-blur-md`: The modern "Glassmorphism" effect used for the photo frame.
  - `doctor.photo ? ... : ...`: Conditional fallback logic to show a professional emoji if no photo is uploaded.
- **The Info Grid (Line 219)**:
  - `.map((item, idx) => ...)`: Uses a clean array-based mapping to render all doctor details automatically.

> [!TIP]
> **How to Edit**: 
> - To change colors, search for `indigo-600` or `bg-white` and swap them.
> - To add more info fields, add them to the array on **Line 220**.

### 📁 Sidebar.jsx (The Portal Sidebar)
- **The Shrink/Expand (Line 49)**:
  - `${isCollapsed ? 'w-20' : 'w-64'}`: Dynamically changes width based on the desktop toggle.
- **Mobile Logic (Line 51)**:
  - `translate-x-0` vs `-translate-x-full`: This moves the sidebar from left-to-right on phones.
- **Active Glow (Line 101)**:
  - `shadow-md shadow-blue-500/20`: Adds a subtle blue "glow" behind the menu button you have currently selected.

## 📁 Admin Portal: Management Mastery

### Lesson 7: The Master Management Pattern (CRUD)
**Files**: `AdminDoctors.js`, `AdminPatients.js`

This is where the Admin controls the core of the system.
1.  **The Dual-Purpose Modal**: Instead of two pages, I used one modal. If `isEditing` is true, it pre-fills data; if false, it shows a blank form.
2.  **The Laravel "PUT" Secret**: Laravel cannot read standard `PUT` requests when they contain images (`FormData`). 
    - **Trick**: I send a `POST` request but add `_method: 'PUT'`. This allows image uploads while still doing an update.
3.  **Live Search Logic**: 
    ```javascript
    const filtered = data.filter(item => 
        item.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
        item.id.toString() === searchTerm
    );
    ```
    - This allows searching by either name or ID instantly as you type.

### 🩺 Lesson 7.1: Deep Dive into Doctor Management
**File**: `AdminDoctors.js` (Frontend) & `AdminController.php` (Backend)

The "Doctor Management" page is a high-security module because it handles professional credentials and financial data.

#### 1. The Data Pipeline (Fetching Data)
This is how records move from MySQL to your browser screen.
- **Trigger**: `useEffect` (Line 38) runs `fetchDoctors()` as soon as the page loads.
- **Requester**: `axiosClient.get("/admin/doctors")` (Line 44) sends a signal to the backend.
- **Controller**: `AdminController@getDoctors` (Line 150) runs the SQL: `SELECT * FROM doctors`.
- **Display**: The `.map()` function (Line 144 in JSX) loops through the data to build the table rows.

#### 2. The Add/Edit Flow (Frontend to Backend)
This is how the system handles the specific "Save" button logic.
- **Capturing Text**: `handleInputChange` (Line 56) saves every keystroke into the `formData` object.
- **Packaging Images**: `new FormData()` (Line 129) is used because standard JSON cannot carry physical image files.
- **Method Spoofing**: To update a doctor, we add `data.append('_method', 'PUT')` (Line 143). This is a "Laravel Secret" that allows updating files via a POST request.
- **The Delivery**: `axiosClient.post(url, data)` (Lines 144/149) sends the envelope to the API.

#### 3. The Backend Security (Reception)
- **Validation**: `request->validate([...])` (Line 160/207) checks for name characters and positive numbers.
- **Storage**: `$doctor->save()` (Line 190/240) is the "Final Commitment" to the MySQL database.
- **Response**: The server sends back the updated doctor object, which React uses to refresh the table without a page reload (Line 147/152).

### 📊 Lesson 7.2: Admin Dashboard Logic (The Overview Hub)
**File**: `AdminDashboard.js` (Frontend) & `AdminController.php` (Backend)

The "Admin Dashboard" is the nerve center of the hospital. It provides real-time statistics and a snapshot of current activities.

#### 1. The Automated Loading (`AdminDashboard.js`)
- **Lifecycle (Line 30)**: Uses the `useEffect` hook to automatically call `fetchDashboardData()` when the admin logs in.
- **Data Hook (Line 48)**: `const res = await axiosClient.get("/admin/dashboard-stats");` 
- **State Management (Lines 50-51)**: The response is split into two boxes: `stats` (for the big cards) and `recentAppointments` (for the table).

#### 2. The Statistical Engine (`AdminController.php`)
- **Function**: `getDashboardStats()` (Line 37)
- **The "Counting" Logic (Lines 40-42)**:
  - `Patient::count()` -> Counts every patient in the database.
  - `Doctor::count()` -> Counts every doctor.
  - `Appointment::count()` -> Counts every booking.
- **The "Recent 5" Order Logic (Lines 44-46)**:
  - `Appointment::orderBy('created_at', 'desc')->take(5)->get()`
  - **Sorting (Line 44)**: `orderBy('created_at', 'desc')` ensures the **Newest Appointments** appear at the very top.
  - **Limiting (Line 45)**: `take(5)` restricts the list to a maximum of 5 items to keep the dashboard clean and fast.
- **The Data Join (Lines 47-59)**: Because the appointment only has IDs (like `doctor_id: 7`), the code uses a `.map()` to look up the actual names (like "Dr. Mahmudul Hasan") from the Doctor and Patient tables before sending the list back to the frontend.

### 👥 Lesson 7.3: Patient Management & Healthy Data
**File**: `AdminPatients.js` (Frontend) & `AdminController.php`/`PatientController.php` (Backend)

We implemented strict rules to ensure patient data is accurate and realistic.

#### 1. The "Non-Negative" Measurements
- **Logic**: Age, Height, and Weight cannot be less than zero.
- **Frontend Check (`AdminPatients.js` Line 108-120)**: Alerts the user immediately if they enter a negative number.
- **HTML Guard (`Settings.js` Lines 436, 459, 469)**: Uses the `min="0"` attribute to prevent the browser's number-picker from going below zero.
- **Backend Guard (`AdminController.php` Line 96 / `PatientController.php` Line 21)**: Uses `numeric|min:0` to stop invalid data at the database level.

#### 2. Consistent Name Validation
We applied the same "Letters Only" rule to patients that we used for doctors.
- **Regex**: `regex:/^[a-zA-Z\s\.]+$/u`
- **Result**: Prevents symbols, numbers, or "garbage" text from being used as a patient name.
- **Line Reference**: `PatientController.php` (Line 17) and `AdminController.php` (Line 93).
**Files**: `AdminAppointments.js`, `AdminPayments.js`, `AdminFeedback.js`

1.  **Visual Status Pills**: I used color-coding for status (Green = Completed, Red = Cancelled). This allows admins to spot issues in the hospital workflow instantly.
2.  **Transaction ID Tracking**: Crucial for bank reconciliations. If a patient claims they paid, the admin can search the literal `Transaction ID` string from the bank.
3.  **Line-Clamping**: In Feedback, long messages are shortened using `line-clamp-2` so the table stays neat, but details are still saved.

---

## 🔬 Clinical Workflow: The Heart of the App

### Lesson 9: The Dynamic Appointments Page
**File**: `Appointments.js`

This is a "Chameleon" page. It changes its skin based on who is looking at it.
1.  **The Role Switch**: `role === 'doctor' ? 'Patient Name' : 'Doctor Name'`. One codebase, two different views.
2.  **Video Call Moderator Link**: 
    - When a doctor clicks "START", the code adds `#config.moderator=true` to the Jitsi link. 
    - This ensures the doctor has "Host" privileges (can mute others, start recording) automatically.
3.  **20-Minute Slot Calculation**: The time shown (e.g., 10:20 AM) isn't just a string. It is calculated by adding `Serial Number * 20 minutes` to the doctor's starting time.

### Lesson 9.1: Status Standardization (Scheduled vs Running vs Done)
**File**: `AdminAppointments.js`, `Appointments.js`, `AdminDashboard.js`

We solved the "Everything looks Scheduled" bug by standardizing naming conventions.
1.  **Case-Insensitivity**: We updated the Admin UI to use `.toLowerCase()` when checking statuses. This ensures that if a Doctor marks a session as `completed`, the Admin sees it as a green "Completed" pill instead of a default blue "Scheduled" pill.
2.  **The "Running" State**: We added `ongoing` (Running) to the Admin Portal.
    - **Doctor Action**: When a doctor clicks "START", the status becomes `ongoing`.
    - **Admin View**: The Admin can now see which consultations are currently live in the hospital.
3.  **Visual Coding**:
    - **Blue**: Scheduled (Upcoming)
    - **Amber/Orange**: Ongoing (Running now)
    - **Green**: Completed (Done)
    - **Red**: Cancelled (Failed/No-show)

### Lesson 10: The Digital Prescription Workflow
**File**: `Prescriptions.js`

1.  **The "Writing" Workflow**: Doctors don't just write anywhere. The app shows a "Write New" tab that lists *only* patients who have an appointment today.
2.  **Document Generation**: Similar to Lesson 3, this generates a professional-grade medical document.
    - **Tip**: Mention that this reduces the need for physical paper, making the hospital "Eco-Friendly."

---

## 🔐 System Maintenance & Settings

### Lesson 11: The Mega-Settings Component
**File**: `Settings.js`

1.  **Adaptive Forms**: This page manages 15+ different fields. It uses `if (role === 'doctor')` to decide which inputs to show.
2.  **The "Storage Poke" Trick**: 
    ```javascript
    window.dispatchEvent(new Event('storage'));
    ```
    - **Why this is genius**: If you change your profile photo in Settings, the Sidebar (which is a different component) would normally stay old. This line "pokes" the Sidebar to tell it "Hey, something changed, update your photo now!"

---

## 📊 Analytics & Reporting

### Lesson 12: High-Level Performance Reports
**File**: `DoctorReports.js`

1.  **Sync Logic**: The "Generate" button triggers a backend "Synchronization" that recalculates every doctor's revenue and attendance for the month from scratch.
2.  **Success Metrics**: The system calculates the `Completion Rate` (% of patients actually seen). High completion rates = Happy hospital management.
3.  **Custom Range Power**: Unlike simple systems, this allows admins to pick *any* two dates (e.g., "Dec 1st to Dec 14th") to see how much revenue the hospital made during that specific week.

---

## 🛡️ Lesson 13: Strict Field Validations (The Regex & Numeric Shield)
**Concept**: "Garbage In, Garbage Out." We prevent "Garbage" from ever entering our database.

### 1. The Regular Expression (Regex)
- **Code**: `regex:/^[a-zA-Z\s\.]+$/u`
- **Explanation**: 
    - `^` = Start of line.
    - `a-zA-Z` = Letters only.
    - `\s` = Allow spaces.
    - `\.` = Allow dots (for "Dr.").
    - `$` = End of line.
- **Presentation Tip**: Tell the judges: *"We implemented custom regex validation to ensure data integrity and prevent identity spoofing with symbols or numbers in names."*

### 2. The Non-Negative Check
- **Code**: `numeric|min:0` (Backend) / `min="0"` (Frontend)
- **Explanation**: Consultation fees and years of experience cannot be negative in the real world. This ensures we don't have calculation errors in our financial reports.

---

## 🏥 Lesson 14: The Patient Dashboard (Personal Health Hub)
**File**: `PatientDashboard.js` (Frontend) & `PatientController.php` / `AppointmentController.php` (Backend)

The Patient Dashboard is the primary interface for patients. It combines physical health tracking (BMI), appointment management, and medical document storage.

### 1. 📊 BMI Index (Real-time Health Calculation)
- **Frontend**: `PatientDashboard.js` (Lines 202-214) 
- **Calculation Logic (Line 209)**: `(weight / ((height / 100) * (height / 100))).toFixed(1)`
- **Explanation**: The BMI is calculated **on-the-fly** in the browser. It takes the patient's weight (kg) and height (cm) from the API response and processes it instantly.
- **Presentation Tip**: *"Our system doesn't just store data; it processes it. By calculating the BMI on the frontend, we provide instant health feedback without overloading the server."*

### 2. 📅 Next Appointment (Paid & Confirmed)
- **Frontend**: `PatientDashboard.js` (Lines 216-290)
- **Backend API**: `/api/appointments/patient/{id}` (routed to `AppointmentController@getPatientAppointments`).
- **Logic**: 
    - **Filter**: The code uses `.filter(a => a.status === 'scheduled')` (Line 74) to show only confirmed visits.
    - **Video Call**: If a doctor starts the session, the status becomes `ongoing`, and a **"Join Call"** button (Line 258) appears automatically using the Jitsi link stored in the `notes` column.

### 3. ⚠️ Unpaid Appointments (Alert System)
- **Frontend**: `PatientDashboard.js` (Lines 362-388)
- **Logic**: 
    - **Filter**: Shows appointments with `status: 'pending_payment'` (Line 79).
    - **Action**: Provides a direct "Pay Now" button (Line 377) that links to the **SSLCommerz Payment Gateway**.

### 4. 📂 Medical Records (Blob Storage & JSON Management)
- **Frontend**: `HealthRecords.jsx` (The Upload Component)
- **Backend Flow**: `PatientController@uploadHealthRecord` (Processes the file and saves it to `storage/app/public/health_records`).
- **Explanation**: The system stores file paths in a JSON array within the `uploaded_record` column. The dashboard parses this JSON (Line 431) to display each document as a downloadable link.

---

## 📅 Lesson 15: The Patient Appointments Page (The Logistics)
**File**: `Appointments.js` (Frontend) & `AppointmentController.php` (Backend)

This page is the "Master Schedule" for patients. It proves that the hospital is organized using a dynamic 20-minute slot system.

### 1. ⏱️ The 20-Minute Slot Engine (The "Brain")
- **Backend Code**: `AppointmentController.php` (Method `calculateTimeSlot` at Line 302)
- **Logic**: `$minutesFromStart = ($serialNumber - 1) * 20;`
- **Explanation**: This is a mathematical calculation. If the doctor starts at 9:00 AM, Patient #1 gets 9:00 AM, and Patient #2 gets 9:20 AM. This prevents long hospital wait times.
- **Frontend Display**: `Appointments.js` (Line 283) displays this as a blue "Slot" badge.

### 2. 🎥 The Video Call "Wait & Join" Logic
- **Frontend Code**: `Appointments.js` (Lines 315-331)
- **States**:
    - **Waiting**: If status is `scheduled`, it shows a "⏳ Waiting" icon (Line 328).
    - **Live**: If status is `ongoing`, it shows an animated **"JOIN"** button (Line 323).
- **Presentation Tip**: *"Our video call button is 'Smart'. It only appears when the doctor is ready. This ensures that the patient doesn't enter an empty meeting room."*

### 3. 📑 Digital Billing (Receipt Generation)
- **Frontend Code**: `Appointments.js` (Lines 106-159)
- **Technique**: Uses the **Hidden Iframe** trick (see Lesson 3) to create professional receipts locally.
- **Presentation Tip**: *"Instead of physical receipts, we provide click-to-print digital receipts that include doctor specialized details and serial numbers for hospital filing."*

---

## 📄 Lesson 16: The Prescription System
**File**: `Prescriptions.js` (Frontend) & `PrescriptionController.php` (Backend)

Digital prescriptions replace physical paper, ensuring patients never lose their doctor's advice.

### 1. ✍️ The Writing Workflow (Doctor)
- **Frontend**: The `Prescriptions.js` page has a "WRITE NEW" tab (Line 310) that lists appointments.
- **The Writing Box**: A standard HTML `<textarea>` (Line 486) captures the medicines and dosages.
- **Submission**: When the doctor clicks "Send", `handleSendPrescription` (Line 71) posts the text to `/api/prescriptions`.

### 2. 📥 The Patient Experience
- **Frontend Fetch**: Calls `/api/prescriptions/patient/{id}` (Line 61) to show the history cards.
- **PDF Export**: Uses the **Hidden Iframe** trick (see Lesson 3) to generate a high-quality clinical document instantly for printing.

### 3. ⚙️ Backend Status Sync
- **Logic**: When a prescription is saved, the backend automatically updates the `appointments` table status to `completed` (Line 31 in `PrescriptionController.php`). This keeps the hospital queue moving.

---

## 📂 Lesson 17: Medical Records & File Management
**File**: `MedicalRecords.js` (Frontend) & `PatientController.php` (Backend)

This page allows patients to manage their uploaded diagnostic documents (PDFs/Images).

### 1. 📥 Data Retrieval & Parsing
- **Frontend**: `MedicalRecords.js` (Line 27) calls the patient details API.
- **The JSON Challenge**: In the database, the `uploaded_record` column stores file paths as a JSON array (e.g., `["path/to/file1.pdf", "path/to/file2.png"]`).
- **Code Logic**: `MedicalRecords.js` (Line 112) uses `JSON.parse` to turn this string back into a JavaScript array so it can be mapped into table rows.

### 2. 🗑️ Secure File Deletion
- **Frontend**: `handleDeleteRecord` (Line 39) sends a DELETE request with the specific file path.
- **Backend Logic**: `PatientController.php` (Line 205) performs three critical steps:
    1. **Physical Deletion**: It uses `Storage::disk('public')->delete()` to remove the file from the server's hard drive.
    2. **Database Update**: It removes the path from the JSON array and saves the updated list back to the `patients` table (Line 231).
    3. **State Sync**: If no files are left, it sets `previous_record` to 'no' (Line 234).

### 3. 🗺️ API Mapping (`api.php`)
- **Fetch**: `GET /api/patients/{id}` -> `PatientController@show` (Line 40)
- **Delete**: `DELETE /api/patients/{id}/health-records` -> `PatientController@deleteHealthRecord` (Line 39)

---

## 🔐 Lesson 18: The Universal Login (Role-Based Access)
**File**: `Login.js` (Frontend) & `PatientController/DoctorController/AdminController.php` (Backend)

The Login page is the "Gatekeeper" of the system, handling three different types of users in one interface.

### 1. 🎛️ Frontend: The Role Switcher
- **File**: `Login.js` (Lines 94-116)
- **Logic**: A state variable `role` (Line 6) defaults to 'patient'. When a user clicks a tab, it updates the visual style and prepares the correct API path.
- **Form Submission (Line 15)**:
    - Depending on `role`, it targets different API endpoints (`/login`, `/doctor/login`, or `/admin/login`).
    - **Success Handling**: It stores the `auth_token`, `auth_user` object, and `user_role` string in `localStorage` (Lines 47-49). This is what keeps the user logged in after a refresh.

### 2. 🛡️ Backend: Verification Logic
- **Hashing**: All controllers use `Hash::check()` (e.g., `PatientController.php` Line 90) to compare the typed password with the encrypted string in the database.
- **Controllers**:
    - **Patient**: `PatientController.php` (Line 87)
    - **Doctor**: `DoctorController.php` (Line 102)
    - **Admin**: `AdminController.php` (Line 14) - Uses `Laravel Sanctum` for token generation.

### 3. 🗺️ API Routes (`api.php`)
- **Patient**: `POST /api/login` (Line 35)
- **Doctor**: `POST /api/doctor/login` (Line 43)
- **Admin**: `POST /api/admin/login` (Line 59)

---

## 📧 Lesson 19: Registration & Email Workflow (The Trigger)
**File**: `Register.js` (Frontend) & `PatientController.php` (Backend)

The verify email system ensures that only users with real email addresses can access the telehealth platform.

### 1. 🚀 The 3-Step Trigger
1. **Frontend**: User fills the form in `Register.js`. When they click "Create", it sends a `POST` to `/api/register` (Line 65).
2. **Backend (Secret Key)**: `PatientController.php` (Line 70) generates a 64-character `verification_token`.
3. **The Mailer**: Laravel uses the `Mail` facade (Line 76) to send a `PatientVerificationMail`.

### 2. 📬 The Template (`PatientVerificationMail.php`)
- This file acts as the bridge between the server and the user's inbox.
- It uses an HTML view (`emails.verify_patient`) to show the user a professional "Verify Email" button.

### 3. ✅ The Final Handshake (`VerifyEmail.js`)
- When the user clicks the link in their email, they are sent to the frontend `VerifyEmail.js` page.
- This page takes the token from the URL and sends it to `/api/verify-email` (Line 41 in `api.php`).
- **Backend Result**: The server finds the patient and updates `email_verified_at = now()`.

---

## 🔑 Lesson 20: The Password Reset Workflow (Security Loop)
**File**: `ForgotPassword.js`, `ResetPassword.js` (Frontend) & `PasswordResetController.php` (Backend)

The "Forgot Password" system uses a secure, time-limited token loop to verify ownership of an email account.

### 1. 📧 Phase 1: Requesting the Link
- **Frontend**: `ForgotPassword.js` (Line 17) sends the user's email to `/api/forgot-password`.
- **Backend Logic (`sendResetLink`)**:
    1. **Multi-Role Check**: It searches for the email in **Patients**, then **Doctors**, then **Admins** (Lines 24-26).
    2. **Token Generation**: It creates a random 60-character string (Line 32).
    3. **Database Guard**: It stores the **Hashed** version of this token in the `password_reset_tokens` table for security (Line 34).
    4. **Mail Delivery**: It sends an email containing a link that looks like: `.../reset-password?token=XYZ&email=abc@test.com`.

### 2. 🔄 Phase 2: Resetting the Password
- **Frontend**: `ResetPassword.js` captures the `token` and `email` from the URL parameters.
- **Backend Logic (`reset`)**:
    1. **Token Validation**: It hashes the incoming token and compares it to the database (Line 60).
    2. **Expiration Check**: Tokens automatically expire after **1 hour** to prevent old links from being reused (Line 67).
    3. **The Update**: If valid, it hashes the *new* password and saves it to the user's record (Line 80).
    4. **Cleanup**: It deletes the token from the database so it can never be used again (Line 83).

### 3. 🗺️ API Mapping (`api.php`)
- **Request Link**: `POST /api/forgot-password` (Line 92)
- **Save New Password**: `POST /api/reset-password` (Line 93)

---

## 🏠 Lesson 21: The Home Page & Search Engine
**File**: `Home.js` (Frontend) & `DoctorController.php` (Backend)

The Home page is the "Public Marketplace" of the hospital, where patients find the right doctors.

### 1. 🔍 The Multi-Layer Search Logic
- **Frontend Filter (Lines 295-307)**: When you type in the search bar, the code filters the `doctors` array based on **Name** or **Specialist** (Disease).
- **Category Filter (Line 301)**: If you select "Cardiologist", it hides all other doctors by comparing the first 4 letters of the specialized field (e.g., "Cardio").

### 2. 🛡️ Role-Based Booking (Line 63)
- When a user clicks "Book Appointment", the system checks `localStorage`:
    - **Not Logged In**: Redirects to `/login`.
    - **Logged in as Doctor/Admin**: Redirects to `/login` (only patients can book).
    - **Logged in as Patient**: Navigates to `/book-appointment/{id}`.

### 3. ⚙️ Backend: The Data Pipe
- **API Cache/Map (Line 11 in `DoctorController.php`)**: The `index` method doesn't just send raw database rows. It **maps** the internal database names (like `full_name`) to frontend-friendly names (like `name`).
- **Storage Bridge (Lines 77-81 in JS)**: Since images are stored in the Laravel `storage/app/public` folder, the frontend uses a helper function to prefix the URL with `http://localhost:8000/storage/` so the images actually show up.

---

## 💳 Lesson 22: The Payment Gateway (SSLCommerz Integration)
**Files**: `BookAppointment.js` (Frontend) & `SslCommerzPaymentController.php` (Backend)

The payment system uses **SSLCommerz**, Bangladesh's leading payment gateway, to process online payments securely.

### 1. 🎯 Frontend: The Payment Trigger
**File**: `BookAppointment.js` (Lines 151-240)

#### **Step 1: Create Appointment (Line 203)**
```javascript
const res = await axiosClient.post("/appointments", appointmentData);
```
- Creates an appointment with `status: "pending_payment"` and `payment_status: "pending"`.
- The appointment is saved to the database first.

#### **Step 2: Initiate Payment (Lines 213-216)**
```javascript
const payRes = await axiosClient.post("/pay", {
    appointment_id: appointmentId,
    payment_method: form.payment_method
});
```
- Sends the appointment ID to the backend payment endpoint.
- The backend will generate a payment URL.

#### **Step 3: Redirect to Gateway (Line 218)**
```javascript
window.location.href = payRes.data.data;
```
- Redirects the user to the SSLCommerz payment page.
- The user leaves your website temporarily to complete payment.

### 2. ⚙️ Backend: The Payment Engine
**File**: `SslCommerzPaymentController.php`

#### **A. Payment Initiation (`index` method, Lines 13-67)**
1. **Transaction ID Generation (Line 23)**:
   ```php
   $tran_id = 'SSLC_' . $appointment->id . '_' . uniqid();
   ```
   - Creates a unique transaction ID for tracking.

2. **SSLCommerz Setup (Lines 31-41)**:
   ```php
   $sslc = new SSLCommerz();
   $sslc->amount($appointment->amount)
        ->trxid($tran_id)
        ->product('Medical Consultation')
        ->customer($appointment->patient_name, $email, $address, $phone);
   ```
   - Configures payment details (amount, customer info, product).

3. **Generate Payment URL (Line 45)**:
   ```php
   $paymentResponse = $sslc->make_payment();
   ```
   - Contacts SSLCommerz servers and gets a payment URL.
   - Returns this URL to the frontend.

#### **B. Payment Success Callback (`success` method, Lines 69-132)**
When the user completes payment, SSLCommerz redirects them back to your site.

1. **Validation (Line 74)**:
   ```php
   $validate = SSLCommerz::validate_payment($request->all());
   ```
   - Verifies the payment is legitimate (not tampered).

2. **Update Appointment (Lines 111-116)**:
   ```php
   $appointment->update([
       'status' => 'scheduled',
       'payment_status' => 'paid',
       'payment_method' => $request->card_issuer,
       'payment_details' => $request->all(),
   ]);
   ```
   - Marks appointment as paid and scheduled.

3. **Redirect to Dashboard (Line 119)**:
   ```php
   return redirect('http://localhost:3000/dashboard?payment=success&tran_id=' . $tran_id);
   ```
   - Sends the user back to the frontend dashboard with a success message.

#### **C. Payment Failure/Cancel (Lines 134-144)**
- If payment fails or is cancelled, redirects to dashboard with error status.

### 3. 🛣️ API Routes (`api.php`)
**File**: `hospital_api\routes\api.php` (Lines 106-111)

```php
Route::post('pay', [SslCommerzPaymentController::class, 'index']);        // Line 107
Route::post('success', [SslCommerzPaymentController::class, 'success']);  // Line 108
Route::post('fail', [SslCommerzPaymentController::class, 'fail']);        // Line 109
Route::post('cancel', [SslCommerzPaymentController::class, 'cancel']);    // Line 110
Route::post('ipn', [SslCommerzPaymentController::class, 'ipn']);          // Line 111
```

### 4. 🔄 The Complete Flow Diagram
```
[Patient] → [Book Appointment] → [Create Appointment (pending_payment)]
    ↓
[Call /pay API] → [Backend generates SSLCommerz URL]
    ↓
[Redirect to SSLCommerz] → [User pays with bKash/Nagad/Card]
    ↓
[SSLCommerz calls /success] → [Backend validates & updates appointment]
    ↓
[Redirect to Dashboard] → [Show "Payment Successful"]
```

### 💡 Presentation Tip:
If the instructor asks: *"What happens if the user closes the browser during payment?"*
> **Your Answer**: "We use **IPN (Instant Payment Notification)** (Line 146). Even if the user closes the browser, SSLCommerz sends a server-to-server notification to our `/ipn` endpoint, which updates the appointment status in the background. This ensures no payment is lost."

---

## 📹 Lesson 23: Specifying the Jitsi Video Link
**Files**: `AppointmentController.php` (Backend) & `Appointments.js` (Frontend)

The system uses **Jitsi Meet** for video consultations. It's a free, open-source video conferencing platform that requires no API keys or complex setup—just a unique URL.

### 1. 🏭 Backend: Generating the Link
**File**: `AppointmentController.php` (Lines 204-209)

When an appointment is booked, the backend automatically generates a unique video meeting link.

```php
// Generate unique room ID
$uniqueRoomId = 'TeleHealth-' . uniqid() . '-' . $appointment->serial_number;

// Construct full Jitsi URL
$videoLink = "https://meet.jit.si/" . $uniqueRoomId;

// Store in 'notes' field (Prepended to user notes)
$appointment->notes = $videoLink . "\n\n" . ($userNotes ? "Patient Notes: " . $userNotes : "");
```
- **Why in `notes`?**: To avoid changing the database schema by adding a new column.
- **Uniqueness**: Uses `uniqid()` + `serial_number` to ensure no two appointments ever share the same room.

### 2. 🖥️ Frontend: Joining the Call
**File**: `Appointments.js` (Lines 304-332)

The frontend parses the link from the `notes` field and displays buttons based on the user's role.

#### **Determining the Link (Line 306)**
```javascript
const link = apt.notes?.match(/https?:\/\/[^\s]+/)?.[0];
```
- Uses Regex to extract the HTTP/HTTPS URL from the text notes.

#### **Patient View ("JOIN" Button)**
- **Action**: Simply opens the link in a new tab.
- **Logic**:
  ```javascript
  window.open(link, '_blank');
  ```

#### **Doctor View ("START" Button)**
- **Action**: Opens the link with special moderator parameters.
- **Logic (Lines 167-170)**:
  ```javascript
  const cleanLink = link.split('#')[0];
  const moderatorParam = `#config.prejoinPageEnabled=false&userInfo.displayName="Dr. ${name}"`;
  const finalLink = cleanLink + moderatorParam;
  window.open(finalLink, '_blank');
  ```
  - **`prejoinPageEnabled=false`**: Skips the "Type your name" screen for the doctor, creating a smoother experience.
  - **`userInfo.displayName`**: Auto-sets the doctor's name in the meeting.

### 3. 🔄 The Flow
1. **Patient Books**: Backend creates `https://meet.jit.si/TeleHealth-65a...` and saves it.
2. **Dashboard**: Both Doctor and Patient see the appointment.
3. **Time for Call**:
   - **Doctor** clicks "START" → Enters room as moderator.
   - **Patient** clicks "JOIN" → Enters same room.
4. **Jitsi Server**: Connects them peer-to-peer (P2P). No video data touches your server.

---

## ️ Presentation Master-List (Advanced)
- [x] **Task #13**: Built the Prescription hidden-iframe PDF system.
- [x] **Task #14**: Implemented multi-file medical record JSON parsing.
- [x] **Task #15**: Standardized Role-Based Login with dynamic visual themes.
- [x] **Task #16**: Integrated automated Email Verification workflow.

---
*(End of Guide)*
