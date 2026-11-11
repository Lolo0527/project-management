@extends('layouts.app')
@section('title', 'Kanban Board')

@section('content')
<style>
    .kanban-board {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        align-items: flex-start;
        margin-top: 20px;
    }
    .kanban-column {
        background: var(--panel-muted-bg, #f4f5f7);
        border-radius: 8px;
        padding: 16px;
        min-height: 480px;
        border: 1px solid var(--panel-muted-border, #dfe1e6);
    }
    .column-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        font-weight: bold;
        color: var(--pico-color);
    }
    .task-list {
        min-height: 400px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .kanban-card {
        background: var(--pico-card-background-color, #ffffff);
        border-radius: 6px;
        padding: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        cursor: grab;
        border-left: 4px solid #9e9e9e;
        border-top: 1px solid var(--pico-muted-border-color);
        border-right: 1px solid var(--pico-muted-border-color);
        border-bottom: 1px solid var(--pico-muted-border-color);
        transition: transform 0.15s, box-shadow 0.15s;
        color: var(--pico-color);
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .kanban-card.dragging {
        opacity: 0.4;
    }
    .kanban-card.priority-high { border-left-color: #ef4444; }
    .kanban-card.priority-medium { border-left-color: #f59e0b; }
    .kanban-card.priority-low { border-left-color: #10b981; }
    .badge {
        font-size: 0.75rem;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 600;
    }
    .badge-count {
        background: var(--pico-muted-border-color, #ebecf0);
        color: var(--pico-color, #172b4d);
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 0.8rem;
    }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="margin: 0;">📋 Kanban Board</h2>
        <p style="margin: 0;"><small>Drag and drop cards between columns to update their status instantly.</small></p>
    </div>

    <!-- Project Filter -->
    <div style="min-width: 260px;">
        <select onchange="window.location.href = this.value ? '{{ route('kanban.index') }}?project_id=' + this.value : '{{ route('kanban.index') }}'">
            <option value="">All Projects</option>
            @foreach($projects as $p)
                <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>
                    {{ $p->title }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="kanban-board">
    <!-- 1. TO DO COLUMN -->
    <div class="kanban-column" data-status="pending">
        <div class="column-header">
            <span>📝 To Do</span>
            <span class="badge-count" id="count-pending">{{ $todoTasks->count() }}</span>
        </div>
        <div class="task-list" id="col-pending" ondragover="allowDrop(event)" ondrop="handleDrop(event, 'pending')">
            @foreach($todoTasks as $task)
                @include('kanban._card', ['task' => $task])
            @endforeach
        </div>
    </div>

    <!-- 2. IN PROGRESS COLUMN -->
    <div class="kanban-column" data-status="in_progress">
        <div class="column-header">
            <span>⚡ In Progress</span>
            <span class="badge-count" id="count-in_progress">{{ $inProgressTasks->count() }}</span>
        </div>
        <div class="task-list" id="col-in_progress" ondragover="allowDrop(event)" ondrop="handleDrop(event, 'in_progress')">
            @foreach($inProgressTasks as $task)
                @include('kanban._card', ['task' => $task])
            @endforeach
        </div>
    </div>

    <!-- 3. COMPLETED COLUMN -->
    <div class="kanban-column" data-status="completed">
        <div class="column-header">
            <span>✅ Completed</span>
            <span class="badge-count" id="count-completed">{{ $completedTasks->count() }}</span>
        </div>
        <div class="task-list" id="col-completed" ondragover="allowDrop(event)" ondrop="handleDrop(event, 'completed')">
            @foreach($completedTasks as $task)
                @include('kanban._card', ['task' => $task])
            @endforeach
        </div>
    </div>
</div>

<script>
    let draggedCardId = null;

    function handleDragStart(e) {
        draggedCardId = e.target.getAttribute('data-task-id');
        e.target.classList.add('dragging');
        e.dataTransfer.setData('text/plain', draggedCardId);
    }

    function handleDragEnd(e) {
        e.target.classList.remove('dragging');
    }

    function allowDrop(e) {
        e.preventDefault();
    }

    function handleDrop(e, targetStatus) {
        e.preventDefault();
        const taskId = e.dataTransfer.getData('text/plain') || draggedCardId;
        const card = document.getElementById('task-card-' + taskId);
        const targetContainer = document.getElementById('col-' + targetStatus);

        if (!card || !targetContainer) return;

        targetContainer.appendChild(card);
        const selectInside = card.querySelector('select');
        if (selectInside) {
            selectInside.value = targetStatus;
        }
        updateCounters();
        saveTaskStatus(taskId, targetStatus);
    }

    // Quick move selector for mobile/touch
    function handleStatusSelect(selectEl, taskId) {
        const newStatus = selectEl.value;
        const card = document.getElementById('task-card-' + taskId);
        const targetContainer = document.getElementById('col-' + newStatus);
        
        if (card && targetContainer) {
            targetContainer.appendChild(card);
            updateCounters();
            saveTaskStatus(taskId, newStatus);
        }
    }

    function saveTaskStatus(taskId, status) {
        fetch('/tasks/' + taskId + '/status', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
        .then(res => res.json())
        .catch(err => {
            console.error('Error saving status:', err);
            alert('Could not update status. Please try again.');
        });
    }

    function updateCounters() {
        ['pending', 'in_progress', 'completed'].forEach(s => {
            const container = document.getElementById('col-' + s);
            const countEl = document.getElementById('count-' + s);
            if (container && countEl) {
                countEl.textContent = container.querySelectorAll('.kanban-card').length;
            }
        });
    }
</script>
@endsection
