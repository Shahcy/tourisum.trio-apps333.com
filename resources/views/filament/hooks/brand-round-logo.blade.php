<style>
    .fi-topbar .fi-logo,
    .fi-sidebar-header .fi-logo {
        gap: 0.5rem !important;
        align-items: center;
    }

    .fi-topbar .fi-logo img,
    .fi-sidebar-header .fi-logo img {
        width: 2rem !important;
        height: 2rem !important;
        border-radius: 9999px !important;
        object-fit: cover;
    }

    .tenant-brand {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tenant-brand__logo {
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tenant-brand__logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 9999px;
    }

    .tenant-brand__logo span {
        font-weight: 600;
    }

    .tenant-brand__name {
        font-weight: 600;
        font-size: 1rem;
        white-space: nowrap;
    }

    .app-brand {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.5rem;
    }

    .app-brand__logo {
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .app-brand__logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 9999px;
    }

    .app-brand__logo img[src=""] {
        display: none;
    }

    .app-brand__logo:empty {
        display: none;
    }

    .app-brand__name {
        font-weight: 600;
        font-size: 1rem;
        white-space: nowrap;
    }
</style>
