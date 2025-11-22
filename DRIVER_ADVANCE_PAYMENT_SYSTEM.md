# Driver Advance Payment Request System - Implementation Summary

**Date:** November 16, 2025  
**Status:** ✅ COMPLETED - Same as Vendor Bill System

---

## 🎯 Objective

Mengimplementasikan sistem tracking pengajuan pembayaran (payment requests) untuk Driver Advances yang **konsisten dan sama** dengan sistem Vendor Bills, untuk mencegah double pengajuan dan memberikan visibility yang jelas tentang status pembayaran.

---

## ✅ What Has Been Implemented

### 1. **Model Enhancements** (`DriverAdvance.php`)

#### Added Relationship:
```php
public function paymentRequests()
{
    return $this->hasMany(PaymentRequest::class, 'driver_advance_id');
}
```

#### Added Attributes:
```php
// Track total yang sudah diajukan via payment requests
public function getTotalRequestedAttribute(): float

// Track sisa yang masih bisa diajukan
public function getRemainingToRequestAttribute(): float
```

#### Added Scopes:
```php
// Driver advances yang masih punya sisa untuk diajukan
public function scopeOutstanding($query)

// Driver advances yang sudah diajukan penuh tapi belum settled
public function scopeFullyRequested($query)
```

### 2. **Controller Updates**

#### `PaymentRequestController.php`:
- ✅ Added eager loading: `driverAdvance.driver`
- ✅ Query outstanding driver advances
- ✅ Pass `$outstandingAdvances` to view

```php
$outstandingAdvances = \App\Models\Operations\DriverAdvance::with(['driver', 'shipmentLeg.jobOrder', 'paymentRequests'])
    ->outstanding()
    ->orderBy('advance_date', 'asc')
    ->get();
```

#### `DriverAdvanceController.php`:
- ✅ Added `paymentRequests` eager loading in index
- ✅ Added `paymentRequests` eager loading in show

### 3. **View Enhancements** (`payment-requests/index.blade.php`)

Added new section **"Driver Advances Not Fully Requested"** yang menampilkan:
- ✅ Driver advance number (dengan link ke detail)
- ✅ Driver name
- ✅ Job order (dengan link)
- ✅ Advance date
- ✅ Total amount
- ✅ Already requested amount (sudah diajukan)
- ✅ Not requested amount (belum diajukan / sisa)
- ✅ Progress percentage
- ✅ Status badge (Pending DP / DP Paid)
- ✅ Action button (Request DP / Request Settlement)

---

## 📊 System Comparison: Vendor Bills vs Driver Advances

| Feature | Vendor Bills | Driver Advances | Status |
|---------|-------------|-----------------|--------|
| **paymentRequests relationship** | ✅ | ✅ | **Same** |
| **total_requested attribute** | ✅ | ✅ | **Same** |
| **remaining_to_request attribute** | ✅ | ✅ | **Same** |
| **outstanding() scope** | ✅ | ✅ | **Same** |
| **fullyRequested() scope** | ✅ | ✅ | **Same** |
| **Display in payment-requests/index** | ✅ | ✅ | **Same** |
| **Prevent double requests** | ✅ | ✅ | **Same** |
| **Visual progress indicator** | ✅ | ✅ | **Same** |

✅ **RESULT: Both systems are now CONSISTENT and work the same way!**

---

## 🔍 How It Works

### Scenario 1: Driver Advance Belum Diajukan Sama Sekali

```
Driver Advance: DA-001
Total Amount: Rp 5,000,000
Status: Pending

Outstanding Check:
- total_requested = Rp 0
- remaining_to_request = Rp 5,000,000
- Show in "Driver Advances Not Fully Requested" ✅

Action Available:
- "Request DP" button visible
```

### Scenario 2: Driver Advance Sudah Diajukan Sebagian (DP)

```
Driver Advance: DA-001
Total Amount: Rp 5,000,000
DP Requested: Rp 3,500,000 (70%)
Status: DP Paid

Outstanding Check:
- total_requested = Rp 3,500,000
- remaining_to_request = Rp 1,500,000
- Show in "Driver Advances Not Fully Requested" ✅

Action Available:
- "Request Settlement" button visible (for remaining Rp 1,500,000)
```

### Scenario 3: Driver Advance Sudah Diajukan Penuh

```
Driver Advance: DA-001
Total Amount: Rp 5,000,000
Total Requested: Rp 5,000,000 (100%)
Status: Settled

Outstanding Check:
- total_requested = Rp 5,000,000
- remaining_to_request = Rp 0
- NOT shown in "Driver Advances Not Fully Requested" ❌
- System prevents creating new request (no remaining amount)
```

