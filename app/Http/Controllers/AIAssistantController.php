<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AIAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AIAssistantController extends Controller
{
    /** @var AIAssistantService */
    protected $ai;

    public function __construct(AIAssistantService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Show the AI assistant page.
     */
    public function index(): View
    {
        $conversations = AiConversation::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('ai.assistant', compact('conversations'));
    }

    /**
     * Send a message and get AI response.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable|exists:ai_conversations,id'
        ]);

        // Get or create conversation
        if ($request->conversation_id) {
            $conversation = AiConversation::where('user_id', Auth::id())
                ->findOrFail($request->conversation_id);
        } else {
            $conversation = AiConversation::create([
                'user_id' => Auth::id(),
                'title' => mb_strimwidth($request->message, 0, 50, '...')
            ]);
        }

        // Build history BEFORE saving the new user message so it isn't sent twice
        $history = $conversation->messages()
            ->orderBy('created_at')
            ->whereIn('role', ['user', 'assistant'])
            ->get()
            ->map(fn($msg) => [
                'role'    => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        // Save user message
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role'    => 'user',
            'content' => $request->message,
        ]);

        // Get AI response
        $response = $this->ai->chat($request->message, $history);

        // Save AI reply with metadata (function calls)
        $aiMessage = AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $response['reply'],
            'metadata' => ['function_calls' => $response['function_calls'] ?? []]
        ]);

        return response()->json([
            'reply' => $response['reply'],
            'conversation_id' => $conversation->id,
            'timestamp' => $aiMessage->created_at->diffForHumans(),
            'function_calls' => $response['function_calls'] ?? []  // <-- send tool calls to frontend
        ]);
    }

    /**
     * Get messages for a specific conversation, including function calls.
     */
    public function getConversation(int $id): JsonResponse
    {
        $conversation = AiConversation::where('user_id', Auth::id())
            ->with('messages')
            ->findOrFail($id);

        return response()->json([
            'messages' => $conversation->messages->map(fn($m) => [
                'role'           => $m->role,
                'content'        => $m->content,
                'time'           => $m->created_at->diffForHumans(),
                'function_calls' => $m->metadata['function_calls'] ?? [],
            ])->toArray()
        ]);
    }

    /**
     * Delete a conversation and all its messages.
     */
    public function deleteConversation(int $id): JsonResponse
    {
        $conversation = AiConversation::where('user_id', Auth::id())->findOrFail($id);
        $conversation->delete();

        return response()->json(['success' => true]);
    }
}