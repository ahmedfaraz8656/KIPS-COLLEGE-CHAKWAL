<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\NotificationRecipient;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // ─── HISTORY PAGE ────────────────────────────────────────────
    public function index()
    {
        $notifications = AppNotification::with('createdBy')
            ->withCount('recipients')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    // ─── CREATE & SEND ───────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'message' => 'required|string',
            'type' => 'required|in:result_published,low_attendance,fee_overdue,notice,exam_scheduled,general',
            'target_type' => 'required|in:all,role,campus,section,student',
            'target_value' => 'nullable|string',
            'channel' => 'required|in:in_app,whatsapp,both',
            'scheduled_at' => 'nullable|date',
        ]);

        $notification = AppNotification::create($request->all() + [
            'created_by' => Auth::id(),
            'sent_at' => $request->scheduled_at ? null : now(),
        ]);

        if (!$request->scheduled_at) {
            $this->dispatchToRecipients($notification);
        }

        AuditLog::record('CREATE', 'Notifications', "Notification sent: {$notification->title}");

        return response()->json([
            'success' => true,
            'message' => $request->scheduled_at
                ? "Notification scheduled for {$request->scheduled_at}."
                : 'Notification sent successfully.',
        ]);
    }

    protected function dispatchToRecipients(AppNotification $notification): void
    {
        $users = $notification->resolveRecipients();

        foreach ($users as $user) {
            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'whatsapp_status' => in_array($notification->channel, ['whatsapp', 'both']) ? 'pending' : 'not_applicable',
            ]);
            // Actual WhatsApp dispatch happens via a queued Job using Settings::get('whatsapp_api_token')
        }
    }

    // ─── BELL DROPDOWN (AJAX) ────────────────────────────────────
    public function bellList()
    {
        $recipients = NotificationRecipient::where('user_id', Auth::id())
            ->with('notification')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $unreadCount = NotificationRecipient::where('user_id', Auth::id())->whereNull('read_at')->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'data' => $recipients->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->notification->title,
                'message' => $r->notification->message,
                'is_read' => (bool) $r->read_at,
                'time_ago' => $r->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function markRead(NotificationRecipient $recipient)
    {
        abort_if($recipient->user_id !== Auth::id(), 403);
        $recipient->markRead();
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        NotificationRecipient::where('user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
    }
}
