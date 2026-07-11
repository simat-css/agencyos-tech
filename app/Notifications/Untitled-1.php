composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate


AgencyOS
│
├── Authentication ✅
├── Companies ✅
├── Departments  ← Current Module
├── Roles
├── Permissions
├── Users
├── Activity Logs
├── Notifications
├── AI Action Engine
├── CRM
├── Projects
├── HR
├── Accounts
├── Support
└── Client Portal


public function getAll($search = null)
{
    $query = Company::with('creator');

    if ($search) {
        $query->where('name', 'like', "%{$search}%");
    }

    return $query->latest()->paginate(10);
}
We should not simply remove the security. We need to move it to the routes.

Current Status

✅ Spatie Installed

✅ Permission Seeder

✅ Role Seeder

✅ Middleware aliases registered

✅ Company Module

✅ Department Module


🏢 ABC Company                         + Add Department
Total Departments: 4
-------------------------------------------------------
☑  #   Department     Code   Status   Actions
-------------------------------------------------------
   1   HR             HR01   ON       View Edit Delete
   2   IT             IT01   ON       View Edit Delete



🏢 XYZ Company                         + Add Department
Total Departments: 2
-------------------------------------------------------
☑  #   Department     Code   Status   Actions
-------------------------------------------------------
   1   Sales          SL01   ON

Ab User CRUD complete:

✅ Create
✅ List
✅ Edit
✅ View Profile
✅ Delete
✅ Role Integration
✅ Company Mapping
✅ Department Mapping
✅ Profile Photo
✅ Responsive UI

Next step hoga User Management advanced features:

🔹 AJAX Active/Inactive Toggle
🔹 Live Search (without page reload)
🔹 Company wise Department dependent dropdown
🔹 Activity Log for User actions


:root{

    --primary:#2563EB;
    --success:#22C55E;
    --warning:#F59E0B;
    --danger:#EF4444;

    --sidebar:#081028;
    --sidebar-hover:#14203d;

    --body:#f4f6f9;
}

body{
    margin:0;
    background:var(--body);
    font-family:'Segoe UI',sans-serif;
}

/* Sidebar */

.app-sidebar{

    width:260px;
    min-height:100vh;

    position:fixed;
    left:0;
    top:0;

    background:var(--sidebar);

    z-index:1000;
}

.sidebar-brand{

    height:70px;

    display:flex;
    align-items:center;

    font-size:32px;
    font-weight:700;

    color:#fff;

    padding:0 25px;

    border-bottom:1px solid rgba(255,255,255,.1);
}

.sidebar-menu{
    padding:20px;
}

.menu-heading{

    color:#8b95b5;

    text-transform:uppercase;

    font-size:12px;

    margin-bottom:15px;
    margin-top:25px;
}

.sidebar-menu > a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #d8def0;
    text-decoration: none;
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 8px;
    transition: .3s;
}

.sidebar-menu > a:hover {
    background: var(--sidebar-hover);
    color: #fff;
}

.sidebar-menu > a.active {
    background: var(--primary);
    color: #fff;
}

/* Navbar */

.top-navbar{

    position:fixed;

    top:0;
    left:260px;
    right:0;

    height:70px;

    background:var(--primary);

    z-index:999;
}

.search-box{
    width:250px;
}

.nav-icon{

    color:#fff;

    font-size:20px;

    text-decoration:none;

    position:relative;
}

.badge-count{

    position:absolute;

    top:-8px;
    right:-8px;

    background:red;

    width:18px;
    height:18px;

    border-radius:50%;

    font-size:11px;

    display:flex;
    align-items:center;
    justify-content:center;

    color:white;
}

.user-profile{

    display:flex;
    align-items:center;
    gap:10px;

    color:#fff;
}

.user-profile img{

    width:38px;
    height:38px;

    border-radius:50%;
}

/* Main Content */

.app-main{
    margin-left:260px;
    padding-top:90px;
    transition:all .3s ease;
}

.content-wrapper{
    padding:25px;
}

/* Cards */

.stat-card{

    border:none;

    border-radius:20px;

    box-shadow:0 5px 25px rgba(0,0,0,.05);
}

.stat-icon{

    width:65px;
    height:65px;

    border-radius:15px;

    display:flex;

    align-items:center;
    justify-content:center;

    color:white;

    font-size:24px;
}

.icon-primary{
    background:var(--primary);
}

.icon-success{
    background:var(--success);
}

.icon-warning{
    background:var(--warning);
}

.icon-danger{
    background:var(--danger);
}

/* Dashboard */

.dashboard-title{

    font-size:36px;
    font-weight:700;
}

.dashboard-subtitle{

    color:#6b7280;
}

/* Content Cards */

.content-card{

    border:none;

    border-radius:20px;

    box-shadow:0 5px 25px rgba(0,0,0,.05);
}

