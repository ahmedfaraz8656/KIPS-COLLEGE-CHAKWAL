<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'college_name'               => Setting::get('college_name', 'KIPS College Chakwal'),
            'college_address'            => Setting::get('college_address', 'Chakwal, Punjab, Pakistan'),
            'college_phone'              => Setting::get('college_phone', ''),
            'college_email'              => Setting::get('college_email', ''),
            'college_logo'               => Setting::get('college_logo', ''),
            'attendance_late_time'       => Setting::get('attendance_late_time', '08:30'),
            'attendance_min_percent'     => Setting::get('attendance_min_percent', '75'),
            'attendance_warning_percent' => Setting::get('attendance_warning_percent', '80'),
            'session_timeout_minutes'    => Setting::get('session_timeout_minutes', '30'),
            'login_max_attempts'         => Setting::get('login_max_attempts', '5'),
            'login_lockout_minutes'      => Setting::get('login_lockout_minutes', '15'),
            'two_factor_enabled'         => Setting::get('two_factor_enabled', '0'),
            'whatsapp_api_token'         => Setting::get('whatsapp_api_token', ''),
            'theme_primary_color'        => Setting::get('theme_primary_color', '#1E3A5F'),
            'theme_mode'                 => Setting::get('theme_mode', 'light'),
            'font_size'                  => Setting::get('font_size', 'normal'),
        ];

        $onlineUsers = User::where('last_login_at', '>=', now()->subMinutes(15))->where('status', true)->get();

        return view('settings.index', compact('settings', 'onlineUsers'));
    }

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'college_name' => 'required|string|max:150',
            'college_address' => 'nullable|string',
            'college_phone' => 'nullable|string|max:20',
            'college_email' => 'nullable|email',
            'logo' => 'nullable|image|max:1024',
        ]);

        Setting::set('college_name', $request->college_name);
        Setting::set('college_address', $request->college_address);
        Setting::set('college_phone', $request->college_phone);
        Setting::set('college_email', $request->college_email);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            Setting::set('college_logo', $path);
        }

        AuditLog::record('UPDATE', 'Settings', 'General college settings updated');

        return response()->json(['success' => true, 'message' => 'General settings saved successfully.']);
    }

    public function updateAttendance(Request $request)
    {
        $request->validate([
            'attendance_late_time' => 'required|date_format:H:i',
            'attendance_min_percent' => 'required|integer|min:0|max:100',
            'attendance_warning_percent' => 'required|integer|min:0|max:100',
        ]);

        Setting::set('attendance_late_time', $request->attendance_late_time);
        Setting::set('attendance_min_percent', $request->attendance_min_percent);
        Setting::set('attendance_warning_percent', $request->attendance_warning_percent);

        AuditLog::record('UPDATE', 'Settings', "Late arrival cutoff changed to {$request->attendance_late_time}");

        return response()->json([
            'success' => true,
            'message' => 'Attendance settings updated. This applies immediately to new attendance markings (existing records unaffected).',
        ]);
    }

    public function updateSecurity(Request $request)
    {
        $request->validate([
            'session_timeout_minutes' => 'required|integer|min:5|max:240',
            'login_max_attempts' => 'required|integer|min:3|max:10',
            'login_lockout_minutes' => 'required|integer|min:5|max:60',
            'two_factor_enabled' => 'nullable|boolean',
        ]);

        Setting::set('session_timeout_minutes', $request->session_timeout_minutes);
        Setting::set('login_max_attempts', $request->login_max_attempts);
        Setting::set('login_lockout_minutes', $request->login_lockout_minutes);
        Setting::set('two_factor_enabled', $request->boolean('two_factor_enabled') ? '1' : '0');

        AuditLog::record('UPDATE', 'Settings', 'Security settings updated');

        return response()->json(['success' => true, 'message' => 'Security settings updated successfully.']);
    }

    public function updateNotificationSettings(Request $request)
    {
        $request->validate(['whatsapp_api_token' => 'nullable|string']);
        Setting::set('whatsapp_api_token', $request->whatsapp_api_token);

        AuditLog::record('UPDATE', 'Settings', 'Notification/WhatsApp settings updated');

        return response()->json(['success' => true, 'message' => 'Notification settings saved.']);
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme_primary_color' => 'required|string|max:7',
            'theme_mode' => 'required|in:light,dark,high-contrast',
            'font_size' => 'required|in:normal,large,extra-large',
        ]);

        Setting::set('theme_primary_color', $request->theme_primary_color);
        Setting::set('theme_mode', $request->theme_mode);
        Setting::set('font_size', $request->font_size);

        return response()->json(['success' => true, 'message' => 'Theme preferences saved.']);
    }

    // ─── ONLINE USERS / FORCE LOGOUT ─────────────────────────────
    public function forceLogout(User $user)
    {
        // Invalidate all of this user's sessions
        \DB::table('sessions')->where('user_id', $user->id)->delete();

        AuditLog::record('UPDATE', 'Settings', "Force logged out user: {$user->name}");

        return response()->json(['success' => true, 'message' => "{$user->name} has been logged out of all devices."]);
    }
}
