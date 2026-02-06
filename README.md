# Simple Event Management System

A comprehensive web-based application for managing events, participants, and attendance tracking using QR code technology. Perfect for conferences, seminars, workshops, training sessions, and any gatherings that require participant registration and attendance monitoring.

## Features

### For Administrators
- **Event Management**: Create, edit, and delete events with customizable details
- **Session Scheduling**: Define multiple sessions per event with specific time slots
- **Participant Management**: Add, edit, and remove participants from the system
- **Invitation System (RSVP)**: Send event invitations to selected participants and track responses
- **QR Code Generation**: Generate static or dynamic QR codes for attendance
- **Live Monitoring**: Real-time display of active session and QR code on monitor/projector
- **Attendance Reports**: View and export attendance history with filtering options (Excel, PDF, Print)

### For Participants
- **Self-Registration**: Register through the web interface
- **Event Invitations**: Receive and respond to event invitations (RSVP)
- **QR Code Scanning**: Scan QR codes to record attendance using smartphone camera
- **Attendance History**: View personal attendance records
- **Profile Management**: Update personal information

### QR Code Modes
1. **Static Mode**: Fixed QR code that can be printed on posters/pamflets - ideal for smaller events
2. **Dynamic Mode**: Auto-refreshing QR code (every 15 seconds) displayed on screen - prevents screenshot abuse for larger events

---

## Application Flow

```
                                    +------------------+
                                    |   Landing Page   |
                                    |    (index.php)   |
                                    +--------+---------+
                                             |
                         +-------------------+-------------------+
                         |                                       |
                         v                                       v
              +----------+----------+                 +----------+----------+
              |   User Registration |                 |      User Login     |
              |   (register.php)    |                 |     (index.php)     |
              +----------+----------+                 +----------+----------+
                         |                                       |
                         +-------------------+-------------------+
                                             |
                         +-------------------+-------------------+
                         |                                       |
                         v                                       v
              +----------+----------+                 +----------+----------+
              |   Admin Dashboard   |                 |   User Dashboard    |
              | (dashboard_admin)   |                 |  (dashboard_user)   |
              +----------+----------+                 +----------+----------+
                         |                                       |
    +--------------------+--------------------+                  |
    |          |         |         |          |                  |
    v          v         v         v          v                  v
+-------+  +-------+  +-------+  +-------+  +-------+     +------------+
|Manage |  |Manage |  | View  |  |Monitor|  |Download|    |  Respond   |
|Events |  |Users  |  |Reports|  |  QR   |  |   QR   |    |to Invites  |
+-------+  +-------+  +-------+  +-------+  +-------+     +------+-----+
    |                                                            |
    v                                                            v
+-------------------+                                    +-------+-------+
| Create/Edit Event |                                    |   Scan QR     |
| - Event Details   |                                    | (Mobile Cam)  |
| - Sessions        |                                    +-------+-------+
| - Invitations     |                                            |
+-------------------+                                            v
                                                         +-------+-------+
                                                         | Process Scan  |
                                                         |(proses_scan)  |
                                                         +-------+-------+
                                                                 |
                                                    +------------+------------+
                                                    |            |            |
                                                    v            v            v
                                              +---------+  +---------+  +---------+
                                              | Success |  | Already |  | Session |
                                              |Recorded |  |Recorded |  | Closed  |
                                              +---------+  +---------+  +---------+
```

---

## Attendance Flow Detail

```
+------------------+     +------------------+     +------------------+
|                  |     |                  |     |                  |
|  Admin creates   |---->|  Admin sends     |---->|  Participant     |
|  event & sessions|     |  invitations     |     |  confirms RSVP   |
|                  |     |                  |     |                  |
+------------------+     +------------------+     +------------------+
                                                          |
                                                          v
+------------------+     +------------------+     +------------------+
|                  |     |                  |     |                  |
|  Attendance      |<----|  System validates|<----|  Participant     |
|  recorded in DB  |     |  session & user  |     |  scans QR code   |
|                  |     |                  |     |                  |
+------------------+     +------------------+     +------------------+
                                                          |
                                                          v
                                                 +------------------+
                                                 |                  |
                                                 |  Admin views     |
                                                 |  attendance      |
                                                 |  reports         |
                                                 +------------------+
```

---

## Database Schema

```
+----------------+       +------------------+       +-------------------+
|     users      |       |      events      |       |  event_sessions   |
+----------------+       +------------------+       +-------------------+
| id (PK)        |       | id (PK)          |       | id (PK)           |
| nama           |       | nama_event       |       | event_id (FK)     |
| nohp (unique)  |       | tanggal          |       | nama_sesi         |
| password       |       | qr_mode          |       | jam_mulai         |
| email          |       +--------+---------+       | jam_selesai       |
| alamat         |                |                 +---------+---------+
| lembaga        |                |                           |
| role           |                |                           |
+-------+--------+                |                           |
        |                         |                           |
        |    +--------------------+---------------------------+
        |    |                    |
        v    v                    v
+-------+----+-------+    +-------+--------+
|event_registrations |    |   attendance   |
+--------------------+    +----------------+
| id (PK)            |    | id (PK)        |
| event_id (FK)      |    | user_id (FK)   |
| user_id (FK)       |    | event_id (FK)  |
| status             |    | session_id (FK)|
| created_at         |    | waktu_scan     |
+--------------------+    +----------------+

+----------------+
|   qr_tokens    |
+----------------+
| id (PK)        |
| token          |
| event_id (FK)  |
| created_at     |
| expires_at     |
+----------------+
```

