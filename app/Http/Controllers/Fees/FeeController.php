<?php

namespace App\Http\Controllers\Fees;

use App\Http\Controllers\Controller;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Fee;
use App\Models\Student;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    // ─── FEE STRUCTURE SETUP ─────────────────────────────────────
    public function structureIndex()
    {
        $categories = FeeCategory::with('structures.program')->get();
        $programs = \App\Models\Program::all();
        return view('fees.structure', compact('categories', 'programs'));
    }

    public function storeStructure(Request $request)
    {
        $request->validate([
            'fee_category_id' => 'required|exists:fee_categories,id',
            'program_id' => 'nullable|exists:programs,id',
            'campus' => 'required|in:boys,girls,both',
            'year' => 'required|in:first,second,both',
            'amount' => 'required|numeric|min:0',
            'installment_plan' => 'required|in:full,2,3,4,custom',
        ]);

        $structure = FeeStructure::create($request->all());
        AuditLog::record('CREATE', 'Fees', "Fee structure created: Rs.{$structure->amount} for category #{$structure->fee_category_id}");

        return response()->json(['success' => true, 'message' => 'Fee structure saved successfully.']);
    }

    public function destroyStructure(FeeStructure $structure)
    {
        $structure->delete();
        return response()->json(['success' => true, 'message' => 'Fee structure removed.']);
    }

    // ─── STUDENT FEE LEDGER ──────────────────────────────────────
    public function ledger(Student $student)
    {
        $fees = $student->fees()->with('category')->orderByDesc('payment_date')->get();
        $categories = FeeCategory::all();

        $totalDue = $fees->sum('amount_due');
        $totalPaid = $fees->sum('amount_paid');
        $totalWaived = $fees->sum('waiver_amount');
        $balance = $totalDue - $totalPaid - $totalWaived;

        return view('fees.ledger', compact('student', 'fees', 'categories', 'totalDue', 'totalPaid', 'totalWaived', 'balance'));
    }

    public function storePayment(Request $request, Student $student)
    {
        $request->validate([
            'fee_category_id' => 'required|exists:fee_categories,id',
            'payment_date' => 'required|date',
            'amount_due' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payment_mode' => 'required|in:cash,bank,jazzcash,easypaisa',
            'remarks' => 'nullable|string',
        ]);

        $fee = Fee::create($request->all() + [
            'student_id' => $student->id,
            'receipt_number' => Fee::generateReceiptNumber(),
            'created_by' => auth()->id(),
        ]);

        AuditLog::record('CREATE', 'Fees', "Payment recorded for {$student->name}: Rs.{$fee->amount_paid} ({$fee->receipt_number})");

        return response()->json([
            'success' => true,
            'message' => "Payment recorded successfully. Receipt: {$fee->receipt_number}",
        ]);
    }

    public function applyWaiver(Request $request, Fee $fee)
    {
        $request->validate([
            'waiver_amount' => 'required|numeric|min:0|max:'.$fee->amount_due,
            'waiver_reason' => 'required|string|max:255',
        ]);

        $fee->update($request->only('waiver_amount', 'waiver_reason'));

        AuditLog::record('UPDATE', 'Fees', "Waiver of Rs.{$fee->waiver_amount} applied to {$fee->student->name}'s fee ({$fee->receipt_number})");

        return response()->json(['success' => true, 'message' => 'Discount/Waiver applied successfully.']);
    }

    public function destroyPayment(Fee $fee)
    {
        $fee->delete();
        return response()->json(['success' => true, 'message' => 'Fee record deleted.']);
    }

    // ─── FEE REPORTS ─────────────────────────────────────────────
    public function reports(Request $request)
    {
        $collected = Fee::sum('amount_paid');
        $pending = Fee::selectRaw('SUM(amount_due - amount_paid - waiver_amount) as total')->value('total') ?? 0;
        $overdue = Fee::whereDate('payment_date', '<', now())
            ->selectRaw('SUM(amount_due - amount_paid - waiver_amount) as total')
            ->havingRaw('SUM(amount_due - amount_paid - waiver_amount) > 0')
            ->value('total') ?? 0;
        $discounts = Fee::sum('waiver_amount');

        $sectionSummary = DB::table('fees')
            ->join('students', 'fees.student_id', '=', 'students.id')
            ->join('sections', 'students.section_id', '=', 'sections.id')
            ->select('sections.code', DB::raw('SUM(fees.amount_paid) as collected'), DB::raw('SUM(fees.amount_due - fees.amount_paid - fees.waiver_amount) as pending'))
            ->groupBy('sections.code')
            ->orderBy('sections.code')
            ->get();

        return view('fees.reports', compact('collected', 'pending', 'overdue', 'discounts', 'sectionSummary'));
    }
}
