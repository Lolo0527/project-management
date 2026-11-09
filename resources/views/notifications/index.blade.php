@extends('layouts.app')
@section('title', 'Smart Notification Center')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0;">🔔 Notification Center</h2>
        <p style="margin: 0;"><small>Real-time updates on assignments, discussions, deadlines, and approvals.</small></p>
    </div>

    @if(auth()->user()->unreadNotifications->count() > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}" style="margin: 0;">
            @csrf
            <button type="submit" class="outline" style="padding: 6px 14px; font-size: 0.85rem; margin: 0;">
                ✓ Mark All as Read
            </button>
        </form>
    @endif
</div>

<!-- Filter Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 20px;">
    <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
       role="button" 
       class="{{ $filter === 'all' ? '' : 'secondary outline' }}" 
       style="padding: 5px 15px; font-size: 0.85rem;">
        All ({{ auth()->user()->notifications->count() }})
    </a>
    <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
       role="button" 
       class="{{ $filter === 'unread' ? '' : 'secondary outline' }}" 
       style="padding: 5px 15px; font-size: 0.85rem;">
        Unread ({{ auth()->user()->unreadNotifications->count() }})
    </a>
</div>

<!-- Notification Feed -->
<div style="display: flex; flex-direction: column; gap: 12px;">
    @forelse($notifications as $notif)
        @php
            $isUnread = is_null($notif->read_at);
            $data = $notif->data;
            $link = $data['link'] ?? '#';
        @endphp

        <article style="margin: 0; padding: 16px; background: {{ $isUnread ? '#f0f7ff' : '#ffffff' }}; border: 1px solid {{ $isUnread ? '#bbdefb' : '#e0e0e0' }}; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                <!-- Left: Icon & Text -->
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div style="font-size: 1.6rem; line-height: 1; padding: 8px; background: {{ $isUnread ? '#e3f2fd' : '#f5f5f5' }}; border-radius: 50%;">
                        {{ $data['icon'] ?? '🔔' }}
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 1rem; color: #172b4d; margin-bottom: 4px;">
                            {{ $data['title'] ?? 'Notification' }}
                            @if($isUnread)
                                <span style="display: inline-block; width: 8px; height: 8px; background: #1976d2; border-radius: 50%; margin-left: 6px;"></span>
                            @endif
                        </div>
                        <div style="font-size: 0.9rem; color: #42526e; margin-bottom: 8px;">
                            {{ $data['message'] ?? '' }}
                        </div>
                        <small style="color: #6b778c;">{{ $notif->created_at->diffForHumans() }}</small>
                    </div>
                </div>

                <!-- Right: Actions -->
                <div style="display: flex; gap: 8px; align-items: center;">
                    <form method="POST" action="{{ route('notifications.read', $notif->id) }}?redirect={{ urlencode($link) }}" style="margin: 0;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" style="padding: 4px 12px; font-size: 0.8rem; margin: 0;">View →</button>
                    </form>

                    @if($isUnread)
                        <form method="POST" action="{{ route('notifications.read', $notif->id) }}" style="margin: 0;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="secondary outline" title="Mark as Read" style="padding: 4px 8px; font-size: 0.8rem; margin: 0;">✓</button>
                        </form>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <article style="text-align: center; padding: 40px; color: gray;">
            <p style="font-size: 2rem; margin-bottom: 10px;">✨</p>
            <p><strong>All caught up!</strong> You have no {{ $filter === 'unread' ? 'unread' : '' }} notifications.</p>
        </article>
    @endforelse
</div>
@endsection