/* Footer */

.footer{

    margin-left:260px;

    text-align:center;

    padding:20px;

    color:#6b7280;
}
.sidebar-link{
    display:flex;
    justify-content:space-between;
    align-items:center;
    width:100%;
    color:#fff;
    text-decoration:none;
    padding:12px 15px;
    border-radius:8px;
    transition:.3s;
}
.sidebar-dropdown .collapse.show{
    display:block !important;
    visibility:visible !important;
}

.sidebar-dropdown .collapse{
    visibility:visible !important;
}
.sidebar-link:hover{
    background:#2563eb;
    color:#fff;
}

.sidebar-sub-link{
    display:flex;
    align-items:flex-start;
    gap:10px;

    padding:10px 20px 10px 45px;

    color:#cbd5e1;
    text-decoration:none;
    font-size:14px;
    border-radius:6px;
    margin-top:4px;
}

.sidebar-sub-link:hover{
    background:#1d4ed8;
    color:#fff;
}
.app-sidebar{
    overflow-y: auto;
}

.sidebar-menu{
    overflow: visible !important;
}

.sidebar-dropdown{
    overflow: visible !important;
}

.collapse{
    overflow: visible !important;
}
.disabled-link{
    opacity:.5;
    pointer-events:none;
    cursor:not-allowed;
}
.tooltip-wrapper{
    position:relative;
    display:inline-block;
}

.custom-tooltip{

    position:absolute;

    top:50%;
    left:25px;

    transform:translateY(-50%) scale(.95);

    background:#111827;
    color:#fff;

    padding:8px 14px;

    border-radius:8px;

    font-size:13px;
    font-weight:500;

    white-space:nowrap;

    opacity:0;
    visibility:hidden;

    transition:all .25s ease;

    box-shadow:0 10px 25px rgba(0,0,0,.2);

    z-index:9999;
}

.tooltip-wrapper:hover .custom-tooltip{

    opacity:1;
    visibility:visible;
    transform:translateY(-50%) scale(1);

}

.custom-tooltip::before{

    content:"";

    position:absolute;

    left:-6px;
    top:50%;

    transform:translateY(-50%);

    border-top:6px solid transparent;
    border-bottom:6px solid transparent;
    border-right:6px solid #111827;

}
.dashboard-title{
    font-weight:700;
    font-size:32px;
}

.dashboard-subtitle{
    color:#6c757d;
}


.stat-card{
    border:0;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,.06);
    transition:.3s;
}


.stat-card:hover{
    transform:translateY(-5px);
}


.stat-card small{
    color:#6c757d;
    font-size:14px;
}


.stat-card h2{
    font-weight:700;
    margin-top:10px;
}


.stat-icon{
    width:55px;
    height:55px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}


.icon-primary{
    background:#e7f0ff;
    color:#2563eb;
}


.icon-success{
    background:#dcfce7;
    color:#16a34a;
}


.icon-warning{
    background:#fef3c7;
    color:#d97706;
}


.icon-danger{
    background:#fee2e2;
    color:#dc2626;
}


.content-card{
    border:0;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,.06);
}


.activity-item{
    padding:12px;
    border-radius:10px;
    background:#f8fafc;
}
.search-wrapper{

    position:relative;
    width:350px;

}
.search-result{

    position:absolute;
    top:45px;
    left:0;
    width:100%;
    background:#fff;
    border-radius:8px;
    z-index:9999;
    display:none;

}
.search-result .result-item{

    padding:10px;
    border-bottom:1px solid #eee;
    cursor:pointer;

}
.search-result .result-item:hover{

    background:#f5f5f5;

}
/* ==================================
   TABLET
================================== */

@media (max-width:991px){

    .app-sidebar{
        width:70px;
    }

    .top-navbar{
        left:70px;
    }

    .app-main{
        margin-left:70px;
        padding-top:90px;
    }

    .footer{
        margin-left:70px;
    }

    .sidebar-brand{
        justify-content:center;
        font-size:0;
    }

    .sidebar-brand i{
        font-size:24px;
    }

    .menu-heading{
        display:none;
    }

    .sidebar-menu a span,
    .sidebar-sub-link{
        display:none;
    }

    .sidebar-link{
        justify-content:center;
    }

    .search-wrapper{
        width:220px;
    }
}

/* Tablet */

@media (max-width:991px){

    .app-sidebar{
        width:70px;
        transition:.3s;
    }

    .top-navbar{
        left:70px;
    }

    .app-main{
        margin-left:70px;
    }

    .footer{
        margin-left:70px;
    }
}

/* Mobile */

@media (max-width:768px){

    .app-sidebar{
        width:260px;
        transform:translateX(-100%);
        transition:.3s;
    }

    .app-sidebar.show{
        transform:translateX(0);
    }

    .top-navbar{
        left:0;
    }

    .app-main{
        margin-left:0;
    }

    .footer{
        margin-left:0;
    }
}

