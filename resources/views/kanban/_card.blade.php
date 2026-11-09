<div class="kanban-card priority-{{ $task->priority ?? 'medium' }}" 
     id="task-card-{{ $task->id }}" 
     data-task-id="{{ $task->id }}" 
     draggable="true" 
     ondragstart="handleDragStart(event)" 
     ondragend="handleDragEnd(event)">
    
    <!-- Project & Priority Badges -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
        <span style="font-size: 0.75rem; color: #1976d2; font-weight: 600;">
            {{ $task->project->title ?? 'General' }}
        </span>
        <div>
            @if($task->is_approved)
                <span class="badge" style="background: #c8e6c9; color: #2e7d32;">✓ Approved</span>
            @endif
            <span class="badge" style="background: {{ $task->priority === 'high' ? '#ffebee' : ($task->priority === 'medium' ? '#fffde7' : '#e8f5e9') }}; color: {{ $task->priority === 'high' ? '#c62828' : ($task->priority === 'medium' ? '#f57f17' : '#2e7d32') }};">
                {{ ucfirst($task->priority ?? 'medium') }}
            </span>
        </div>
    </div>

    <!-- Task Title & Description -->
    <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 6px; color: #172b4d;">
        {{ $task->title }}
    </div>
    @if($task->description)
        <p style="font-size: 0.8rem; color: #5e6c84; margin-bottom: 10px; line-height: 1.3;">
            {{ Str::limit($task->description, 80) }}
        </p>
    @endif

    <!-- Assignee & Due Date -->
    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #5e6c84; margin-bottom: 8px;">
        <span>👤 {{ $task->assignee->name ?? 'Unassigned' }}</span>
        <span>📅 {{ $task->due_date }}</span>
    </div>

    <!-- Footer: Discussion & Quick Move -->
    <div style="margin-top: 8px; border-top: 1px dashed #dfe1e6; padding-top: 6px; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('tasks.show', $task) }}" style="font-size: 0.75rem; font-weight: 600;">
            💬 Discussion ({{ $task->comments->count() }})
        </a>
        <select onchange="handleStatusSelect(this, '{{ $task->id }}')" style="width: auto; font-size: 0.75rem; padding: 2px 6px; margin: 0; height: auto;">
            <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>To Do</option>
            <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
        </select>
    </div>
</div>
