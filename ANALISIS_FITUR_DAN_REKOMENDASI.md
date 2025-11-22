# 📊 Analisis Fitur TMS Hybrid & Rekomendasi Pengembangan

**Tanggal Analisis:** 21 November 2025  
**Versi Aplikasi:** TMS Hybrid v1.0  
**Stack:** Laravel 12, PHP 8.2, MySQL, Blade + Tailwind CSS

---

## 🎯 Executive Summary

TMS Hybrid adalah sistem manajemen transportasi yang sudah memiliki **fondasi yang sangat kuat** dengan integrasi akuntansi yang komprehensif. Sistem ini sudah mencakup 80% kebutuhan operasional perusahaan jasa transportasi. Namun, ada beberapa fitur tambahan yang akan meningkatkan efisiensi dan memberikan nilai lebih untuk manajemen perusahaan.

---

## ✅ FITUR YANG SUDAH ADA (Existing Features)

### 📦 1. **Modul Master Data**
- ✅ Customer Management (dengan contact person)
- ✅ Vendor Management (dengan PIC dan bank accounts)
- ✅ Truck Management (dengan driver assignment)
- ✅ Driver Management
- ✅ Equipment Management
- ✅ Sales Management
- ✅ Routes Management

### 🚚 2. **Modul Operasional**

#### Job Order System
- ✅ Job Order Creation & Management
- ✅ Multi-item cargo (Job Order Items)
- ✅ Status tracking (draft, confirmed, in_progress, completed, cancelled)
- ✅ Cancellation with reason tracking
- ✅ Invoice status tracking (not_invoiced, partially_invoiced, fully_invoiced)
- ✅ Board view untuk visualisasi status

#### Shipment Leg System (Multi-Moda)
- ✅ Multiple shipment legs per job order
- ✅ Support untuk berbagai cost categories:
  - Trucking (internal/vendor)
  - Sea Freight
  - Air Freight
  - Asuransi (Insurance) dengan billable option
  - PIC (Pengurusan Import/Export)
- ✅ Main costs dengan tax calculation (PPN & PPh 23)
- ✅ Additional costs (detention, storage, handling) dengan billable option
- ✅ Vendor assignment per leg
- ✅ Auto-generate vendor bills dari shipment legs

#### Transport System
- ✅ Transport management (planned, on_route, delivered, closed)
- ✅ Transport costs tracking
- ✅ Executor type (internal vs vendor)

### 💰 3. **Modul Finance**

#### Invoice Management (Customer)
- ✅ Multi-select job orders untuk satu invoice
- ✅ Auto-preview items dari job order
- ✅ Auto-include billable additional costs
- ✅ Tax calculation (PPN 11%)
- ✅ PPh 23 calculation & display
- ✅ Invoice status workflow (draft, sent, paid, partial, overdue, cancelled)
- ✅ Mark as sent dengan journal posting
- ✅ Revert to draft functionality
- ✅ Invoice preview modal dengan DRAFT watermark
- ✅ Reference field untuk tracking

#### Vendor Bill Management
- ✅ Vendor bill tracking
- ✅ Mark as received
- ✅ Mark as paid dengan journal posting
- ✅ Tax handling (PPN & PPh 23)
- ✅ Integration dengan payment requests

#### Payment Request System
- ✅ Payment request creation untuk vendor bills
- ✅ Payment request creation untuk driver advances
- ✅ Approval workflow (pending, approved, rejected, paid)
- ✅ Outstanding tracking (prevent double requests)
- ✅ Manual payment request support
- ✅ Vendor bank account selection
- ✅ Progress percentage tracking

#### Driver Advance System
- ✅ Driver advance tracking per shipment leg
- ✅ DP (Down Payment) management
- ✅ Settlement calculation dengan deductions
- ✅ Status workflow (pending, dp_paid, settled)
- ✅ Integration dengan payment requests
- ✅ Outstanding advance tracking

#### Payment Receipt System (Customer)
- ✅ Payment receipt creation
- ✅ Allocation to invoices
- ✅ Deallocation support
- ✅ Payment method tracking