---

## File Structure

```
/
├── config.php              # Database configuration & environment loader
├── koneksi.php             # Alternative connection file
├── index.php               # Main login page
├── login.php               # Alternative login page
├── register.php            # User registration
├── logout.php              # Session termination
│
├── dashboard_admin.php     # Admin main dashboard (event list)
├── dashboard_user.php      # User main dashboard (invitations & history)
│
├── tambah_event.php        # Create new event with sessions & invitations
├── edit_event.php          # Modify existing event
├── manage_users.php        # User/participant management
├── edit_user_admin.php     # Edit user by admin
├── edit_profile.php        # User self-profile editing
│
├── monitor.php             # QR display page for projector/screen
├── download_qr.php         # Download static QR code as image
├── ajax_generate_qr.php    # Generate dynamic QR code (auto-refresh)
├── ajax_get_session_name.php # Get current active session name
│
├── proses_scan.php         # Process QR scan and record attendance
├── proses_absen.php        # Alternative attendance processor
├── history_daurah.php      # Attendance reports with export options
│
├── .env                    # Environment variables (create manually)
└── README.md               # This documentation
```

---

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx/Laragon)
- Modern web browser with camera access (for QR scanning)

### Steps

1. **Clone or download** the project to your web server directory

2. **Create the database** and run the following SQL:

```sql
CREATE DATABASE event_attendance;
USE event_attendance;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nohp VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    alamat TEXT,
    lembaga VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_event VARCHAR(150) NOT NULL,
    tanggal DATE NOT NULL,
    qr_mode ENUM('static', 'dynamic') DEFAULT 'static'
);

//update certificate
ALTER TABLE `events` 
ADD COLUMN `cert_template` VARCHAR(255) NULL,
ADD COLUMN `cert_font` VARCHAR(255) NULL,
ADD COLUMN `cert_font_size` INT DEFAULT 30,
ADD COLUMN `cert_font_color` VARCHAR(20) DEFAULT '#000000';

CREATE TABLE event_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    nama_sesi VARCHAR(100),
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    session_id INT,
    waktu_scan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (session_id) REFERENCES event_sessions(id)
);

CREATE TABLE qr_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64),
    event_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME
);

CREATE TABLE event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_user_rsvp ON event_registrations(user_id, status);

INSERT INTO users (nama, nohp, password, role) VALUES
('Super Admin', 'admin', 'admin123', 'admin');
```

3. **Create environment file** `.env` in the project root:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=event_attendance
BASE_URL=http://localhost/your-project-folder
```

4. **Configure your web server** to point to the project directory

5. **Access the application**:
   - Open `http://localhost/your-project-folder` in your browser
   - Login with default admin: Phone: `admin`, Password: `admin123`

---

## Usage Guide

### For Administrators

1. **Login** with admin credentials
2. **Create an Event**:
   - Click "Add Event" on the dashboard
   - Fill in event name, date, and QR mode
   - Add session schedules (e.g., Morning Session 08:00-12:00)
   - Select participants to invite
   - Save the event
3. **Monitor Attendance**:
   - Click the monitor icon to display QR on screen/projector
   - Participants scan the QR during active session times
4. **View Reports**:
   - Go to Reports/History section
   - Filter by event or date range
   - Export to Excel, PDF, or Print

### For Participants

1. **Register** an account (or admin creates one)
2. **Login** with phone number and password
3. **Respond to Invitations**: Confirm or decline event invitations on dashboard
4. **Attend Event**: Scan the displayed QR code using the built-in scanner
5. **View History**: Check attendance records on dashboard

---

## Key Technical Features

### Smart Redirect After Login
When a user scans a QR code but is not logged in, the system saves the intended URL and redirects them back after successful login.

### Session-Based Attendance
Attendance is tied to specific time-based sessions. The system automatically determines which session is active based on current server time and only allows attendance during valid session windows.

### Duplicate Prevention
The system prevents duplicate attendance records for the same user in the same session.

### Dynamic QR Security
In dynamic mode, QR codes expire after 15 seconds and are regenerated automatically, preventing users from sharing screenshots.

---

## Security Recommendations

For production deployment, consider implementing:
- Password hashing using `password_hash()` and `password_verify()`
- Prepared statements for all database queries (prevent SQL injection)
- CSRF token protection on all forms
- HTTPS encryption
- Input validation and sanitization
- Rate limiting for login attempts
- Session timeout configuration

---

## Use Cases

This system is suitable for:
- Corporate conferences and seminars
- Educational workshops and training sessions
- Religious gatherings and study circles
- Community events and meetings
- Government or organizational assemblies
- University lectures and classes
- Any event requiring attendance tracking with RSVP

---

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: Bootstrap 5, jQuery
- **Data Tables**: DataTables.js (with export buttons)
- **QR Generation**: QR Server API
- **QR Scanning**: HTML5-QRCode library
- **Icons**: Font Awesome 6

---

## License

This project is open-source and available for modification and distribution.

---

## Support

For issues and feature requests, please create an issue in the repository or contact me, thanks.