---

## 🛡️ Prevention of Double Requests

### Automatic Checks:

1. **Database Level** (Scope):
   ```sql
   WHERE (amount - (SELECT COALESCE(SUM(amount),0) 
                    FROM payment_requests 
                    WHERE driver_advance_id = driver_advances.id)) > 0
   ```

2. **Application Level** (Attribute):
   ```php
   $advance->remaining_to_request // Always shows accurate remaining
   ```

3. **UI Level** (Display):
   - Only shows advances with `remaining_to_request > 0`
   - Visual progress indicator shows percentage
   - Clear breakdown: Total / Already Requested / Not Requested

### Example Flow:

```
User sees: DA-001 - Rp 5,000,000
           Already Requested: Rp 3,500,000
           Not Requested: Rp 1,500,000 (70% requested)

User clicks "Request Settlement"
→ Form pre-filled with max Rp 1,500,000
→ If user tries to request more, validation will fail
→ After successful request, DA-001 will disappear from outstanding list
```

---

## 📈 Benefits

### 1. **Transparency**
- Clear visibility of what's been requested vs what's still pending
- Prevent confusion about payment status

### 2. **Control**
- Automatic tracking prevents duplicate requests
- Easy to see which driver advances need action

### 3. **Consistency**
- Same system for Vendor Bills and Driver Advances
- Uniform user experience across modules

### 4. **Efficiency**
- Quick identification of outstanding items
- Single page shows both vendor bills and driver advances that need payment requests

---

## 🔄 Workflow Integration

### Payment Request Creation Flow:

```
1. User visits /payment-requests
   ↓
2. Sees two sections:
   - Vendor Bills Not Fully Requested
   - Driver Advances Not Fully Requested ← NEW!
   ↓
3. For each outstanding driver advance, user can:
   - View current status
   - See how much has been requested
   - See how much remaining
   - Click action button to create request
   ↓
4. System prevents over-requesting automatically
   ↓
5. After request is paid, status updates accordingly
```

---

## 🧪 Testing Checklist

- [x] DriverAdvance model has paymentRequests relationship
- [x] total_requested attribute calculates correctly
- [x] remaining_to_request attribute calculates correctly
- [x] outstanding() scope filters correctly
- [x] Outstanding advances display in payment-requests/index
- [x] Progress percentage shows correctly
- [x] Action buttons show based on status (pending/dp_paid)
- [x] Links to driver-advances/show work
- [x] Links to job-orders/show work
- [x] No lazy loading errors (eager loading working)

---

## 📝 Database Schema Reference

### Existing Tables:
```sql
driver_advances
  - id
  - advance_number
  - amount (total uang jalan)
  - dp_amount (DP yang sudah dibayar)
  - status (pending/dp_paid/settled)
  - driver_id
  - shipment_leg_id
  
payment_requests
  - id
  - driver_advance_id (nullable, for driver advance payments)
  - vendor_bill_id (nullable, for vendor bill payments)
  - payment_type ('trucking' for driver advances)
  - amount
  - status (pending/approved/rejected/paid)
```

### Key Relationship:
```
DriverAdvance (1) ----< (many) PaymentRequest
    via driver_advance_id
```

---

## 🎓 Key Learnings

1. **Consistency is Critical**: When you have similar business processes (Vendor Bills & Driver Advances), they should work the same way

2. **Attributes vs Real Columns**: 
   - `total_requested` and `remaining_to_request` are calculated attributes, not database columns
   - This ensures they're always accurate and don't need manual updates

3. **Eager Loading Prevention**: 
   - LazyLoadingViolationException helped us identify missing eager loads
   - Fixed by adding `driverAdvance.driver` and `paymentRequests` to queries

4. **Scopes for Business Logic**: 
   - `outstanding()` scope encapsulates complex query logic
   - Reusable across controllers and easy to maintain

---

## 🚀 Future Enhancements (Optional)

1. **Bulk Actions**: Allow approving multiple payment requests at once
2. **Notifications**: Alert when driver advances need payment requests
3. **Reports**: Dashboard showing outstanding payment request items
4. **Approval Workflow**: Multi-level approval for large amounts
5. **Payment Scheduling**: Schedule future payments for driver advances

---

**Status:** ✅ FULLY IMPLEMENTED  
**Compatibility:** Same system as Vendor Bills  
**Prevention:** Double requests prevented  
**Visibility:** Clear tracking in payment-requests page

