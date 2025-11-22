# Invoice Module - Complete Implementation Guide

## 📋 Overview
Modul Invoice untuk TMS Hybrid dengan workflow berbasis customer dan job order selection. Sistem memungkinkan:
- Multi-select job orders untuk satu invoice
- Auto-preview items dari job order terpilih
- Auto-include billable additional costs (detention, storage, handling)
- Track invoice status di job orders
- Payment receipt allocation

## 🎯 Workflow Create Invoice

### Step 1: Pilih Customer
- Sidebar kiri menampilkan daftar customer
- **Hanya customer dengan Job Order completed & belum full invoice yang muncul**
- Query menggunakan `whereHas` untuk filter customer eligible

### Step 2: Pilih Job Order (Multi-Select)
- Setelah customer dipilih, tampil daftar job orders milik customer tsb
- **Hanya JO dengan status "completed" dan belum full invoice**
- User bisa centang beberapa job order sekaligus (multi-select)
- Setiap JO menampilkan: job_number, origin → destination, dan total amount

### Step 3: Preview Items
- Panel kanan menampilkan preview items dari semua JO terpilih
- Items include:
  - **Main shipping item** (invoice_amount dari JO)
  - **Billable additional costs** (detention, storage, handling) yang belum diinvoice
- Badge "NO PPN" untuk items dengan exclude_tax = true
- Subtotal otomatis terhitung

### Step 4: Input Detail Invoice
- Tanggal invoice (default: today)
- Due date (default: +30 days)
- Catatan (optional)

### Step 5: Edit Items (Optional)
- User bisa edit description, quantity, unit_price
- Hidden fields: job_order_id, shipment_leg_id, item_type, exclude_tax

### Step 6: Tax & Discount
- Input tax amount (PPN 11%) - manual atau 0
- Input discount amount (optional)
- Total invoice otomatis terhitung

### Step 7: Submit
- Invoice tersimpan dengan status "draft"
- InvoiceItems terbuat dengan relasi ke job_order_id
- Job order status otomatis update (isInvoiced, isFullyInvoiced)

## 📊 Database Schema

### invoices
```sql
- id (PK)
- invoice_number (auto-generated: INV-YYYY-NNNN)
- customer_id (FK → customers)
- invoice_date
- due_date
- subtotal
- tax_amount
- discount_amount
- total_amount
- paid_amount
- status (enum: draft, sent, paid, partial, overdue, cancelled)
- notes
- sent_at
- paid_at
- timestamps
```

### invoice_items
```sql
- id (PK)
- invoice_id (FK → invoices)
- job_order_id (FK → job_orders, nullable)
- shipment_leg_id (FK → shipment_legs, nullable)
- transport_id (FK → transports, nullable)
- description
- quantity
- unit_price
- amount (auto-calculated: quantity × unit_price)
- item_type (enum: shipping, detention, storage, handling, other)
- timestamps
```

### payment_receipts
```sql
- id (PK)
- receipt_number (auto-generated: RCP-YYYY-NNNN)
- customer_id (FK → customers)
- payment_date
- amount
- allocated_amount
- payment_method (enum: cash, transfer, check, giro)
- bank_account_id (FK → bank_accounts, nullable)
- reference_number
- notes
- received_by (FK → users)
- timestamps
```

### invoice_payments (pivot)
```sql
- id (PK)
- invoice_id (FK → invoices)
- payment_receipt_id (FK → payment_receipts)
- amount
- timestamps
```

## 🔧 Controller Logic

