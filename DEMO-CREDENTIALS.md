# FRMS Demo Credentials

**Real-Time Facial Recognition-Based Monitoring and Alert System**  
Southern University College — FYP Prototype

---

## Quick Start

| Item | Value |
|---|---|
| **Login URL** | `http://localhost:8000` |
| **Start server** | `php artisan serve --port=8000` |
| **Seed database** | `php artisan migrate:fresh --seed` |
| **Default Password** | `password` _(same for all accounts)_ |

---

## Roles

| Role | Description | Dashboard |
|---|---|---|
| **Super Admin (PDRM IT Department)** | Highest-level system admin. Responsible for overall system governance and access control. | `Admin Dashboard` |
| **PDRM Officer** | Case management and investigation unit. Handles missing person cases, monitors alerts, and verifies public submissions. | `PDRM Officer Dashboard` |
| **Patrol Unit** | Operational police units. Receives confirmed alerts from PDRM officers and takes immediate field action. | `Patrol Unit Dashboard` |
| **Public User** | Citizens who can lodge missing person reports. Cases are verified by PDRM officers before facial recognition begins. | `Public Dashboard` |

---

## Demo Accounts

### 🔴 Super Admin (PDRM IT Department)

| # | Name | Email | Agency |
|---|---|---|---|
| 1 | DCP Azman bin Ibrahim | `admin@frms.gov.my` | PDRM IT Department, Bukit Aman |

---

### 🔵 PDRM Officers (Case Management & Investigation)

| # | Name | Email | Agency |
|---|---|---|---|
| 2 | ASP Kamal bin Hassan | `kamal@frms.gov.my` | IPD Dang Wangi, Kuala Lumpur |
| 3 | Insp. Tan Mei Hua | `tan@frms.gov.my` | IPD Petaling Jaya, Selangor |
| 4 | Insp. Norazlina binti Kamarudin | `norazlina@frms.gov.my` | IPD Johor Bahru, Johor |
| 5 | Insp. Rajesh a/l Muthu | `rajesh@frms.gov.my` | IPD George Town, Penang |
| 6 | Insp. Lim Wei Jie | `lim@frms.gov.my` | IPD Kuching, Sarawak |

---

### 🟡 Patrol Unit (Field Operations)

| # | Name | Email | Agency |
|---|---|---|---|
| 7 | Sjn. Mohd Fauzi bin Abdullah | `fauzi@frms.gov.my` | Unit Peronda, IPD Dang Wangi |
| 8 | Kpl. Ahmad Syafiq bin Razak | `syafiq@frms.gov.my` | Unit Peronda, IPD Petaling Jaya |

---

### 🟢 Public Users (Citizens)

| # | Name | Email | Agency |
|---|---|---|---|
| 9 | Puan Sarah binti Ismail | `sarah@frms.gov.my` | — |
| 10 | Encik Rahim bin Hassan | `rahim@frms.gov.my` | — |

---

## Workflow

### Public Submission → Verification → Monitoring

```
1. Public User submits missing person report (name, photo, last seen details)
   ↓
2. Case created with status: Pending Verification
   ↓
3. PDRM Officer reviews the case and verifies the information
   ↓
4. Officer clicks "Verify & Activate" → Status changes to Active
   ↓
5. System begins facial recognition monitoring against camera feeds
   ↓
6. Detections generate Alerts when similarity score is high
   ↓
7. PDRM Officer reviews alerts, assigns to Patrol Unit if needed
   ↓
8. Patrol Unit receives alert and takes field action
```

### Officer Direct Creation (Auto-Verified)

```
1. PDRM Officer creates missing person case directly
   ↓
2. Case is auto-verified (status: Active)
   ↓
3. Facial recognition monitoring begins immediately
```

---

## Seeder Data Summary

The `php artisan migrate:fresh --seed` command populates the database with:

- **10 users** — across all 4 roles
- System settings
- **15 cameras** — across multiple locations
- **50 missing persons** — mix of Active, Pending, Found, Closed, Reopened
- **200 detections** — all referencing missing persons with realistic similarity scores
- **75 alerts** — generated from detections, assigned to PDRM officers and patrol units
- **40 verification requests** — identity verification workflow
- Notifications and audit logs

All facial recognition data is **mock/simulated** — this is a prototype system.

---

## Database Connection (`.env`)

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=fyp_system
DB_USERNAME=postgres
DB_PASSWORD=1234
```
