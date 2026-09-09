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
}