#### Cash & Bank Management
- ✅ Cash/Bank account management
- ✅ Transaction tracking (cash_in, cash_out)
- ✅ Source tracking (customer_payment, vendor_payment, expense)
- ✅ Integration dengan invoice & vendor bill payments
- ✅ Expense recording
- ✅ PPh 23 withholding tracking

### 📊 4. **Modul Akuntansi (Accounting)**

#### Chart of Accounts
- ✅ Complete COA structure
- ✅ Account types (asset, liability, equity, revenue, expense)
- ✅ Account categories
- ✅ Active/inactive status

#### Journal System
- ✅ Journal entry creation
- ✅ Journal lines dengan debit/credit
- ✅ Auto-posting dari transactions
- ✅ Journal service untuk automated posting:
  - Invoice posting
  - Customer payment posting
  - Vendor bill posting
  - Vendor payment posting
  - Expense posting
  - PPh 23 handling

#### Fiscal Period Management
- ✅ Fiscal period creation
- ✅ Period closing
- ✅ Period reopening
- ✅ Period locking

#### Financial Reports
- ✅ Trial Balance (Neraca Saldo)
- ✅ General Ledger (Buku Besar)
- ✅ Profit & Loss Statement (Laba Rugi)
- ✅ Balance Sheet (Neraca)
- ✅ Date range filtering
- ✅ Account-specific filtering

### 📦 5. **Modul Inventory (Parts Management)**
- ✅ Parts master data
- ✅ Part stock tracking
- ✅ Part purchases dengan vendor bills
- ✅ Part usage tracking
- ✅ Tax handling (PPN & PPh 23)
- ✅ Inventory dashboard

### 🤖 6. **AI Assistant**
- ✅ AI-powered analysis untuk semua data TMS & Keuangan
- ✅ Natural language queries
- ✅ Intent detection (piutang, hutang, profit, cash flow, dll)
- ✅ Data aggregation & summarization
- ✅ Integration dengan Gemini API
- ✅ Chat interface dengan dark mode

### 👥 7. **User Management & Security**
- ✅ User authentication
- ✅ Role-based access (super_admin, admin, finance, operations, viewer)
- ✅ User status (active/inactive)
- ✅ Menu-based permissions
- ✅ Notification system

### 🎨 8. **UI/UX Features**
- ✅ Dark mode theme
- ✅ Responsive design dengan Tailwind CSS
- ✅ Modal-based workflows
- ✅ Real-time notifications
- ✅ Board view untuk job orders
- ✅ Preview modals (invoice preview)

---

## 🚀 FITUR YANG PERLU DITAMBAHKAN (Recommended Features)

### 🔴 **PRIORITAS TINGGI** (Critical for Business Operations)

#### 1. **Dashboard & Analytics** ⭐⭐⭐⭐⭐
**Status:** Belum ada dashboard yang komprehensif

**Yang Dibutuhkan:**
- **Main Dashboard:**
  - KPI Cards: Total Revenue, Outstanding Receivables, Outstanding Payables, Net Profit
  - Chart: Revenue vs Expense (monthly trend)
  - Chart: Job Order status distribution
  - Recent activities (latest invoices, payments, job orders)
  - Alerts: Overdue invoices, pending approvals, low cash balance

- **Finance Dashboard:**
  - Aging Report (Piutang & Hutang)
  - Cash Flow Chart (30 days)
  - Top 10 Customers by Revenue
  - Top 10 Vendors by Expense
  - Profit Margin per Job Order

- **Operations Dashboard:**
  - Active Job Orders map/timeline
  - Truck utilization rate
  - Driver performance metrics
  - On-time delivery rate
  - Pending shipment legs

**Estimasi Waktu:** 3-5 hari  
**Kompleksitas:** Medium-High  
**Impact:** Very High

---

#### 2. **Document Management & File Upload** ⭐⭐⭐⭐⭐
**Status:** Tidak ada sistem upload dokumen

**Yang Dibutuhkan:**
- Upload dokumen untuk:
  - Job Orders (PO, Surat Jalan, Delivery Order)
  - Invoices (Invoice PDF, Bukti Kirim)
  - Vendor Bills (Tagihan Vendor, Faktur Pajak)
  - Payment Requests (Bukti Transfer, Kwitansi)
  - Driver Advances (SPJ, Bukti Pengeluaran)
  - Shipment Legs (Packing List, Bill of Lading, AWB)

