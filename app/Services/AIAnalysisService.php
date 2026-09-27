<?php

namespace App\Services;

use App\Models\Mark;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIAnalysisService
{
    /**
     * Build the student payload used by analyzeStudentPerformance(). Expects
     * $student->marks (with subject and grade) already eager-loaded.
     *
     * Includes each subject's class average alongside the student's own
     * score — without it, the AI has no way to tell "62%" apart from
     * "weak" vs "actually above average for this subject", so it either
     * hedges or invents a judgement call that isn't grounded in real data.
     */
    public function buildStudentPayload(Student $student): array
    {
        $classAverages = $student->class_id
            ? Mark::where('class_id', $student->class_id)
                ->selectRaw('subject_id, ROUND(AVG(mark), 1) as avg_mark')
                ->groupBy('subject_id')
                ->pluck('avg_mark', 'subject_id')
            : collect();

        return [
            'id'    => $student->id,
            'name'  => $student->first_name . ' ' . $student->last_name,
            'class' => $student->class->name ?? 'N/A',
            'marks' => $student->marks->map(fn ($m) => [
                'subject' => $m->subject->name ?? 'N/A',
                // Mark's actual column is `mark`, not `score`; `grade` is a
                // belongsTo relation, not a scalar — using either directly
                // silently sent null/whole-object noise to the AI before.
                'score'          => $m->mark,
                'grade'          => $m->grade->name ?? 'N/A',
                'class_average'  => $classAverages[$m->subject_id] ?? null,
            ])->toArray(),
        ];
    }

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $model;

    /** @var string */
    protected $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key', env('GROQ_API_KEY'));
        $this->model  = config('services.groq.model', env('GROQ_MODEL', 'openai/gpt-oss-120b'));
    }

    /**
     * Send a prompt to Groq AI with caching.
     *
     * @param string $prompt
     * @param string|null $cacheKey
     * @param int $cacheMinutes
     * @return string
     */
    public function ask(string $prompt, ?string $cacheKey = null, int $cacheMinutes = 60): string
    {
        if (empty($this->apiKey)) {
            return "⚠️ **GROQ_API_KEY not configured.**\n\nAdd it to your `.env` file:\n```\nGROQ_API_KEY=gsk_...\n```\nGet a free key at **console.groq.com/keys**";
        }

        // Return cached response if available
        if ($cacheKey) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                Log::info('Groq response from cache', ['key' => $cacheKey]);
                return $cached . "\n\n💾 (Cached result)";
            }
        }

        try {
            Log::info('Calling Groq API', ['model' => $this->model]);

            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->post($this->baseUrl, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional education and data analyst for a Tanzanian school. Always respond in structured Markdown with headers, bullet points, and tables where appropriate. Be specific, data-driven, and actionable. Never add disclaimers or filler text.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 1800,
                    // openai/gpt-oss-120b is a reasoning model: it spends part
                    // of max_tokens on an internal reasoning chain before
                    // writing the visible answer, and that cost scales with
                    // how much data is in the prompt. On a real student's 28
                    // marks it burned 1012 of 1200 tokens reasoning, cutting
                    // the report off mid-sentence (finish_reason: "length").
                    // 'low' keeps reasoning minimal (verified: ~6-400 tokens
                    // instead of 1000+) so the budget goes to the actual
                    // report; combined with the higher ceiling above, both
                    // the student and class report prompts finish cleanly
                    // (finish_reason: "stop") against real data.
                    'reasoning_effort' => 'low',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $result = $data['choices'][0]['message']['content'] ?? 'No response generated';

                // Cache successful response
                if ($cacheKey) {
                    Cache::put($cacheKey, $result, now()->addMinutes($cacheMinutes));
                }

                return $result;
            }

            // Handle API errors
            $statusCode = $response->status();
            $errorMessage = $response->json()['error']['message'] ?? 'Unknown error';
            
            Log::error("Groq API error ({$statusCode}): " . $errorMessage);

            if ($statusCode === 401 || $statusCode === 403) {
                return "⚠️ Invalid API key. Please check your GROQ_API_KEY in .env file.";
            }

            if ($statusCode === 429) {
                return "⚠️ Rate limit reached (30 requests/minute). Please wait a moment and try again.";
            }

            return "⚠️ API error ({$statusCode}): " . $errorMessage;

        } catch (\Exception $e) {
            Log::error('Groq connection error: ' . $e->getMessage());
            
            return "⚠️ Unable to connect to AI service.\n\n"
                 . "Error: " . $e->getMessage() . "\n\n"
                 . "Please check:\n"
                 . "1. Your internet connection\n"
                 . "2. GROQ_API_KEY is correct in .env\n"
                 . "3. The Groq service is online (https://status.groq.com)";
        }
    }

    public function analyzeStudentPerformance(array $studentData): string
    {
        $studentId = $studentData['id'] ?? md5(json_encode($studentData));
        $cacheKey  = "ai_student_{$studentId}";

        $count  = count($studentData['marks'] ?? []);
        $prompt = <<<PROMPT
You are a professional education analyst for a Tanzanian school. Analyze the student data below and write a detailed, structured report in Markdown.

Each subject includes the student's score AND the class_average for that
subject — use the comparison, not the raw score alone, to judge strengths
and weaknesses. A 65% is a weakness if the class averages 85%, and a
strength if the class averages 45%. Call out that gap explicitly (e.g.
"12 points above the class average of 53%").

Use EXACTLY this format — do not skip any section:

## Student Performance Report: {$studentData['name']}
**Class:** {$studentData['class']}

---

### 📊 Performance Overview
Write 2-3 sentences summarising this student's overall academic standing, their average vs. the class average, and general trend.

### ✅ Top Strengths
List the 2-3 subjects where the student is strongest relative to the class average, not just the highest raw score. State both numbers and the gap.

### ⚠️ Areas Needing Improvement
List the 2-3 subjects where the student is furthest below the class average. State both numbers and the gap — a low score that matches the whole class struggling is a different problem than a low score where peers are doing fine.

### 💡 Actionable Recommendations
Give 3 concrete, specific recommendations that the teacher or parent can act on THIS WEEK, tied directly to the weak subjects identified above.

---
*Based on {$count} subject records*

---

Student Data (JSON):
{$this->json($studentData)}
PROMPT;

        return $this->ask($prompt, $cacheKey, 1440);
    }

    public function analyzeClassPerformance(array $classData): string
    {
        $classId  = $classData['class_id'] ?? md5(json_encode($classData));
        $cacheKey = "ai_class_{$classId}";

        $prompt = <<<PROMPT
You are a professional education analyst for a Tanzanian school. Analyze the class performance data below and write a detailed, structured report in Markdown.

Use EXACTLY this format:

## Class Performance Report

---

### 📈 Overall Summary
Write 2-3 sentences on the class's general academic health. Mention the overall average and what it means.

### 🏆 Subject Ranking (Best → Worst)
List each subject with average, pass rate, highest, and lowest scores in a Markdown table:

| Subject | Average | Pass Rate | Highest | Lowest |
|---------|---------|-----------|---------|--------|
| ...     | ...%    | ...%      | ...     | ...    |

### ✅ Best Performing Subject
Name the top subject and explain what is going well.

### ⚠️ Subject Needing Urgent Attention
Name the weakest subject. Explain the risk and what a low pass rate means for students.

### 👥 Students to Watch
The data includes top_students and bottom_students (each student's overall average across all subjects). List the bottom_students by name with their average, and briefly note what kind of support each likely needs. Then list the top_students by name as ones worth recognizing.

### 💡 Teacher Recommendations
Give 3 specific, immediately actionable teaching strategies tailored to this class's data.

---

Class Data (JSON):
{$this->json($classData)}
PROMPT;

        return $this->ask($prompt, $cacheKey, 1440);
    }

    public function suggestInterventions($strugglingStudents): string
    {
        $data = $strugglingStudents instanceof \Illuminate\Support\Collection
            ? $strugglingStudents->toArray()
            : $strugglingStudents;

        $cacheKey = "ai_interventions_" . md5(json_encode($data));
        $count    = count($data);

        $prompt = <<<PROMPT
You are a professional student welfare and education specialist for a Tanzanian school. {$count} students are academically struggling (average below 40). Provide structured intervention plans.

Use EXACTLY this format for EACH student:

---

### 🎯 [Student Name] — Avg: [score]%

**Likely Root Causes:**
- [Cause 1]
- [Cause 2]

**Intervention Strategy:**
[Concrete 2-3 step action plan]

**Teacher Action:** [What the teacher should do this week]
**Parent/Guardian Action:** [What the parent should do]

---

After all students, add a section:

## 📋 Priority Action List for Class Teacher
List the top 3 most urgent actions to take immediately for the whole group.

---

Struggling Students Data (JSON):
{$this->json($data)}
PROMPT;

        return $this->ask($prompt, $cacheKey, 1440);
    }

    public function analyzeFinance(array $financeData): string
    {
        $cacheKey = "ai_finance_" . date('Y-m-d');

        $prompt = <<<PROMPT
You are a financial analyst for a Tanzanian school. Analyze the pocket money data below and write a professional financial insights report in Markdown.

Use EXACTLY this format:

## 💰 School Finance Insights Report
**Date:** {$this->today()}

---

### 📊 Summary
| Metric | Amount |
|--------|--------|
| Total Deposits | TZS [amount] |
| Total Withdrawals | TZS [amount] |
| Net Balance | TZS [amount] |

### 📈 Financial Health Assessment
Write 2-3 sentences assessing the balance ratio (deposits vs withdrawals). Is it healthy? What does it indicate about student spending?

### ✅ Positive Trend
Identify one concrete positive pattern in the data.

### 💡 Cost-Saving Recommendation
Give one specific, actionable recommendation the school finance office can implement.

### ⚠️ Risk Flag
Note any concern worth monitoring (e.g. high withdrawal-to-deposit ratio).

---

Finance Data (JSON):
{$this->json($financeData)}
PROMPT;

        return $this->ask($prompt, $cacheKey, 1440);
    }

    private function json(mixed $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function today(): string
    {
        return now()->format('d F Y');
    }
}