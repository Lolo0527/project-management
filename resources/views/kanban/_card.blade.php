<div class="kanban-card priority-{{ $task->priority ?? 'medium' }}" 
     id="task-card-{{ $task->id }}" 
     data-task-id="{{ $task->id }}" 
     draggable="true" 
     ondragstart="handleDragStart(event)" 
     ondragend="handleDragEnd(event)">
    
    <!-- Project & Priority Badges -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
        <span style="font-size: 0.75rem; color: var(--brand-primary, #2563eb); font-weight: 700;">
            {{ $task->project->title ?? 'General' }}
        </span>
        <div style="display: flex; gap: 4px; align-items: center;">
            @if($task->is_approved)
                <span class="badge" style="background: var(--badge-approved-bg, rgba(34, 197, 94, 0.2)); color: var(--badge-approved-text, #16a34a);">✓ Approved</span>
            @endif
            <span class="badge" style="background: {{ $task->priority === 'high' ? 'rgba(239, 68, 68, 0.15)' : ($task->priority === 'medium' ? 'rgba(234, 179, 8, 0.15)' : 'rgba(34, 197, 94, 0.15)') }}; color: {{ $task->priority === 'high' ? '#ef4444' : ($task->priority === 'medium' ? '#eab308' : '#22c55e') }};">
                {{ ucfirst($task->priority ?? 'medium') }}
            </span>
        </div>
    </div>

    <!-- Task Title & Description -->
    <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 6px; color: var(--pico-color);">
        {{ $task->title }}
    </div>
    @if($task->description)
        <p style="font-size: 0.8rem; color: var(--pico-muted-color); margin-bottom: 10px; line-height: 1.35;">
            {{ Str::limit($task->description, 80) }}
        </p>
    @endif

    <!-- Assignee & Due Date -->
    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--pico-muted-color); margin-bottom: 8px;">
        <span>👤 {{ $task->assignee->name ?? 'Unassigned' }}</span>
        <span>📅 {{ $task->due_date }}</span>
    </div>

    <!-- Footer: Discussion & Quick Move -->
    <div style="margin-top: 8px; border-top: 1px dashed var(--pico-muted-border-color); padding-top: 6px; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('tasks.show', $task) }}" style="font-size: 0.75rem; font-weight: 600; text-decoration: none;">
            💬 Discussion ({{ $task->comments->count() }})
        </a>
        <select onchange="handleStatusSelect(this, '{{ $task->id }}')" style="width: auto; font-size: 0.75rem; padding: 2px 6px; margin: 0; height: auto;">
            <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>To Do</option>
            <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
        </select>
    </div>
</div>