- Fitur:
  - Multiple file upload per document
  - File type validation (PDF, JPG, PNG, Excel)
  - File size limit (max 10MB per file)
  - Preview untuk PDF & images
  - Download & delete files
  - File versioning (optional)

**Database Schema:**
```sql
CREATE TABLE document_attachments (
    id BIGINT PRIMARY KEY,
    attachable_type VARCHAR(255), -- polymorphic
    attachable_id BIGINT,
    file_name VARCHAR(255),
    file_path VARCHAR(500),
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by BIGINT,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Estimasi Waktu:** 2-3 hari  
**Kompleksitas:** Medium  
**Impact:** Very High

---

#### 3. **PDF Generation untuk Semua Dokumen** ⭐⭐⭐⭐⭐
**Status:** Sudah ada route untuk invoice PDF tapi belum diimplementasi

**Yang Dibutuhkan:**
- **Invoice PDF:**
  - Professional template dengan company logo
  - Customer details & invoice details
  - Item breakdown dengan tax
  - Payment terms & bank details
  - QR Code untuk payment (optional)

- **Vendor Bill PDF:**
  - Vendor details
  - Bill items
  - Tax breakdown

- **Payment Receipt PDF:**
  - Official receipt format
  - Allocation details

- **Job Order PDF:**
  - Complete job order details
  - Cargo list
  - Shipment legs summary

- **Driver Advance PDF:**
  - SPJ format
  - Expense breakdown
  - Settlement calculation

**Library:** Laravel DomPDF atau Laravel Snappy (wkhtmltopdf)

**Estimasi Waktu:** 3-4 hari  
**Kompleksitas:** Medium  
**Impact:** Very High

---

#### 4. **Email Notification System** ⭐⭐⭐⭐
**Status:** Notification table ada tapi belum ada email

**Yang Dibutuhkan:**
- Email notifications untuk:
  - Invoice sent to customer (dengan PDF attachment)
  - Payment reminder (3 days before due, on due date, after overdue)
  - Payment received confirmation
  - Vendor bill received
  - Payment request approval needed
  - Payment request approved/rejected
  - Job order status updates

- Email templates:
  - Professional HTML templates
  - Company branding
  - Responsive design

- Configuration:
  - SMTP settings di .env
  - Email queue untuk performa
  - Email log tracking

**Estimasi Waktu:** 2-3 hari  
**Kompleksitas:** Medium  
**Impact:** High

---

#### 5. **Advanced Search & Filtering** ⭐⭐⭐⭐
**Status:** Filter dasar ada, tapi belum advanced

**Yang Dibutuhkan:**
- **Global Search:**
  - Search across all modules (Job Orders, Invoices, Customers, Vendors)
  - Quick search di navbar
  - Search results page dengan kategori

- **Advanced Filters per Module:**
  - Job Orders: By customer, status, date range, sales person, route
  - Invoices: By customer, status, date range, amount range, overdue
  - Vendor Bills: By vendor, status, date range, amount range
  - Payment Requests: By status, payment type, vendor, amount range

- **Saved Filters:**
  - User dapat save filter favorit
  - Quick access ke saved filters

**Estimasi Waktu:** 2-3 hari  
**Kompleksitas:** Medium  
**Impact:** High

---

### 🟡 **PRIORITAS SEDANG** (Important for Efficiency)

#### 6. **Recurring Invoice System** ⭐⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- Create recurring invoice templates
- Schedule: Daily, Weekly, Monthly, Quarterly, Yearly
- Auto-generate invoices based on schedule
- Email notification saat invoice di-generate
- Pause/Resume recurring invoices

**Use Case:** Customer dengan kontrak bulanan/regular shipment

**Estimasi Waktu:** 2-3 hari  
**Kompleksitas:** Medium  
**Impact:** Medium-High

---

#### 7. **Quotation/Offer System** ⭐⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- Create quotation sebelum job order
- Quotation items dengan pricing
- Send quotation to customer (email + PDF)
- Quotation status: Draft, Sent, Accepted, Rejected, Expired
- Convert quotation to job order (one-click)
- Quotation validity period

**Workflow:**
```
Quotation (Draft) → Send to Customer → Accepted → Convert to Job Order
```

**Estimasi Waktu:** 3-4 hari  
**Kompleksitas:** Medium  
**Impact:** Medium-High

---

#### 8. **Customer Portal** ⭐⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- Separate login untuk customers
- Customer dapat:
  - View job orders mereka
  - Track shipment status (real-time)
  - View & download invoices
  - View payment history
  - Upload PO/documents
  - Submit feedback/complaints

**Estimasi Waktu:** 5-7 hari  
**Kompleksitas:** High  
**Impact:** Medium-High

---

#### 9. **Vendor Portal** ⭐⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- Separate login untuk vendors
- Vendor dapat:
  - View assigned shipment legs
  - Update shipment status
  - Upload documents (POD, Surat Jalan)
  - View vendor bills
  - View payment status
  - Submit invoices

**Estimasi Waktu:** 5-7 hari  
**Kompleksitas:** High  
**Impact:** Medium

---

#### 10. **GPS Tracking Integration** ⭐⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- Integration dengan GPS device (via API)
- Real-time truck location
- Route history
- Geofencing alerts
- ETA calculation
- Map view untuk active shipments

**Catatan:** Memerlukan GPS hardware & API dari provider

**Estimasi Waktu:** 7-10 hari (tergantung GPS provider)  
**Kompleksitas:** High  
**Impact:** Medium-High

---

#### 11. **Expense Approval Workflow** ⭐⭐⭐
**Status:** Cash bank expense langsung tercatat, belum ada approval

**Yang Dibutuhkan:**
- Multi-level approval untuk expenses
- Approval rules berdasarkan amount
- Approval history tracking
- Email notification untuk approver
- Reject dengan reason

**Workflow:**
```
Expense Request → Manager Approval → Finance Approval → Paid
```

**Estimasi Waktu:** 2-3 hari  
**Kompleksitas:** Medium  
**Impact:** Medium

---

#### 12. **Budget Management** ⭐⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- Set budget per:
  - Department
  - Cost category
  - Project/Job Order
  - Monthly/Quarterly/Yearly
- Budget vs Actual comparison
- Alert saat mendekati/melebihi budget
- Budget report

**Estimasi Waktu:** 3-4 hari  
**Kompleksitas:** Medium  
**Impact:** Medium

---

### 🟢 **PRIORITAS RENDAH** (Nice to Have)

#### 13. **Mobile App (Progressive Web App)** ⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- PWA untuk driver & field staff
- Features:
  - Update shipment status
  - Upload POD (Proof of Delivery)
  - Capture photos
  - Digital signature
  - Offline capability

**Estimasi Waktu:** 10-14 hari  
**Kompleksitas:** High  
**Impact:** Medium

---

#### 14. **WhatsApp Integration** ⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- Send notifications via WhatsApp
- WhatsApp Business API integration
- Notifications untuk:
  - Invoice sent
  - Payment reminder
  - Shipment updates
  - Payment received

**Estimasi Waktu:** 3-5 hari  
**Kompleksitas:** Medium  
**Impact:** Low-Medium

---

#### 15. **Barcode/QR Code System** ⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- Generate barcode/QR untuk:
  - Job Orders
  - Cargo items
  - Invoices
- Scan barcode untuk:
  - Quick search
  - Status update
  - Document verification

**Estimasi Waktu:** 2-3 hari  
**Kompleksitas:** Low-Medium  
**Impact:** Low-Medium

---

#### 16. **Fuel Management System** ⭐⭐
**Status:** Belum ada (fuel cost tercatat di transport costs)

**Yang Dibutuhkan:**
- Fuel card integration
- Fuel consumption tracking per truck
- Fuel efficiency report
- Fuel price tracking
- Budget vs actual fuel cost

**Estimasi Waktu:** 3-4 hari  
**Kompleksitas:** Medium  
**Impact:** Low-Medium

---

#### 17. **Maintenance Management** ⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- Scheduled maintenance tracking
- Maintenance history per truck
- Spare parts usage
- Maintenance cost tracking
- Maintenance reminder

**Estimasi Waktu:** 4-5 hari  
**Kompleksitas:** Medium  
**Impact:** Low-Medium

---

#### 18. **Contract Management** ⭐⭐
**Status:** Belum ada

**Yang Dibutuhkan:**
- Customer contracts
- Vendor contracts
- Contract terms & pricing
- Contract renewal reminder
- Contract performance tracking

**Estimasi Waktu:** 3-4 hari  
**Kompleksitas:** Medium  
**Impact:** Low-Medium

---

#### 19. **Multi-Currency Support** ⭐
**Status:** Belum ada (semua dalam Rupiah)

**Yang Dibutuhkan:**
- Support multiple currencies
- Exchange rate management
- Currency conversion
- Multi-currency reports

**Estimasi Waktu:** 5-7 hari  
**Kompleksitas:** High  
**Impact:** Low (jika tidak ada transaksi internasional)

---

#### 20. **API for Third-Party Integration** ⭐
**Status:** Belum ada public API

**Yang Dibutuhkan:**
- RESTful API
- API authentication (OAuth2/API Token)
- API documentation
- Rate limiting
- Webhook support

**Estimasi Waktu:** 7-10 hari  
**Kompleksitas:** High  
**Impact:** Low-Medium

---

## 📋 ROADMAP REKOMENDASI

### **Phase 1: Essential Features (1-2 Bulan)**
1. ✅ Dashboard & Analytics (Week 1)
2. ✅ Document Management (Week 2)
3. ✅ PDF Generation (Week 3)
4. ✅ Email Notifications (Week 4)
5. ✅ Advanced Search & Filtering (Week 5-6)

**Total Estimasi:** 5-6 minggu

---

### **Phase 2: Business Enhancement (2-3 Bulan)**
1. ✅ Quotation System (Week 7-8)
2. ✅ Recurring Invoices (Week 9)
3. ✅ Expense Approval Workflow (Week 10)
4. ✅ Budget Management (Week 11-12)

**Total Estimasi:** 5-6 minggu

---

### **Phase 3: Customer & Vendor Experience (3-4 Bulan)**
1. ✅ Customer Portal (Week 13-14)
2. ✅ Vendor Portal (Week 15-16)
3. ✅ GPS Tracking (Week 17-18)

**Total Estimasi:** 6 minggu

---

### **Phase 4: Advanced Features (Optional)**
1. Mobile App (PWA)
2. WhatsApp Integration
3. Barcode/QR System
4. Fuel Management
5. Maintenance Management
6. Contract Management

**Total Estimasi:** 8-12 minggu (opsional)

---

## 💡 REKOMENDASI PRIORITAS UNTUK PERUSAHAAN

Berdasarkan analisis, saya merekomendasikan **fokus pada Phase 1** terlebih dahulu karena:

### **Top 5 Fitur yang Harus Segera Ditambahkan:**

1. **Dashboard & Analytics** - Untuk visibility & decision making
2. **Document Management** - Untuk compliance & audit trail
3. **PDF Generation** - Untuk professional document output
4. **Email Notifications** - Untuk customer communication
5. **Advanced Search** - Untuk operational efficiency

### **Mengapa Prioritas Ini?**

✅ **Dashboard** memberikan insight real-time untuk manajemen  
✅ **Document Management** memenuhi kebutuhan legal & audit  
✅ **PDF Generation** meningkatkan profesionalisme perusahaan  
✅ **Email Notifications** mengurangi manual work & improve customer service  
✅ **Advanced Search** meningkatkan produktivitas staff  

---

## 🎯 KESIMPULAN

**Sistem TMS Hybrid sudah sangat lengkap** dengan:
- ✅ Core operations (Job Order, Shipment, Transport)
- ✅ Complete finance module (Invoice, Vendor Bill, Payment)
- ✅ Full accounting integration
- ✅ Inventory management
- ✅ AI Assistant

**Yang masih kurang:**
- ❌ Dashboard & reporting yang visual
- ❌ Document management
- ❌ PDF generation
- ❌ Email automation
- ❌ Customer/Vendor portal

**Rekomendasi:**
Fokus pada **Phase 1 (Essential Features)** untuk melengkapi sistem menjadi **production-ready** dan **enterprise-grade**.

---

**Dibuat oleh:** AI Assistant  
**Tanggal:** 21 November 2025  
**Versi:** 1.0
