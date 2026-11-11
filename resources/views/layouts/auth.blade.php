<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Planova - Project Management')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    
    <script>
        // Synchronous theme detector avoiding FOUC
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
                el.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            updateThemeIcons(current);
        });
    </script>

    <style>
        :root {
            --brand-blue: #2563eb;
            --brand-blue-hover: #1d4ed8;
            --brand-dark-bg: #0f172a;
            --brand-light-bg: #f8fafc;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        .auth-container {
            display: grid;
            grid-template-columns: 1.15fr 1fr;
            width: 100%;
            min-height: 100vh;
        }

        /* LEFT BRANDING SECTION */
        .auth-left {
            background: linear-gradient(145deg, #1e3a8a 0%, #0f172a 100%);
            color: #ffffff;
            padding: 50px 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .auth-left::before {
            content: '';
            position: absolute;
            top: -120px;
            right: -120px;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, transparent 70%);
            pointer-events: none;
        }

        .brand-logo-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
            text-decoration: none;
            margin-bottom: 30px;
        }

        .brand-logo-icon {
            width: 40px;
            height: 40px;
            background: #3b82f6;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
        }

        .hero-headline {
            font-size: 2.3rem;
            font-weight: 800;
            line-height: 1.25;
            color: #ffffff;
            margin-bottom: 15px;
        }

        .hero-subhead {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #93c5fd;
            margin-bottom: 35px;
            max-width: 520px;
        }

        /* 4 Feature Descriptions */
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 35px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 16px;
            transition: transform 0.2s, background 0.2s;
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .feature-icon {
            font-size: 1.4rem;
            margin-bottom: 8px;
            display: inline-block;
        }

        .feature-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .feature-desc {
            font-size: 0.8rem;
            color: #cbd5e1;
            line-height: 1.4;
            margin: 0;
        }

        /* Decorative Kanban Snippet Mockup */
        .preview-mockup {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.82rem;
            color: #cbd5e1;
        }

        /* RIGHT FORM SECTION */
        .auth-right {
            padding: 40px 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: var(--pico-background-color);
        }

        .auth-top-nav {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .theme-btn {
            background: transparent;
            border: 1px solid var(--pico-muted-border-color);
            color: var(--pico-color);
            padding: 6px 14px;
            font-size: 0.85rem;
            border-radius: 20px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: auto;
            margin: 0;
        }

        .theme-btn:hover {
            border-color: var(--brand-blue);
        }

        .auth-form-card {
            max-width: 440px;
            width: 100%;
            margin: 0 auto;
        }

        .password-toggle-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle-wrapper input {
            padding-right: 44px;
            margin-bottom: 0;
        }

        .password-toggle-btn {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: var(--pico-muted-color);
            cursor: pointer;
            padding: 6px;
            font-size: 1.1rem;
            margin: 0;
            width: auto;
            line-height: 1;
        }

        /* Responsive Layout for Mobile */
        @media (max-width: 900px) {
            .auth-container {
                grid-template-columns: 1fr;
            }
            .auth-left {
                padding: 40px 30px;
            }
            .features-grid {
                grid-template-columns: 1fr;
            }
            .auth-right {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- LEFT SECTION: Branding & Feature Overview -->
        <div class="auth-left">
            <div>
                <a href="{{ url('/') }}" class="brand-logo-badge">
                    <div class="brand-logo-icon">▲</div>
                    <span>PLANOVA</span>
                </a>

                <h1 class="hero-headline">Everything your team needs to get work done.</h1>
                <p class="hero-subhead">Planova brings projects, people and tasks together in one organized workspace.</p>

                <!-- 4 Feature Highlights -->
                <div class="features-grid">
                    <div class="feature-card">
                        <span class="feature-icon">📁</span>
                        <div class="feature-title">Project Management</div>
                        <p class="feature-desc">Create projects, organize teams and manage deadlines effortlessly.</p>
                    </div>

                    <div class="feature-card">
                        <span class="feature-icon">📋</span>
                        <div class="feature-title">Task Tracking</div>
                        <p class="feature-desc">Assign tasks and monitor work through a live, drag-and-drop Kanban board.</p>
                    </div>

                    <div class="feature-card">
                        <span class="feature-icon">💬</span>
                        <div class="feature-title">Team Collaboration</div>
                        <p class="feature-desc">Communicate, share files, and discuss tasks in dedicated threads.</p>
                    </div>

                    <div class="feature-card">
                        <span class="feature-icon">📊</span>
                        <div class="feature-title">Productivity Reports</div>
                        <p class="feature-desc">Track employee performance, workload balance, and project completion.</p>
                    </div>
                </div>
            </div>

            <!-- Subtle Decorative Preview -->
            <div class="preview-mockup">
                <span>⚡ Live Status: <strong>Website Redesign</strong> (Active)</span>
                <span style="background: rgba(34, 197, 94, 0.2); color: #4ade80; padding: 2px 8px; border-radius: 4px; font-weight: 600;">✓ In Sync</span>
            </div>
        </div>

        <!-- RIGHT SECTION: Authentication Form -->
        <div class="auth-right">
            <div class="auth-top-nav">
                <button type="button" class="theme-btn" onclick="toggleTheme()" aria-label="Toggle theme">
                    <span class="theme-toggle-icon">🌙</span>
                    <span class="theme-toggle-text">Dark Mode</span>
                </button>
            </div>

            <div class="auth-form-card">
                @if(session('success'))
                    <article style="background: #e6f4ea; color: #137333; padding: 12px; margin-bottom: 20px; border-radius: 6px;">
                        {{ session('success') }}
                    </article>
                @endif

                @if($errors->has('error'))
                    <article style="background: #fde8e8; color: #9b1c1c; padding: 12px; margin-bottom: 20px; border-radius: 6px;">
                        {{ $errors->first('error') }}
                    </article>
                @endif

                @yield('content')
            </div>

            <div style="text-align: center; font-size: 0.8rem; color: var(--pico-muted-color); padding-top: 20px;">
                © {{ date('Y') }} Planova. Built for modern engineering and operations teams.
            </div>
        </div>
    </div>

    <!-- Password visibility toggle script -->
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.textContent = '👁️';
            } else {
                input.type = 'password';
                if (icon) icon.textContent = '🙈';
            }
        }
    </script>
</body>
</html>