### InvoiceController@create
```php
// Query customers with eligible job orders
$customers = Customer::whereHas('jobOrders', function($q) {
    $q->where('status', 'completed')
      ->where(function($sq) {
          $sq->whereDoesntHave('invoiceItems')
             ->orWhereRaw('(total_invoiced) < (invoice_amount + total_billable)');
      });
})->orderBy('name')->get();

// If customer selected, get their eligible job orders
if ($request->filled('customer_id')) {
    $jobOrders = JobOrder::with(['shipmentLegs.additionalCosts'])
        ->where('customer_id', $request->customer_id)
        ->where('status', 'completed')
        ->where(function($q) {
            $q->whereDoesntHave('invoiceItems')
              ->orWhereRaw('(total_invoiced) < (invoice_amount + total_billable)');
        })
        ->get();
}

// If job orders selected, build preview items
if ($request->filled('job_order_ids')) {
    foreach ($selectedJobOrders as $jobOrder) {
        // Add main shipping item
        $previewItems[] = [
            'job_order_id' => $jobOrder->id,
            'description' => 'Shipping - ' . $jobOrder->job_number,
            'quantity' => 1,
            'unit_price' => $jobOrder->invoice_amount,
            'item_type' => 'shipping',
            'exclude_tax' => false,
        ];
        
        // Add billable additional costs
        foreach ($jobOrder->shipmentLegs as $leg) {
            foreach ($leg->additionalCosts->where('is_billable', true) as $cost) {
                if (!$alreadyInvoiced && $cost->amount > 0) {
                    $previewItems[] = [
                        'job_order_id' => $jobOrder->id,
                        'shipment_leg_id' => $leg->id,
                        'description' => ucfirst($cost->cost_type) . ' - ' . $cost->description,
                        'quantity' => 1,
                        'unit_price' => $cost->amount,
                        'item_type' => $cost->cost_type,
                        'exclude_tax' => true, // No PPN for detention/storage/handling
                    ];
                }
            }
        }
    }
}
```

### InvoiceController@store
```php
DB::beginTransaction();
try {
    // Create invoice
    $invoice = Invoice::create([
        'customer_id' => $validated['customer_id'],
        'invoice_date' => $validated['invoice_date'],
        'due_date' => $validated['due_date'],
        'tax_amount' => $validated['tax_amount'] ?? 0,
        'discount_amount' => $validated['discount_amount'] ?? 0,
        'notes' => $validated['notes'] ?? null,
        'status' => 'draft',
        'subtotal' => 0,
        'total_amount' => 0,
    ]);
    
    // Create items
    foreach ($validated['items'] as $itemData) {
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'job_order_id' => $itemData['job_order_id'] ?? null,
            'shipment_leg_id' => $itemData['shipment_leg_id'] ?? null,
            'description' => $itemData['description'],
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'item_type' => $itemData['item_type'] ?? 'other',
        ]);
    }
    
    // Auto-calculate totals via model observers
    $invoice->recalculateTotals();
    
    DB::commit();
    return redirect()->route('invoices.show', $invoice);
} catch (\Exception $e) {
    DB::rollback();
    return back()->withErrors(['error' => $e->getMessage()]);
}
```

## 📈 Model Relationships & Helper Methods

### Invoice Model
```php
// Relationships
public function customer() { return $this->belongsTo(Customer::class); }
public function items() { return $this->hasMany(InvoiceItem::class); }
public function payments() { return $this->belongsToMany(PaymentReceipt::class, 'invoice_payments')->withPivot('amount'); }

// Helper Methods
public function generateNumber() { return 'INV-' . date('Y') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT); }
public function recalculateTotals() { /* sum items, apply tax/discount */ }
public function updatePaidAmount() { /* sum allocated payments */ }
public function isOverdue() { return $this->status !== 'paid' && $this->due_date < now(); }
```

### JobOrder Model (Updated)
```php
// Invoice Relationships
public function invoiceItems() { return $this->hasMany(InvoiceItem::class); }
public function invoices() { return $this->hasManyThrough(Invoice::class, InvoiceItem::class); }

// Invoice Status Helpers
public function isInvoiced() { return $this->invoiceItems()->exists(); }
public function isFullyInvoiced() { return $this->total_invoiced >= ($this->invoice_amount + $this->total_billable); }
public function getInvoiceStatusAttribute() {
    if (!$this->isInvoiced()) return 'not_invoiced';
    if (!$this->isFullyInvoiced()) return 'partially_invoiced';
    return 'fully_invoiced';
}
public function getTotalInvoicedAttribute() { return $this->invoiceItems()->sum('amount'); }
public function getUninvoicedAmountAttribute() { return ($this->invoice_amount + $this->total_billable) - $this->total_invoiced; }
```

## 🎨 UI Components

