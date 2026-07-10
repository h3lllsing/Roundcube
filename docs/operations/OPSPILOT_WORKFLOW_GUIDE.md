# OpsPilot Workflow Guide

## Contents

- [A. First Day Setup](#a-first-day-setup)
- [B. New Hosting/Domain Onboarding](#b-new-hostingdomain-onboarding)
- [C. VPS Onboarding](#c-vps-onboarding)
- [D. Employee Onboarding](#d-employee-onboarding)
- [E. Employee Exit](#e-employee-exit)
- [F. Password Reveal Workflow](#f-password-reveal-workflow)
- [G. Renewal Workflow](#g-renewal-workflow)

---

## A. First Day Setup

This is for Super Admin setting up OpsPilot for the first time.

### Step 1: Login
1. Open the portal URL
2. Login with your Super Admin credentials
3. You will see the Dashboard

### Step 2: Configure SMTP (Required for email notifications)
1. Go to **SMTP Profiles** (Administration → SMTP Profiles)
2. Click **Create SMTP Profile**
3. Enter your email server details:
   - Name: "Company Email"
   - Sender Email: notifications@yourcompany.com
   - SMTP Host: smtp.yourprovider.com
   - SMTP Port: 587
   - Encryption: tls
   - Username and Password
4. Click **Save**
5. Click **Test** to verify the connection
6. Click **Set as Default**

### Step 3: Create Service Providers
1. Go to **Service Providers** (Infrastructure → Service Providers)
2. Click **Create**
3. Add each provider you use:
   - GoDaddy (domains)
   - DigitalOcean (hosting/VPS)
   - AWS (cloud services)
   - Google Workspace (email)
   - Any other providers
4. Repeat for each provider

### Step 4: Create Users
1. Go to **Users** (Administration → Users)
2. Click **Create**
3. For each team member:
   - Enter Name, Email, Password
   - Assign a Role (User, Admin, or Super Admin)
4. Click **Save**

### Step 5: Assign Roles and Permissions
1. Go to **Module Permissions** (Administration → Permissions)
2. For each module:
   - Select a role
   - Set Create, Read, Update, Delete, Export permissions
3. Click **Save**

### Step 6: Test Everything
1. Login as a test user to verify permissions
2. Go to an Expiry Tracker → Click **Test Email**
3. Verify the email is received
4. Check Dashboard widgets show data

---

## B. New Hosting/Domain Onboarding

When you buy a new domain and hosting:

### Step 1: Add Service Provider (if not already added)
1. Go to **Service Providers → Create**
2. Enter provider name (e.g., "Namecheap")
3. Click **Save**

### Step 2: Add Domain
1. Go to **Domains → Create**
2. Enter the domain name (e.g., "mynewwebsite.com")
3. Select the Service Provider
4. Set Registration Date and Expiry Date
5. Enter Cost (monthly equivalent)
6. Set Status: Active
7. Click **Save**

### Step 3: Add Hosting
1. Go to **Hosting → Create**
2. Enter a name (e.g., "My Website Hosting")
3. Select the Service Provider
4. Enter cPanel URL, username, password
5. Link to the Domain (if applicable)
6. Set Cost, Expiry Date
7. Click **Save**

### Step 4: Add Domain Emails
1. Go to **Domain Emails → Create**
2. Enter the email address (e.g., "info@mynewwebsite.com")
3. Select the Domain
4. Select Service Provider (if different)
5. Enter storage, password
6. Click **Save**

### Step 5: Add Expiry Tracking
1. Go to **Renewals → Create**
2. For the domain:
   - Name: "mynewwebsite.com Renewal"
   - Expiry Date: (from domain record)
   - Cost: (monthly equivalent)
   - SMTP Profile: select your SMTP
   - Enable Email Notifications
   - Set Notify Days: 30, 7, 1
   - Click **Save**
3. Repeat for hosting if it has its own expiry

### Step 6: Verify Dashboard
1. Go to Dashboard
2. Check Operations Widget shows the new services
3. Check Renewals Widget shows upcoming expiry
4. Check Calendar shows the expiry dates

---

## C. VPS Onboarding

When you set up a new VPS:

### Step 1: Add Provider (if needed)
1. Go to **Service Providers → Create**
2. Enter the VPS provider (e.g., "DigitalOcean", "Linode")
3. Click **Save**

### Step 2: Add VPS
1. Go to **VPS → Create**
2. Enter a name (e.g., "web-server-01")
3. Select Service Provider
4. Enter:
   - IP Address
   - OS (e.g., Ubuntu 22.04)
   - RAM, Disk, CPU
   - Plan name
   - Cost
   - Expiry Date (if applicable)
5. Click **Save**

### Step 3: Add Credentials (Vault)
1. Go to **Vault → Shared Credentials → Create**
2. If this VPS has a shared root password:
   - Service Name: "web-server-01 Root Access"
   - Username: root
   - Password: (the password)
   - Description: "Root access for web-server-01"
   - Module: select VPS module
3. Click **Save**

### Step 4: Add Expiry Tracking (if VPS has expiry)
1. Go to **Renewals → Create**
2. Name: "web-server-01 Renewal"
3. Expiry Date, Cost, SMTP Profile
4. Click **Save**

### Step 5: Add Notes (optional)
1. Go to **Notes → Create**
2. Write important information about this VPS
3. Example: "This server runs the company website and CRM."
4. Click **Save**

---

## D. Employee Onboarding

When a new team member joins:

### Step 1: Create User Account
1. Go to **Users → Create**
2. Enter:
   - Name: "Sara Ahmed"
   - Email: sara@company.com
   - Password: (temporary, user will change)
3. Assign role:
   - IT Support → "User" role with module permissions
   - Manager → "Admin" role
   - System Admin → "Super Admin" role
4. Click **Save**

### Step 2: Assign Module Permissions (if needed)
1. Go to **Module Permissions**
2. If using a custom role, set permissions for the modules the employee needs
3. Or set User Overrides for specific modules

### Step 3: Assign Assets
1. Go to **Assets**
2. Find the laptop, monitor, or other equipment
3. Click **Assign**
4. Select the new employee
5. The asset status changes to "Assigned"

### Step 4: Assign Tasks
1. Go to **Tasks → Create**
2. Create onboarding tasks:
   - "Complete IT orientation"
   - "Set up development environment"
   - "Read OpsPilot documentation"
3. Set the new employee as assignee (if using API)
4. Set due dates and priorities

### Step 5: Verify Access
1. Ask the employee to login
2. Check they can see the modules they need
3. Check **My Access** to verify permissions

---

## E. Employee Exit

When a team member leaves:

### Step 1: Suspend User
1. Go to **Users**
2. Find the employee
3. Click **Suspend**
4. Enter a reason for suspension
5. The user can no longer login

### Step 2: Transfer Tasks
1. Go to **Tasks**
2. Find tasks assigned to the departing employee
3. Edit each task and reassign to another team member
4. Note: this must be done via API (web form does not support assignee editing yet)

### Step 3: Return Assets
1. Go to **Assets**
2. Find assets assigned to the departing employee
3. Click **Return** on each asset
4. The asset status changes to "Available"
5. Update condition if needed (e.g., "Good", "Fair")

### Step 4: Review Vault Access
1. Check if the employee had any personal Vault entries
2. Transfer shared credentials if needed
3. No action needed for Shared Vault (employee cannot access after suspension)

### Step 5: Check Audit Logs
1. Go to **Activity Logs**
2. Filter by the departing employee's name
3. Review recent activity for anything unusual
4. Check **Login Audits** for failed login attempts after exit

---

## F. Password Reveal Workflow

When you need to see a stored password:

### Step 1: Navigate to the Record
1. Go to the module containing the password
2. Open the specific record

### Step 2: Reveal the Password
- **For Vault:** Click **Reveal Password**
- **For Module (Hosting, VPS, etc.):** Click the password field or the reveal button

### Step 3: Use the Password
1. The password appears in plain text
2. Type it where needed (or use copy if available)
3. The system logs this reveal automatically

### Step 4: Verify After Reveal
1. Check **Activity Logs** to confirm the reveal was logged
2. The log entry shows:
   - Who revealed
   - Which record
   - When

### Who Can Reveal

| Role | Vault Reveal | Module Password Reveal |
|------|-------------|----------------------|
| Super Admin | Any Vault entry | Any module record |
| Admin | Vault entries with Reveal permission | Module records with Reveal permission |
| User | Their own Vault entries | Their own records (if module allows) |

---

## G. Renewal Workflow

When you need to process a renewal:

### Step 1: Check Dashboard
1. Go to Dashboard
2. Look at **Renewals Widget**
3. See which services are expiring soon
4. Check for any failed notifications

### Step 2: Review Expiry Trackers
1. Go to **Renewals**
2. Filter by:
   - Status: Active
   - Expiry Date: upcoming 30 days
3. Review each tracker that needs attention

### Step 3: Test SMTP (if problems suspected)
1. Go to **SMTP Profiles**
2. Click **Test** on the relevant profile
3. Verify test email is received

### Step 4: Send Manual Reminder (if needed)
1. Open the Expiry Tracker
2. Click **Send Reminder Now**
3. Confirm the email was sent

### Step 5: Confirm Activity Log
1. Go to **Activity Logs**
2. Search for "reminder" or the tracker name
3. Verify the reminder was logged

### Step 6: Process the Renewal
1. Login to the service provider's website
2. Renew the service
3. Come back to OpsPilot
4. Update the Expiry Date in the tracker
5. Update the Status if needed
6. Click **Save**
