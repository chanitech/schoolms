<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\SmsLog;
use App\Models\Staff;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function __construct(private SmsService $sms)
    {
        $this->middleware('permission:send sms');
    }

    public function compose()
    {
        $classes = SchoolClass::orderBy('name')->get(['id', 'name']);

        return view('sms.compose', compact('classes'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'audience'  => 'required|in:all_guardians,class_guardians,all_staff,custom',
            'class_id'  => 'required_if:audience,class_guardians|nullable|exists:school_classes,id',
            'custom_numbers' => 'required_if:audience,custom|nullable|string',
            'message'   => 'required|string|max:918', // 6 SMS segments (153 chars x 6)
        ]);

        $recipients = match ($data['audience']) {
            'all_guardians' => Guardian::whereNotNull('phone')->get(['first_name', 'last_name', 'phone'])
                ->map(fn($g) => ['name' => trim("{$g->first_name} {$g->last_name}"), 'phone' => $g->phone]),

            'class_guardians' => Guardian::whereHas('students', fn($q) => $q->where('class_id', $data['class_id']))
                ->whereNotNull('phone')->get(['first_name', 'last_name', 'phone'])
                ->map(fn($g) => ['name' => trim("{$g->first_name} {$g->last_name}"), 'phone' => $g->phone]),

            'all_staff' => Staff::whereNotNull('phone')->get(['first_name', 'last_name', 'phone'])
                ->map(fn($s) => ['name' => trim("{$s->first_name} {$s->last_name}"), 'phone' => $s->phone]),

            'custom' => collect(preg_split('/[\s,;\n]+/', $data['custom_numbers'], -1, PREG_SPLIT_NO_EMPTY))
                ->map(fn($p) => ['name' => null, 'phone' => $p]),
        };

        if ($recipients->isEmpty()) {
            return back()->withInput()->with('error', 'No recipients found for that audience.');
        }

        $result = $this->sms->sendBulk($recipients->all(), $data['message'], 'announcement');

        if ($result['error'] && $result['sent'] === 0) {
            return back()->withInput()->with('error', 'Sending failed: ' . $result['error']);
        }

        $summary = "{$result['sent']} sent";
        if ($result['invalid']) $summary .= ", {$result['invalid']} skipped (invalid number)";
        if ($result['failed']) $summary .= ", {$result['failed']} failed";

        return redirect()->route('sms.index')->with('success', "Message sent — {$summary}.");
    }

    public function index(Request $request)
    {
        $query = SmsLog::with('sender')->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);

        $logs = $query->paginate(30)->withQueryString();

        return view('sms.index', compact('logs'));
    }
}