### Sidebar (Customer & Job Order Selection)
- Card "1️⃣ Pilih Customer" dengan dropdown
- Card "2️⃣ Pilih Job Order" dengan multi-checkbox
- Button "Preview Items" untuk submit selection

### Main Panel (Preview & Form)
- Card "Preview Items" dengan list items + subtotal
- Card "3️⃣ Detail Invoice" dengan date inputs + notes
- Card "4️⃣ Invoice Items" dengan editable items
- Card "5️⃣ Tax & Discount" dengan tax/discount inputs + total
- Card dengan button "Simpan Invoice"

### Job Orders Table (Index & Show)
- Column "Invoice" dengan 3 status badges:
  - 🔴 Belum Invoice (not_invoiced)
  - 🟠 Sebagian (partially_invoiced) - tampil jumlah invoiced & sisa
  - 🟢 Sudah Invoice (fully_invoiced)
- Link "Buat Invoice" untuk uninvoiced orders
- Filter dropdown "Status Invoice" dengan opsi "Belum Diinvoice" dan "Sudah Diinvoice"

## 🧪 Testing Steps

1. **Setup Test Data**
   - Create customer: PT Test Customer
   - Create job order dengan status "completed"
   - Add shipment leg dengan additional costs (is_billable = true)

2. **Test Create Invoice**
   - Open `/invoices/create`
   - Select customer "PT Test Customer" → form submit otomatis
   - Centang 1-2 job orders → klik "Preview Items"
   - Verify: preview items muncul (shipping + additional costs)
   - Verify: items dengan detention/storage ada badge "NO PPN"
   - Edit tanggal invoice & due date
   - Input tax amount (atau 0)
   - Klik "Simpan Invoice"
   - Verify: redirect ke invoice show page
   - Verify: invoice_number auto-generated (INV-2025-0001)

3. **Test Job Order Status Update**
   - Open job order yang baru diinvoice
   - Verify: section "Status Invoice" tampil badge "Sudah Diinvoice Penuh" (jika full)
   - Verify: section "Invoice History" tampil list invoices
   - Open `/job-orders` → filter "Belum Diinvoice"
   - Verify: job order yang sudah diinvoice TIDAK muncul

4. **Test Multi Job Order Invoice**
   - Pilih 2-3 job orders sekaligus
   - Verify: semua items dari semua JO muncul di preview
   - Create invoice
   - Verify: semua invoice_items punya job_order_id yang benar

5. **Test Edge Cases**
   - Job order belum "completed" → tidak muncul di list
   - Job order sudah fully invoiced → tidak muncul di list customer
   - Customer tanpa job order eligible → tidak muncul di dropdown

## 📝 Routes

```php
Route::resource('invoices', InvoiceController::class);
Route::patch('invoices/{invoice}/mark-as-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.mark-as-sent');
Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

Route::resource('payment-receipts', PaymentReceiptController::class)->except(['edit', 'update']);
Route::post('payment-receipts/{payment_receipt}/allocate', [PaymentReceiptController::class, 'allocate'])->name('payment-receipts.allocate');
Route::delete('payment-receipts/{payment_receipt}/deallocate', [PaymentReceiptController::class, 'deallocate'])->name('payment-receipts.deallocate');
```

## 🚀 Next Steps (Optional Enhancements)

1. **PDF Generation**
   - Install: `composer require barryvdh/laravel-dompdf`
   - Create PDF template untuk invoice
   - Implement InvoiceController@pdf method

2. **Email Notifications**
   - Send email saat invoice di-mark as sent
   - Attach PDF invoice
   - Email overdue reminders

3. **Aging Reports**
   - Report invoice berdasarkan umur (0-30, 31-60, 61-90, >90 days)
   - Export to Excel

4. **Payment Receipt Views**
   - Create payment-receipts/create.blade.php
   - Create payment-receipts/show.blade.php dengan allocation form
   - Implement allocate/deallocate methods

## 📚 References

- Laravel Documentation: https://laravel.com/docs
- TailwindCSS: https://tailwindcss.com
- Alpine.js (if needed for interactivity): https://alpinejs.dev

---
**Last Updated:** November 16, 2025
**Author:** AI Assistant
**Version:** 1.0.0
