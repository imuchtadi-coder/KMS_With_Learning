<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Http\Request;

class DiscussionReplyController extends Controller
{
    public function store(Request $request, Discussion $discussion)
    {
        $this->authorize('reply', $discussion);

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $reply = DiscussionReply::create([
            'discussion_id' => $discussion->id,
            'content' => $validated['content'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('discussions.show', $discussion)
            ->with('success', 'Reply posted.');
    }

    public function update(Request $request, Discussion $discussion, DiscussionReply $reply)
    {
        abort_unless($reply->discussion_id === $discussion->id, 404);
        $this->authorize('update', $reply);

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $reply->update($validated);

        return redirect()->route('discussions.show', $reply->discussion_id)
            ->with('success', 'Reply updated.');
    }

    public function destroy(Discussion $discussion, DiscussionReply $reply)
    {
        abort_unless($reply->discussion_id === $discussion->id, 404);
        $this->authorize('delete', $reply);

        $discussionId = $reply->discussion_id;
        $reply->delete();

        return redirect()->route('discussions.show', $discussionId)
            ->with('success', 'Reply deleted.');
    }

    public function toggleSolution(Request $request, Discussion $discussion, DiscussionReply $reply)
    {
        abort_unless($reply->discussion_id === $discussion->id, 404);
        $this->authorize('verifySolution', $discussion);

        if ($reply->is_solution) {
            $reply->update([
                'is_solution' => false,
                'verified_at' => null,
                'verified_by' => null,
            ]);

            $discussion->update([
                'is_resolved' => false,
                'accepted_reply_id' => null,
            ]);

            return redirect()->back()->with('success', 'Status solusi terverifikasi dibatalkan.');
        }

        // Unmark previous solutions
        $discussion->replies()->where('is_solution', true)->update([
            'is_solution' => false,
            'verified_at' => null,
            'verified_by' => null,
        ]);

        $reply->update([
            'is_solution' => true,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        $discussion->update([
            'is_resolved' => true,
            'accepted_reply_id' => $reply->id,
        ]);

        return redirect()->back()->with('success', 'Jawaban berhasil ditandai sebagai solusi terverifikasi!');
    }
}
