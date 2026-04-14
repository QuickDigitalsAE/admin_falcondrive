<style>
    :root {
        --color-primary: #ffea7c;
        --color-secondary: #b27f2a;
        --color-dark: #111827;
        --color-dark-soft: #1f2937;
        --color-surface: #ffffff;
        --color-bg: #f5f7fb;
        --color-border: #e5e7eb;
        --color-text: #0f172a;
        --color-muted: #64748b;
        --sidebar-width: 212px;
        --sidebar-collapsed-width: 76px;
    }

    html {
        scroll-behavior: smooth;
        height: 100%;
        scrollbar-width: thin;
        scrollbar-color: #c9a64a #f6efe0;
    }

    body {
        margin: 0;
        min-height: 100vh;
        height: 100vh;
        overflow: hidden;
        overflow-x: hidden;
        background: var(--color-bg);
        color: var(--color-text);
    }

    #mainContent {
        min-width: 0;
        height: 100vh;
        overflow: hidden;
    }

    #pageMain {
        min-height: 0;
        height: 100%;
        overflow-y: auto;
        overflow-x: hidden;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    .admin-sticky-filter {
        position: sticky;
        top: 0;
        z-index: 15;
        background: #f7f5ee;
        padding-top: 4px;
        padding-bottom: 12px;
    }

    .admin-sticky-filter-card {
        border: 1px solid #eadfbe;
        background: rgba(252, 251, 247, 0.96);
        backdrop-filter: blur(8px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    #sidebar {
        scrollbar-width: thin;
        scrollbar-color: rgba(217, 181, 90, 0.78) transparent;
        transition: transform .28s ease, width .28s ease, min-width .28s ease, max-width .28s ease;
        backface-visibility: hidden;
        transform: translateZ(0);
        will-change: transform, width;
    }

    #sidebar::-webkit-scrollbar {
        width: 6px;
    }

    #sidebar::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 999px;
    }

    #sidebar::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(217, 181, 90, 0.65), rgba(183, 137, 34, 0.9));
        border-radius: 999px;
        border: 1px solid transparent;
        background-clip: padding-box;
    }

    #sidebar .sidebar-link,
    #sidebar .sidebar-collapse-btn,
    #sidebar .sidebar-sublink,
    #sidebar .sidebar-icon,
    #sidebar .sidebar-text {
        transition: all .2s ease;
    }

    @media (min-width: 1000px) {
        #sidebar {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            height: 100vh !important;
            width: var(--sidebar-width) !important;
            min-width: var(--sidebar-width) !important;
            max-width: var(--sidebar-width) !important;
            transform: translateX(0) !important;
            overflow-x: hidden !important;
        }

        #sidebar.sidebar-desktop-collapsed {
            width: var(--sidebar-collapsed-width) !important;
            min-width: var(--sidebar-collapsed-width) !important;
            max-width: var(--sidebar-collapsed-width) !important;
        }

        #sidebar.sidebar-desktop-collapsed .sidebar-text,
        #sidebar.sidebar-desktop-collapsed .sidebar-user-details,
        #sidebar.sidebar-desktop-collapsed .sidebar-role-badges,
        #sidebar.sidebar-desktop-collapsed .sidebar-brand-text,
        #sidebar.sidebar-desktop-collapsed .sidebar-arrow {
            display: none !important;
        }

        #sidebar.sidebar-desktop-collapsed .sidebar-link,
        #sidebar.sidebar-desktop-collapsed .sidebar-collapse-btn {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        #sidebar.sidebar-desktop-collapsed .sidebar-link span.flex,
        #sidebar.sidebar-desktop-collapsed .sidebar-collapse-btn span.flex {
            margin-right: 0 !important;
        }

        #sidebar.sidebar-desktop-collapsed nav {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    }

    @media (max-width: 999px) {
        #sidebar {
            width: min(212px, calc(100vw - 16px)) !important;
            min-width: min(212px, calc(100vw - 16px)) !important;
            max-width: min(212px, calc(100vw - 16px)) !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100dvh !important;
            transform: translateX(-100%);
            box-shadow: 14px 0 42px rgba(2, 6, 23, 0.35);
        }

        #sidebar.sidebar-open {
            transform: translateX(0) !important;
        }
    }
</style>