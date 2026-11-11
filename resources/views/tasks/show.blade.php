@extends('layouts.app')
@section('title', $task->title . ' - Details & Discussion')

@section('content')
<div style="display: flex; gap: 15px; margin-bottom: 15px;">
    <a href="{{ route('kanban.index') }}">← Back to Kanban Board</a>
    @if(in_array(auth()->user()->role, ['manager', 'admin']))
        <a href="{{ route('manager.projects.show', $task->project_id) }}">← Back to Project Details</a>
    @endif
</div>

<!-- 1. Task Header & Metadata -->
<article>
    <header style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <span style="font-size: 0.85rem; color: #1976d2; font-weight: 600;">
                Project: {{ $task->project->title ?? 'General' }}
            </span>
            <h2 style="margin: 5px 0 0 0;">{{ $task->title }}</h2>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <mark style="background: {{ $task->priority === 'high' ? 'rgba(239, 68, 68, 0.15)' : ($task->priority === 'medium' ? 'rgba(234, 179, 8, 0.15)' : 'rgba(34, 197, 94, 0.15)') }}; color: {{ $task->priority === 'high' ? '#ef4444' : ($task->priority === 'medium' ? '#eab308' : '#22c55e') }};">
                Priority: {{ ucfirst($task->priority ?? 'medium') }}
            </mark>

            @if($task->is_approved)
                <mark style="background: var(--badge-approved-bg); color: var(--badge-approved-text);">✓ Approved by Manager</mark>
            @elseif($task->status === 'completed')
                <mark style="background: var(--badge-review-bg); color: var(--badge-review-text);">⏳ Submitted for Review</mark>
            @else
                <mark style="background: var(--badge-working-bg); color: var(--badge-working-text);">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</mark>
            @endif

            <!-- Quick Status Selector -->
            <form method="POST" action="{{ route('tasks.update-status', $task) }}" style="display: inline-flex; align-items: center; margin: 0;">
                @csrf
                @method('PATCH')
                <select name="status" onchange="this.form.submit()" style="font-size: 0.8rem; padding: 2px 8px; margin: 0; width: auto; height: auto;">
                    <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>To Do</option>
                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </form>
        </div>
    </header>

    <div class="grid" style="margin-top: 15px;">
        <div><strong>Assigned To:</strong> {{ $task->assignee->name ?? 'Unassigned' }} ({{ $task->assignee->department ?? 'General' }})</div>
        <div><strong>Start Date:</strong> {{ $task->start_date ?? 'Not set' }}</div>
        <div><strong>Due Date:</strong> {{ $task->due_date }}</div>
    </div>

    @if($task->description)
        <hr>
        <p style="margin: 0;"><strong>Task Description:</strong><br>{{ $task->description }}</p>
    @endif

    @if($task->attachment_path)
        <hr>
        <div>
            <strong>Attached Specification:</strong>
            <a href="{{ asset('storage/' . $task->attachment_path) }}" target="_blank" role="button" class="outline" style="padding: 4px 10px; font-size: 0.85rem; margin-left: 10px;">
                📎 Open Task Attachment
            </a>
        </div>
    @endif

    <!-- Workflow Actions: Submission & Review -->
    <hr>
    <div style="background: var(--panel-muted-bg); border: 1px solid var(--panel-muted-border); border-radius: 6px; padding: 14px; margin-top: 10px;">
        <h4 style="margin-top: 0; margin-bottom: 8px;">Review & Approval Workflow</h4>

        @if(auth()->user()->id === $task->assigned_to && !$task->is_approved)
            <details style="margin: 0;">
                <summary role="button" class="secondary" style="padding: 6px 14px; font-size: 0.9rem; display: inline-block;">
                    🚀 Submit Work for Manager Review
                </summary>
                <form method="POST" action="{{ route('tasks.submit-review', $task) }}" enctype="multipart/form-data" style="margin-top: 15px;">
                    @csrf
                    <label><strong>Submission Notes / Deliverable Summary:</strong></label>
                    <textarea name="submission_notes" rows="2" placeholder="e.g. Design files finalized according to specs and ready for review.">{{ old('submission_notes', $task->submission_notes) }}</textarea>

                    <label style="margin-top: 8px;"><strong>Upload Work Deliverable (Optional PDF, ZIP, Image):</strong></label>
                    <input type="file" name="deliverable">

                    <button type="submit" style="margin-top: 10px; padding: 6px 14px;">Confirm & Submit for Review</button>
                </form>
            </details>
        @endif

        @if(in_array(auth()->user()->role, ['manager', 'admin']))
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 8px;">
                @if(!$task->is_approved)
                    <form method="POST" action="{{ route('tasks.approve', $task) }}" style="margin: 0;">
                        @csrf
                        <button type="submit" style="padding: 6px 14px; font-size: 0.9rem; background: #2e7d32; border-color: #2e7d32; margin: 0;">
                            ✓ Approve Work
                        </button>
                    </form>
                @else
                    <span style="color: #2e7d32; font-weight: 600;">✓ Work is approved.</span>
                @endif

                <details style="display: inline-block; margin: 0;">
                    <summary role="button" class="outline" style="padding: 6px 14px; font-size: 0.9rem; margin: 0;">
                        ⚠️ Request Corrections
                    </summary>
                    <form method="POST" action="{{ route('tasks.request-correction', $task) }}" style="margin-top: 10px; min-width: 300px;">
                        @csrf
                        <label><small><strong>Reason / Requested Changes:</strong></small></label>
                        <textarea name="revision_notes" rows="2" placeholder="Explain what changes or corrections are needed..." required></textarea>
                        <button type="submit" class="secondary" style="padding: 4px 10px; font-size: 0.85rem; margin-top: 6px;">
                            Send Revision Request
                        </button>
                    </form>
                </details>
            </div>
        @endif
    </div>
