<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Company Portal')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body class="container" style="padding-top: 20px;">
    @auth
    <nav>
        <ul>
            <li><strong><a href="{{ url('/') }}" style="text-decoration: none; color: inherit;">Company Portal</a></strong></li>
            @if(auth()->user()->role === 'employee')
                <li><a href="{{ route('employee.dashboard') }}">Dashboard</a></li>
            @elseif(auth()->user()->role === 'manager')
                <li><a href="{{ route('manager.dashboard') }}">Dashboard</a></li>
            @elseif(auth()->user()->role === 'admin')
                <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            @endif
            <li><a href="{{ route('kanban.index') }}">Kanban Board</a></li>
            <li>
                <a href="{{ route('notifications.index') }}" style="display: inline-flex; align-items: center; gap: 4px;">
                    🔔 Notifications
                    @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                    @if($unreadCount > 0)
                        <span style="background: #d32f2f; color: white; border-radius: 10px; padding: 1px 7px; font-size: 0.75rem; font-weight: bold;">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>
            </li>
            @if(in_array(auth()->user()->role, ['admin', 'manager']))
                <li><a href="{{ route('employees.index') }}">Manage Employees</a></li>
                <li><a href="{{ route('manager.reports.index') }}">Reports</a></li>
            @endif
        </ul>
        <ul>
            <li>{{ auth()->user()->name }} (<em>{{ ucfirst(auth()->user()->role) }}</em>)</li>
            <li>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="secondary" style="padding: 4px 12px; margin: 0;">Logout</button>
                </form>
            </li>
        </ul>
    </nav>
    @endauth

    @if(session('success'))
        <article style="background: #e6f4ea; color: #137333; padding: 12px; margin-bottom: 20px;">
            {{ session('success') }}
        </article>
    @endif

    <main>@yield('content')</main>
</body>
</html>
