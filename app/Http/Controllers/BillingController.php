<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    /**
     * Display a listing of the pending bills.
     */
    public function pendingBills()
    {
        $pendingBills = Customer::query()
            ->with('package')
            ->where('status', true)
            ->get()
            ->map(function ($customer) {
                $hasPaid = $customer->hasPaidForCurrentPeriod();
                return [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'email' => $customer->email,
                    'phone_number' => $customer->phone_number,
                    'package' => $customer->package->title,
                    'monthly_bill' => $customer->bill,
                    'billing_start_date' => $customer->billing_start_date ? $customer->billing_start_date->format('Y-m-d') : null,
                    'next_billing_date' => $customer->getNextBillingDate() ? $customer->getNextBillingDate()->format('Y-m-d') : null,
                    'days_remaining' => $customer->daysUntilNextBilling(),
                    'has_paid' => $hasPaid,
                ];
            })
            ->filter(function ($customer) {
                // Include both upcoming bills (due in next 7 days) and late/unpaid bills
                return !$customer['has_paid'] && $customer['days_remaining'] !== null && 
                       ($customer['days_remaining'] <= 7 || $customer['days_remaining'] < 0);
            })
            ->values();

        return Inertia::render('Billing/PendingBills', [
            'pendingBills' => $pendingBills,
        ]);
    }

    /**
     * Display all bills (both pending and paid).
     */
    public function allBills()
    {
        $customers = Customer::with('package')
            ->where('status', true)
            ->get()
            ->map(function ($customer) {
                $hasPaid = $customer->hasPaidForCurrentPeriod();
                return [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'email' => $customer->email,
                    'phone_number' => $customer->phone_number,
                    'package' => $customer->package->title,
                    'monthly_bill' => $customer->bill,
                    'billing_start_date' => $customer->billing_start_date ? $customer->billing_start_date->format('Y-m-d') : null,
                    'next_billing_date' => $customer->getNextBillingDate() ? $customer->getNextBillingDate()->format('Y-m-d') : null,
                    'days_remaining' => $customer->daysUntilNextBilling(),
                    'status' => $hasPaid ? 'Paid' : 'Pending',
                ];
            })
            ->values();

        return Inertia::render('Billing/AllBills', [
            'customers' => $customers,
        ]);
    }

    /**
     * Display the detailed customer billing history.
     */
    public function customerBillingHistory($customerId)
    {
        $customer = Customer::with(['package', 'transactions' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($customerId);

        $billingHistory = $customer->transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'payment_date' => $transaction->payment_date ? $transaction->payment_date->format('Y-m-d H:i') : null,
                'payment_method' => $transaction->payment_method,
                'reference_number' => $transaction->reference_number,
                'status' => $transaction->status,
                'billing_period' => $transaction->billing_period_start->format('Y-m-d') . ' to ' . $transaction->billing_period_end->format('Y-m-d'),
                'notes' => $transaction->notes,
                'created_at' => $transaction->created_at->format('Y-m-d H:i'),
            ];
        });

        return Inertia::render('Billing/CustomerHistory', [
            'customer' => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
                'address' => $customer->address,
                'package' => $customer->package->title,
                'package_fee' => $customer->package->fee,
                'monthly_bill' => $customer->bill,
                'billing_start_date' => $customer->billing_start_date ? $customer->billing_start_date->format('Y-m-d') : null,
                'status' => $customer->status ? 'Active' : 'Inactive',
            ],
            'billingHistory' => $billingHistory,
        ]);
    }

    /**
     * Show form to record a payment.
     */
    public function showPaymentForm($customerId)
    {
        $customer = Customer::with('package')->findOrFail($customerId);
        
        // Check if there's a pending transaction for the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $pendingTransaction = Transaction::where('customer_id', $customerId)
            ->where('status', 'pending')
            ->whereBetween('billing_period_start', [$startOfMonth, $endOfMonth])
            ->first();
            
        if (!$pendingTransaction) {
            // Create a new pending transaction if one doesn't exist
            $pendingTransaction = new Transaction([
                'customer_id' => $customer->id,
                'amount' => $customer->bill,
                'status' => 'pending',
                'billing_period_start' => $startOfMonth,
                'billing_period_end' => $endOfMonth,
            ]);
            $pendingTransaction->save();
        }

        return Inertia::render('Billing/RecordPayment', [
            'customer' => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'package' => $customer->package->title,
                'monthly_bill' => $customer->bill,
            ],
            'transaction' => [
                'id' => $pendingTransaction->id,
                'amount' => $pendingTransaction->amount,
                'billing_period_start' => $pendingTransaction->billing_period_start->format('Y-m-d'),
                'billing_period_end' => $pendingTransaction->billing_period_end->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Record a payment.
     */
    public function recordPayment(Request $request, $customerId)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);
        
        // Ensure the transaction belongs to the requested customer
        if ($transaction->customer_id != $customerId) {
            return back()->with('error', 'Transaction does not belong to this customer.');
        }

        $transaction->payment_method = $request->payment_method;
        $transaction->reference_number = $request->reference_number;
        $transaction->payment_date = $request->payment_date;
        $transaction->notes = $request->notes;
        $transaction->status = 'paid';
        $transaction->save();

        return redirect()->route('billing.customer-history', $customerId)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Generate monthly billing report.
     */
    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        $transactions = Transaction::with('customer')
            ->whereBetween('billing_period_start', [$startDate, $endDate])
            ->orWhereBetween('billing_period_end', [$startDate, $endDate])
            ->get();
        
        $paidTransactions = $transactions->where('status', 'paid');
        $pendingTransactions = $transactions->where('status', 'pending');
        
        $totalBilled = $transactions->sum('amount');
        $totalCollected = $paidTransactions->sum('amount');
        $totalPending = $pendingTransactions->sum('amount');
        
        $report = [
            'month' => $startDate->format('F Y'),
            'total_billed' => $totalBilled,
            'total_collected' => $totalCollected,
            'total_pending' => $totalPending,
            'collection_rate' => $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 2) : 0,
            'paid_transactions' => $paidTransactions->count(),
            'pending_transactions' => $pendingTransactions->count(),
        ];
        
        return Inertia::render('Billing/MonthlyReport', [
            'report' => $report,
            'paidTransactions' => $paidTransactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'customer_name' => $transaction->customer->full_name,
                    'amount' => $transaction->amount,
                    'payment_date' => $transaction->payment_date ? $transaction->payment_date->format('Y-m-d') : null,
                    'payment_method' => $transaction->payment_method,
                    'reference_number' => $transaction->reference_number,
                ];
            }),
            'pendingTransactions' => $pendingTransactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'customer_name' => $transaction->customer->full_name,
                    'amount' => $transaction->amount,
                    'billing_period' => $transaction->billing_period_start->format('Y-m-d') . ' to ' . $transaction->billing_period_end->format('Y-m-d'),
                ];
            }),
        ]);
    }

    /**
     * Generate billing for all active customers.
     */
    public function generateBills()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        
        // Get all active customers
        $customers = Customer::where('status', true)->get();
        $generatedCount = 0;
        
        foreach ($customers as $customer) {
            // Skip if a transaction already exists for this period
            $existingTransaction = Transaction::where('customer_id', $customer->id)
                ->whereBetween('billing_period_start', [$startOfMonth, $endOfMonth])
                ->first();
                
            if (!$existingTransaction) {
                Transaction::create([
                    'customer_id' => $customer->id,
                    'amount' => $customer->bill,
                    'status' => 'pending',
                    'billing_period_start' => $startOfMonth,
                    'billing_period_end' => $endOfMonth,
                ]);
                $generatedCount++;
            }
        }
        
        return redirect()->route('billing.pending-bills')
            ->with('success', "Generated {$generatedCount} new bills for the current month.");
    }
}