</article>

<!-- 2. Team Communication & Comments Feed -->
<article>
    <h3>💬 Task Communication Thread ({{ $task->comments->count() }})</h3>
    <p><small>Discuss project updates, ask questions, or share files directly within this task.</small></p>

    <!-- Message Stream -->
    <div style="display: flex; flex-direction: column; gap: 16px; margin: 20px 0;">
        @forelse($task->comments as $comment)
            <div style="background: {{ $comment->user_id === auth()->id() ? 'var(--panel-muted-bg)' : 'var(--pico-card-background-color)' }}; border: 1px solid var(--pico-muted-border-color); border-radius: 8px; padding: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <div>
                        <strong>{{ $comment->user->name ?? 'System User' }}</strong>
                        <small style="color: var(--pico-muted-color); margin-left: 6px;">({{ ucfirst($comment->user->role ?? 'user') }})</small>
                    </div>
                    <small style="color: var(--pico-muted-color);">{{ $comment->created_at->diffForHumans() }}</small>
                </div>

                <div style="white-space: pre-wrap; font-size: 0.95rem; margin-bottom: 8px;">{{ $comment->content }}</div>

                <!-- Attachment (PDF / Image Preview / Download) -->
                @if($comment->attachment_path)
                    <div style="margin-top: 8px; padding: 8px 12px; background: var(--pico-background-color); border-radius: 6px; border: 1px dashed var(--pico-muted-border-color); display: inline-block;">
                        <a href="{{ asset('storage/' . $comment->attachment_path) }}" target="_blank" style="text-decoration: none;">
                            📎 <strong>{{ $comment->attachment_name ?? 'Download Attachment' }}</strong>
                        </a>
                        @php
                            $ext = strtolower(pathinfo($comment->attachment_path, PATHINFO_EXTENSION));
                        @endphp
                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <div style="margin-top: 8px;">
                                <img src="{{ asset('storage/' . $comment->attachment_path) }}" alt="Preview" style="max-height: 160px; border-radius: 4px; border: 1px solid #eee;">
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <p><em>No messages in this task thread yet. Post the first message below!</em></p>
        @endforelse
    </div>

    <hr>

    <!-- Post Comment Form -->
    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" enctype="multipart/form-data">
        @csrf
        <label><strong>Post a Reply or Update</strong></label>
        <textarea name="content" rows="3" placeholder="Write your message here (e.g. 'The design is ready for review.')..."></textarea>
        @error('content') <small style="color: red;">{{ $message }}</small> @enderror

        <label style="margin-top: 10px;"><strong>Attach File (PDF, Image, or Doc - Max 10MB)</strong></label>
        <input type="file" name="attachment">
        @error('attachment') <small style="color: red;">{{ $message }}</small> @enderror

        <button type="submit" style="margin-top: 12px;">Send Message</button>
    </form>
</article>
@endsection
