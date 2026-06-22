<?php

namespace App\Http\Controllers\Notices;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\NoticeRead;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $role = Auth::user()->primaryRole();
        $roleTargetMap = [
            'Teacher' => 'teachers', 'Class Incharge' => 'teachers',
            'Student' => 'students', 'Parent' => 'parents',
        ];
        $myTarget = $roleTargetMap[$role] ?? null;

        $query = Notice::visible()->with('createdBy')->orderByDesc('priority')->orderByDesc('post_date');

        if (!Auth::user()->can('manage notices')) {
            $query->where(fn ($q) => $q->where('target', 'all')->when($myTarget, fn ($q2) => $q2->orWhere('target', $myTarget)));
        }

        if ($request->boolean('archived')) {
            $query = Notice::where('is_archived', true)->orderByDesc('created_at');
        }

        $notices = $query->paginate(15);

        return view('notices.index', compact('notices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'content' => 'required|string',
            'target' => 'required|in:all,teachers,students,parents,campus',
            'campus_scope' => 'required|in:boys,girls,both',
            'priority' => 'required|in:normal,important,urgent',
            'attachment' => 'nullable|file|max:5120',
            'post_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:today',
        ]);

        $data = $request->except('attachment');

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('notices', 'public');
        }

        $notice = Notice::create($data + ['created_by' => Auth::id()]);

        AuditLog::record('CREATE', 'Notices', "Notice posted: {$notice->title}");

        return response()->json(['success' => true, 'message' => 'Notice posted successfully.']);
    }

    public function markRead(Notice $notice)
    {
        NoticeRead::firstOrCreate(
            ['notice_id' => $notice->id, 'user_id' => Auth::id()],
            ['read_at' => now()]
        );
        return response()->json(['success' => true]);
    }

    public function archive(Notice $notice)
    {
        $notice->update(['is_archived' => true]);
        return response()->json(['success' => true, 'message' => 'Notice archived.']);
    }

    public function destroy(Notice $notice)
    {
        if ($notice->attachment) Storage::disk('public')->delete($notice->attachment);
        $title = $notice->title;
        $notice->delete();

        AuditLog::record('DELETE', 'Notices', "Notice deleted: {$title}");

        return response()->json(['success' => true, 'message' => 'Notice deleted successfully.']);
    }
}
