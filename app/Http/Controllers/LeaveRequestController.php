<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeaveRequestController extends Controller
{
    /**
     * Display the leave request form.
     */
    public function create(Request $request)
    {
        $leaveTypes = LeaveType::all();
        // Get employees for replacement field - enhanced version
        $employees = User::select('id', 'name', 'department', 'email')
            ->where('status', 'active')
            ->where('id', '!=', auth()->id())
            ->whereNotNull('name')
            ->orderBy('department', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Get current user's leave data
        $user = Auth::user();
        $currentYear = date('Y');

        $usedLeave = $user->leaveRequests()
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        $annualLeaveAllowance = $user->annual_leave_quota ?? 12;
        if ($annualLeaveAllowance < 12) {
            $annualLeaveAllowance = 12;
        }

        $remainingLeave = max(0, $annualLeaveAllowance - $usedLeave);

        // Check if edit mode
        $editRequest = null;
        if ($request->has('edit')) {
            $editRequest = LeaveRequest::where('id', $request->get('edit'))
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->with(['leaveType', 'replacementEmployee'])
                ->first();
        }

        return view('leave.show', compact('leaveTypes', 'employees', 'user', 'usedLeave', 'annualLeaveAllowance', 'remainingLeave', 'editRequest'));
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10',
            'emergency_contact' => 'nullable|string|max:255',
            'replacement_employee_id' => 'nullable|exists:users,id|different:user_id',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Create leave request
        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'emergency_contact' => $request->emergency_contact,
            'replacement_employee_id' => $request->replacement_employee_id,
            'status' => 'pending',
        ]);

        // Handle file uploads if any
        if ($request->hasFile('documents')) {
            $leaveRequest->update([
                'documents' => $this->storeLeaveDocuments($request->file('documents')),
            ]);
        }

        return redirect()->route('leave.status')->with('success', 'Permohonan cuti berhasil diajukan!');
    }

    /**
     * Update an existing leave request.
     */
    public function update(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'emergency_contact' => 'nullable|string|max:255',
            'replacement_employee_id' => 'nullable|exists:users,id',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $leaveRequest->update([
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'emergency_contact' => $request->emergency_contact,
            'replacement_employee_id' => $request->replacement_employee_id,
        ]);

        // Handle file uploads if any
        if ($request->hasFile('documents')) {
            $leaveRequest->update([
                'documents' => $this->storeLeaveDocuments($request->file('documents')),
            ]);
        }

        return redirect()->route('leave.status')->with('success', 'Permohonan cuti berhasil diperbarui!');
    }

    /**
     * Display a listing of leave requests.
     */
    public function index()
    {
        $leaveRequests = Auth::user()->leaveRequests()
            ->with(['leaveType', 'replacementEmployee', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('leave.index', compact('leaveRequests'));
    }

    /**
     * Display status page with user's leave requests and statistics.
     */
    public function status()
    {
        $user = Auth::user();
        $currentYear = date('Y');

        $leaveRequests = $user->leaveRequests()
            ->with(['leaveType', 'replacementEmployee', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalRequests = $user->leaveRequests()->count();
        $approvedRequests = $user->leaveRequests()->where('status', 'approved')->count();
        $pendingRequests = $user->leaveRequests()->where('status', 'pending')->count();

        $usedLeave = $user->leaveRequests()
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        $annualLeaveAllowance = $user->annual_leave_quota ?? 12;
        if ($annualLeaveAllowance < 12) {
            $annualLeaveAllowance = 12;
        }

        $remainingLeave = max(0, $annualLeaveAllowance - $usedLeave);

        return view('leave.status', compact(
            'leaveRequests',
            'totalRequests',
            'approvedRequests',
            'pendingRequests',
            'usedLeave',
            'annualLeaveAllowance',
            'remainingLeave'
        ));
    }

    /**
     * Cancel (delete) a leave request.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $leaveRequest = LeaveRequest::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();

            $leaveRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permohonan cuti berhasil dibatalkan dan dihapus.',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan permohonan cuti. '.$e->getMessage(),
            ], 400);
        }
    }

    public function downloadDocument(string $path)
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $decodedPath = ltrim(urldecode($path), '/');
        if ($decodedPath === '' || str_contains($decodedPath, '..')) {
            abort(400, 'Path tidak valid.');
        }

        $leaveRequests = LeaveRequest::query()
            ->when(! $user->canViewOthersLeave(), fn ($q) => $q->where('user_id', $user->id))
            ->get(['id', 'user_id', 'documents']);

        $matched = null;
        foreach ($leaveRequests as $leaveRequest) {
            $docs = is_array($leaveRequest->documents) ? $leaveRequest->documents : [];
            foreach ($docs as $doc) {
                $normalized = ltrim((string) $doc, '/');
                if ($normalized === $decodedPath || basename($normalized) === basename($decodedPath)) {
                    $matched = $leaveRequest;
                    $decodedPath = $normalized;
                    break 2;
                }
            }
        }

        if (! $matched) {
            abort(403);
        }

        Gate::authorize('view', $matched);

        foreach (['private', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($decodedPath)) {
                return Storage::disk($disk)->response($decodedPath);
            }
        }

        abort(404, 'File tidak ditemukan.');
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     * @return list<string>
     */
    private function storeLeaveDocuments(array $files): array
    {
        $uploadedFiles = [];
        foreach ($files as $file) {
            $extension = strtolower((string) ($file->guessExtension() ?: $file->extension() ?: 'bin'));
            if (! in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                continue;
            }
            $uploadedFiles[] = $file->storeAs(
                'leave-documents',
                Str::uuid()->toString().'.'.$extension,
                'private'
            );
        }

        return $uploadedFiles;
    }
}
