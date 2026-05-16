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
        scrollbar-color: rgba(201, 166, 74, 0.82) rgba(246, 239, 224, 0.45);
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

    * {
        scrollbar-width: thin;
        scrollbar-color: rgba(201, 166, 74, 0.82) rgba(246, 239, 224, 0.35);
    }

    *::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    *::-webkit-scrollbar-track {
        background: rgba(246, 239, 224, 0.45);
        border-radius: 999px;
    }

    *::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(217, 181, 90, 0.72), rgba(183, 137, 34, 0.88));
        border-radius: 999px;
        border: 1px solid rgba(246, 239, 224, 0.4);
    }

    *::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, rgba(208, 168, 65, 0.9), rgba(170, 126, 28, 0.95));
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

    .admin-picker-trigger {
        border: 1px solid #e5d7b1;
        background: linear-gradient(180deg, #ffffff 0%, #fffaf0 100%);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }

    .admin-picker-panel {
        overflow: hidden;
        border: 1px solid #eadfbe;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 20px 44px rgba(15, 23, 42, 0.14);
        backdrop-filter: blur(12px);
    }

    .admin-picker-search {
        border: 1px solid #e5d7b1;
        background: linear-gradient(180deg, #fffdf8 0%, #fffaf0 100%);
    }

    .admin-picker-option {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 12px;
        border-radius: 16px;
        border: 1px solid transparent;
        background: transparent;
        padding: 12px;
        text-align: left;
        transition: all .18s ease;
    }

    .admin-picker-option:hover {
        border-color: #ead39a;
        background: linear-gradient(180deg, #fffaf0 0%, #fff4de 100%);
        transform: translateY(-1px);
    }

    .admin-picker-option-icon {
        display: inline-flex;
        height: 38px;
        width: 38px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #fff1c8;
        color: #9b7a28;
        box-shadow: inset 0 0 0 1px #f1ddaa;
    }

    .admin-picker-option-title {
        color: #1e293b;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.35;
    }

    .admin-picker-option-subtitle {
        margin-top: 2px;
        color: #8c7a52;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .admin-picker-empty {
        padding: 20px 16px;
        color: #94a3b8;
        font-size: 14px;
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
        width: 4px;
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

    .sidebar-menu-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(217, 181, 90, 0.72) transparent;
    }

    .sidebar-menu-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-menu-scroll::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 999px;
    }

    .sidebar-menu-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(217, 181, 90, 0.62), rgba(183, 137, 34, 0.86));
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