php artisan make:seeder UserPermissionSeeder



$user = App\Models\User::find(10);


git commit -m "Authentication  Module and Company Module and User Module"





array_intersectTest Commands

✅ Success

Create company named AI Future Tech with email futuretech@gmail.com phone 8888888888 address Mumbai

✅ Duplicate

Create company named AI Future Tech with email futuretech@gmail.com phone 8888888888 address Mumbai

Response:

Company with email futuretech@gmail.com already exists.

✅ Missing Data

Create company named AI Future Tech with email futuretech@gmail.com

Response:

Company name, email, phone and address are required.




//ai 
Testing ke liye AI Company Management ki complete command list (Create → View → Update → Status → Delete → Notifications → Statistics) ye hai:

---

## 1. Create Company

**Command:**

```
create company named AI Future Tech with email futuretech@gmail.com phone 8888888888 address Mumbai
```

Expected:

```
Company AI Future Tech has been created successfully.
```

Check:

* Company table
* Activity Log
* Notification

---

# 2. Show Active Companies

```
show active companies
```

Expected:

```
📋 ACTIVE COMPANIES

1) AI Future Tech

Total Active Companies: 1
```

---

# 3. Show Inactive Companies

```
show inactive companies
```

Expected:

```
📋 INACTIVE COMPANIES

Company list...
```

---

# 4. Show Company Details

```
show company AI Future Tech
```

Expected:

```
🏢 COMPANY DETAILS

Name: AI Future Tech
Email: futuretech@gmail.com
Phone: 8888888888
Address: Mumbai
Status: Active
Departments: 0
```

---

# 5. Search Company

```
search company Future
```

Expected:

```
🔍 SEARCH RESULTS

1) AI Future Tech
```

---

# 6. Company Statistics

```
show company statistics
```

Expected:

```
📊 COMPANY STATISTICS

Total Companies: X
Active Companies: X
Inactive Companies: X

Active Percentage: XX%
```

---

# 7. Deactivate Company

```
deactivate company AI Future Tech
```

Expected:

```
Company AI Future Tech has been deactivated successfully.
```

Check:

* status = 0
* Activity Log
* Notification

---

# 8. Activate Company

```
activate company AI Future Tech
```

Expected:

```
Company AI Future Tech has been activated successfully.
```

Check:

* status = 1
* Activity Log
* Notification

---

# 9. Update Company (Email)

```
update company AI Future Tech email ai@gmail.com
```

Response:

```
⚠️ UPDATE COMPANY CONFIRMATION

🏢 Company Details

Name: AI Future Tech
Email: futuretech@gmail.com

Changes:
Email → ai@gmail.com

To confirm update, type:
confirm
```

Then:

```
confirm
```

Expected:

```
Company AI Future Tech has been updated successfully.
```

Check:

* Database updated
* Activity Log
* Notification

---

# 10. Update Company Phone

```
update company AI Future Tech phone 9999999999
```

Then:

```
confirm
```

---

# 11. Update Company Address

```
update company AI Future Tech address Delhi
```

Then:

```
confirm
```

---

# 12. Delete Company (Soft Delete)

First:

```
delete company AI Future Tech
```

Response:

```
⚠️ DELETE COMPANY CONFIRMATION

Company Details...

Warning:
This action will soft delete the company.

To confirm deletion, type:
confirm delete company AI Future Tech
```

Confirm:

```
confirm delete company AI Future Tech
```

OR:

```
confirm
```

(if pending delete session support hai)

Expected:

```
Company AI Future Tech has been deleted successfully.
```

Check:

* `deleted_at` filled
* Activity Log
* Notification

---

# 13. Latest Notifications

```
show latest notifications
```

Expected:

```
🔔 LATEST NOTIFICATIONS

1) Company AI Future Tech created via AI Assistant.
2) Company AI Future Tech updated via AI Assistant.
3) Company AI Future Tech deleted via AI Assistant.
```

---

# Negative Testing

## Wrong Company

```
show company ABC Company
```

Expected:

```
Company ABC Company not found.
```

---

## Duplicate Email

```
create company named Test Company with email futuretech@gmail.com phone 1111111111 address Delhi
```

Expected:

```
Company with email futuretech@gmail.com already exists.
```

---

## Update Without Confirm

```
update company AI Future Tech email test@gmail.com
```

Database change nahi hona chahiye jab tak:

```
confirm
```

na bhejo.

---

Ye complete Module 1 Company AI Action Engine testing flow cover karega:
✅ Create
✅ Read
✅ Search
✅ Statistics
✅ Activate/Deactivate
✅ Update with Confirmation
✅ Delete with Soft Delete Confirmation
✅ Activity Logs
✅ Notifications
