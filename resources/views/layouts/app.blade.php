<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Planova - Workspace')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">

    <!-- Synchronous Theme Detector avoiding FOUC -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('planova-theme');
            if (savedTheme === 'dark' || savedTheme === 'light') {
                document.documentElement.setAttribute('data-theme', savedTheme);
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('planova-theme', next);
            updateThemeIcons(next);
        }

        function updateThemeIcons(theme) {
            document.querySelectorAll('.theme-toggle-icon').forEach(el => {
                el.textContent = theme === 'dark' ? '☀️' : '🌙';
            });
            document.querySelectorAll('.theme-toggle-text').forEach(el => {
                el.textContent = theme === 'dark' ? 'Light' : 'Dark';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            updateThemeIcons(current);
        });
    </script>

    <style>
        /* Design Tokens & Theme Adaptations */
        :root {
            --brand-primary: #2563eb;
            --brand-primary-hover: #1d4ed8;
            --nav-link-color: var(--pico-color);
        }

        [data-theme="light"] {
            --badge-approved-bg: #c8e6c9;
            --badge-approved-text: #1b5e20;
            --badge-review-bg: #ffe0b2;
            --badge-review-text: #e65100;
            --badge-working-bg: #bbdefb;
            --badge-working-text: #0d47a1;
            --panel-muted-bg: #f8fafc;
            --panel-muted-border: #e2e8f0;
        }

        [data-theme="dark"] {
            --badge-approved-bg: rgba(34, 197, 94, 0.2);
            --badge-approved-text: #4ade80;
            --badge-review-bg: rgba(249, 115, 22, 0.2);
            --badge-review-text: #fb923c;
            --badge-working-bg: rgba(59, 130, 246, 0.2);
            --badge-working-text: #60a5fa;
            --panel-muted-bg: #1e293b;
            --panel-muted-border: #334155;
        }

        .theme-toggle-nav-btn {
            background: transparent;
            border: 1px solid var(--pico-muted-border-color);
            color: var(--pico-color);
            padding: 3px 10px;
            font-size: 0.8rem;
            border-radius: 16px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin: 0;
            width: auto;
            line-height: 1.3;
        }

        .theme-toggle-nav-btn:hover {
            border-color: var(--brand-primary);
        }

        .brand-nav-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: -0.5px;
            color: var(--brand-primary);
            text-decoration: none;
        }

        .brand-nav-icon {
            width: 24px;
            height: 24px;
            background: #2563eb;
            color: #ffffff;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        article {
            border-radius: 8px;
            border: 1px solid var(--pico-muted-border-color);
        }
    </style>
</head>
<body class="container" style="padding-top: 15px; padding-bottom: 30px;">
    @auth
    <nav style="margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <ul>
            <li>
                <a href="{{ url('/') }}" class="brand-nav-title">
                    <span class="brand-nav-icon">▲</span>
                    <span>PLANOVA</span>
                </a>
            </li>
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
                        <span style="background: #dc2626; color: white; border-radius: 10px; padding: 1px 7px; font-size: 0.72rem; font-weight: bold;">
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
        <ul style="display: flex; align-items: center; gap: 12px;">
            <li>
                <button type="button" class="theme-toggle-nav-btn" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
                    <span class="theme-toggle-icon">🌙</span>
                    <span class="theme-toggle-text">Theme</span>
                </button>
            </li>
            <li style="font-size: 0.9rem;">
                {{ auth()->user()->name }} <small style="color: var(--pico-muted-color);">({{ ucfirst(auth()->user()->role) }})</small>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="secondary" style="padding: 4px 10px; font-size: 0.8rem; margin: 0;">Logout</button>
                </form>
            </li>
        </ul>
    </nav>
    @endauth

    @if(session('success'))
        <article style="background: rgba(34, 197, 94, 0.12); color: #15803d; border: 1px solid rgba(34, 197, 94, 0.3); padding: 12px; margin-bottom: 20px; border-radius: 6px;">
            {{ session('success') }}
        </article>
    @endif

    @if(session('error') || $errors->has('error'))
        <article style="background: rgba(239, 68, 68, 0.12); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px; margin-bottom: 20px; border-radius: 6px;">
            {{ session('error') ?? $errors->first('error') }}
        </article>
    @endif

    <main>@yield('content')</main>
</body>
</html>
