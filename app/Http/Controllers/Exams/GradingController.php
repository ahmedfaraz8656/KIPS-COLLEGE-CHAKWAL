<?php

namespace App\Http\Controllers\Exams;

use App\Http\Controllers\Controller;
use App\Models\GradingTemplate;
use App\Models\GradingRule;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradingController extends Controller
{
    public function index()
    {
        $templates = GradingTemplate::with('rules')->get();
        return view('exams.grading', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'min_pass_percent' => 'required|integer|min:0|max:100',
            'rules' => 'required|array|min:1',
            'rules.*.from_percent' => 'required|numeric|min:0|max:100',
            'rules.*.to_percent'   => 'required|numeric|min:0|max:100',
            'rules.*.grade'        => 'required|string|max:5',
            'rules.*.remarks'      => 'required|string|max:50',
        ]);

        // Validate ranges don't overlap and cover 0-100
        $sorted = collect($request->rules)->sortBy('from_percent')->values();
        for ($i = 0; $i < $sorted->count() - 1; $i++) {
            if ($sorted[$i]['to_percent'] >= $sorted[$i + 1]['from_percent']) {
                return response()->json(['success' => false, 'message' => "Overlap detected between {$sorted[$i]['grade']} and {$sorted[$i+1]['grade']} ranges."], 422);
            }
        }
        if ($sorted->first()['from_percent'] != 0 || $sorted->last()['to_percent'] < 99.99) {
            return response()->json(['success' => false, 'message' => 'Grading ranges must fully cover 0% to 100% with no gaps.'], 422);
        }

        $template = null;

        DB::transaction(function () use ($request, &$template) {
            $template = GradingTemplate::create([
                'name' => $request->name,
                'min_pass_percent' => $request->min_pass_percent,
                'is_default' => $request->boolean('is_default'),
            ]);

            if ($request->boolean('is_default')) {
                GradingTemplate::where('id', '!=', $template->id)->update(['is_default' => false]);
            }

            foreach ($request->rules as $rule) {
                GradingRule::create($rule + ['grading_template_id' => $template->id]);
            }

            AuditLog::record('CREATE', 'Grading', "Grading template created: {$template->name}");
        });

        return response()->json(['success' => true, 'message' => "{$template->name} created successfully."]);
    }

    public function update(Request $request, GradingTemplate $template)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'min_pass_percent' => 'required|integer|min:0|max:100',
            'rules' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request, $template) {
            $template->update(['name' => $request->name, 'min_pass_percent' => $request->min_pass_percent]);
            $template->rules()->delete();
            foreach ($request->rules as $rule) {
                GradingRule::create([
                    'grading_template_id' => $template->id,
                    'from_percent' => $rule['from_percent'],
                    'to_percent' => $rule['to_percent'],
                    'grade' => $rule['grade'],
                    'remarks' => $rule['remarks'],
                ]);
            }
        });

        AuditLog::record('UPDATE', 'Grading', "Grading template updated: {$template->name}");

        return response()->json(['success' => true, 'message' => 'Template updated successfully.']);
    }

    public function setDefault(GradingTemplate $template)
    {
        GradingTemplate::query()->update(['is_default' => false]);
        $template->update(['is_default' => true]);

        return response()->json(['success' => true, 'message' => "{$template->name} set as default template."]);
    }

    public function destroy(GradingTemplate $template)
    {
        if ($template->is_default) {
            return response()->json(['success' => false, 'message' => 'Cannot delete the default template.'], 422);
        }
        $name = $template->name;
        $template->rules()->delete();
        $template->delete();

        AuditLog::record('DELETE', 'Grading', "Grading template deleted: {$name}");

        return response()->json(['success' => true, 'message' => "{$name} deleted successfully."]);
    }
}
