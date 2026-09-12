@extends('layouts.task-manager')

@section('title', 'Settings')

@section('content')

<div class="page settings-page">

    {{-- =========================================================
         HEADER
         ========================================================= --}}
    <div class="page-header">

        <div>

            <span class="eyebrow">
                TASK MANAGEMENT
            </span>

            <h1>
                Settings
            </h1>

            <p class="page-subtitle">
                Kelola pengaturan dan integrasi TaskFlow.
            </p>

        </div>

    </div>


    {{-- =========================================================
         SETTINGS LAYOUT
         ========================================================= --}}
    <div class="settings-layout">

        {{-- =====================================================
             SIDEBAR TABS
             ===================================================== --}}
        <aside class="settings-sidebar">

            <button
                type="button"
                class="settings-tab active"
                data-tab="general"
            >
                <span>General</span>
            </button>


            <button
                type="button"
                class="settings-tab"
                data-tab="notifications"
            >
                <span>Notifications</span>
            </button>


            <button
                type="button"
                class="settings-tab"
                data-tab="clickup"
            >
                <span>ClickUp</span>
            </button>

        </aside>


        {{-- =====================================================
             CONTENT
             ===================================================== --}}
        <main class="settings-content">

            {{-- =================================================
                 GENERAL
                 ================================================= --}}
            <section
                class="settings-panel active"
                data-panel="general"
            >

                <div class="settings-panel-header">

                    <div>

                        <h2>
                            General
                        </h2>

                        <p>
                            Pengaturan umum TaskFlow.
                        </p>

                    </div>

                </div>


                <div class="settings-card">

                    <div class="settings-card-title">
                        Workspace
                    </div>

                    <div class="settings-card-description">
                        Pengaturan workspace akan tersedia di bagian ini.
                    </div>

                    <div class="settings-placeholder">
                        General settings
                    </div>

                </div>

            </section>


            {{-- =================================================
                 NOTIFICATIONS
                 ================================================= --}}
            <section
                class="settings-panel"
                data-panel="notifications"
            >

                <div class="settings-panel-header">

                    <div>

                        <h2>
                            Notifications
                        </h2>

                        <p>
                            Kelola notifikasi TaskFlow.
                        </p>

                    </div>

                </div>


                <div class="settings-card">

                    <div class="settings-card-title">
                        Notification preferences
                    </div>

                    <div class="settings-card-description">
                        Pengaturan notifikasi akan tersedia di bagian ini.
                    </div>

                    <div class="settings-placeholder">
                        Notification settings
                    </div>

                </div>

            </section>


            {{-- =================================================
                 CLICKUP
                 ================================================= --}}
            <section
                class="settings-panel"
                data-panel="clickup"
            >

                <div class="settings-panel-header">

                    <div>

                        <h2>
                            ClickUp Integration
                        </h2>

                        <p>
                            Hubungkan TaskFlow dengan ClickUp untuk
                            mengimpor task ke dalam workspace TaskFlow.
                        </p>

                    </div>

                </div>


                {{-- CONNECTION CARD --}}
                <div class="settings-card clickup-card">

                    <div class="clickup-card-header">

                        <div class="clickup-brand">

                            <div class="clickup-logo">
                                C
                            </div>

                            <div>

                                <div class="settings-card-title">
                                    ClickUp
                                </div>

                                <div class="settings-card-description">
                                    Import tasks from ClickUp
                                </div>

                            </div>

                        </div>


                        @if(session('clickup_connected'))
    <span class="connection-status connected">
        Connected
    </span>
@else
    <span class="connection-status disconnected">
        Not Connected
    </span>
@endif

                    </div>


                    <div class="clickup-divider"></div>


                    <div class="clickup-info">

                        <h3>
                            Connect your ClickUp account
                        </h3>

                        <p>
                            Hubungkan akun ClickUp untuk mengambil
                            Workspace, Space, Folder, List, dan Task
                            yang kemudian dapat diimpor ke TaskFlow.
                        </p>

                    </div>


                    <div class="clickup-actions">

                        <a href="{{ route('settings.clickup.connect') }}" class="btn-connect-clickup">
    Connect ClickUp
</a>

                    </div>

                </div>


                {{-- IMPORT INFO --}}
                <div class="settings-card clickup-import-info">

                    <div class="settings-card-title">
                        Import from ClickUp
                    </div>

                    <div class="settings-card-description">
                        Setelah ClickUp terhubung, Anda dapat memilih
                        data yang ingin diimpor ke TaskFlow.
                    </div>


                    <div class="import-flow">

                        <div class="import-step">

                            <span class="import-step-number">
                                1
                            </span>

                            <div>

                                <strong>
                                    Connect
                                </strong>

                                <span>
                                    Hubungkan akun ClickUp
                                </span>

                            </div>

                        </div>


                        <div class="import-arrow"></div>


                        <div class="import-step">

                            <span class="import-step-number">
                                2
                            </span>

                            <div>

                                <strong>
                                    Select
                                </strong>

                                <span>
                                    Pilih Workspace / Space / List
                                </span>

                            </div>

                        </div>


                        <div class="import-arrow"></div>


                        <div class="import-step">

                            <span class="import-step-number">
                                3
                            </span>

                            <div>

                                <strong>
                                    Import
                                </strong>

                                <span>
                                    Masukkan task ke TaskFlow
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>


<style>

/* =============================================================
   SETTINGS
   ============================================================= */

.settings-page {
    max-width: 1400px;
    margin: 0 auto;
}


/* =============================================================
   HEADER
   ============================================================= */

.settings-page .page-header {
    margin-bottom: 24px;
}

.settings-page .page-header h1 {
    margin: 4px 0 6px;
    font-size: 30px;
    line-height: 1.15;
}

.settings-page .page-subtitle {
    margin: 0;
    color: var(--muted);
    font-size: 14px;
}


/* =============================================================
   LAYOUT
   ============================================================= */

.settings-layout {
    display: grid;
    grid-template-columns: 190px minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}


/* =============================================================
   SIDEBAR
   ============================================================= */

.settings-sidebar {
    padding: 6px;

    display: flex;
    flex-direction: column;
    gap: 3px;

    border: 1px solid var(--line);
    border-radius: 12px;

    background: var(--panel);
}

.settings-tab {
    width: 100%;
    min-height: 40px;

    padding: 0 11px;

    display: flex;
    align-items: center;

    border: 0;
    border-radius: 8px;

    background: transparent;
    color: var(--muted);

    font-size: 13px;
    text-align: left;

    cursor: pointer;

    transition:
        background .15s ease,
        color .15s ease;
}

.settings-tab:hover {
    background: #f5f6f8;
    color: var(--text);
}

.settings-tab.active {
    background: var(--primary-soft);
    color: var(--primary);
    font-weight: 600;
}


/* =============================================================
   CONTENT
   ============================================================= */

.settings-content {
    min-width: 0;
}

.settings-panel {
    display: none;
}

.settings-panel.active {
    display: block;
}

.settings-panel-header {
    margin-bottom: 16px;
}

.settings-panel-header h2 {
    margin: 0 0 5px;

    font-size: 20px;
    line-height: 1.25;
}

.settings-panel-header p {
    margin: 0;

    color: var(--muted);
    font-size: 13px;
}


/* =============================================================
   CARD
   ============================================================= */

.settings-card {
    padding: 20px;

    border: 1px solid var(--line);
    border-radius: 12px;

    background: var(--panel);
}

.settings-card + .settings-card {
    margin-top: 14px;
}

.settings-card-title {
    font-size: 14px;
    font-weight: 600;
}

.settings-card-description {
    margin-top: 5px;

    color: var(--muted);
    font-size: 12px;
    line-height: 1.5;
}

.settings-placeholder {
    margin-top: 18px;
    padding: 18px;

    border: 1px dashed var(--line);
    border-radius: 9px;

    color: var(--muted);

    font-size: 12px;
}


/* =============================================================
   CLICKUP
   ============================================================= */

.clickup-card {
    padding: 22px;
}

.clickup-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.clickup-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.clickup-logo {
    width: 40px;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 10px;

    background: var(--primary-soft);
    color: var(--primary);

    font-size: 17px;
    font-weight: 800;
}

.connection-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 5px 9px;

    border-radius: 7px;

    font-size: 11px;
    font-weight: 600;
}

.connection-status.disconnected {
    background: #f5f6f8;
    color: var(--muted);
}
.connection-status.connected {
    color: #16a34a;
}

.status-dot {
    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: currentColor;
}

.clickup-divider {
    height: 1px;

    margin: 20px 0;

    background: var(--line);
}

.clickup-info h3 {
    margin: 0;

    font-size: 15px;
}

.clickup-info p {
    max-width: 650px;

    margin: 7px 0 0;

    color: var(--muted);

    font-size: 12px;
    line-height: 1.6;
}

.clickup-actions {
    margin-top: 20px;

    display: flex;
    justify-content: flex-end;
}


/* =============================================================
   IMPORT FLOW
   ============================================================= */

.clickup-import-info {
    padding: 18px 20px;
}

.import-flow {
    margin-top: 18px;

    display: flex;
    align-items: center;
    gap: 12px;
}

.import-step {
    min-width: 0;

    display: flex;
    align-items: center;
    gap: 9px;
}

.import-step-number {
    width: 27px;
    height: 27px;

    flex: 0 0 auto;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: var(--primary-soft);
    color: var(--primary);

    font-size: 11px;
    font-weight: 700;
}

.import-step strong {
    display: block;

    font-size: 12px;
}

.import-step span:not(.import-step-number) {
    display: block;

    margin-top: 2px;

    color: var(--muted);

    font-size: 10px;
}

.import-arrow {
    width: 28px;
    height: 1px;

    flex: 0 0 auto;

    background: var(--line);

    position: relative;
}

.import-arrow::after {
    content: "";

    position: absolute;

    right: 0;
    top: -3px;

    width: 6px;
    height: 6px;

    border-top: 1px solid var(--muted);
    border-right: 1px solid var(--muted);

    transform: rotate(45deg);
}


/* =============================================================
   RESPONSIVE
   ============================================================= */

@media (max-width: 800px) {

    .settings-layout {
        grid-template-columns: 1fr;
    }

    .settings-sidebar {
        flex-direction: row;
        overflow-x: auto;
    }

    .settings-tab {
        width: auto;
        min-width: max-content;
    }

    .import-flow {
        align-items: flex-start;
        flex-direction: column;
    }

    .import-arrow {
        display: none;
    }

}


@media (max-width: 600px) {

    .clickup-card-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .clickup-actions {
        justify-content: stretch;
    }

    .clickup-actions .btn {
        width: 100%;
    }

}

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const tabs =
        document.querySelectorAll('.settings-tab');

    const panels =
        document.querySelectorAll('.settings-panel');


    /*
    |--------------------------------------------------------------------------
    | SETTINGS TABS
    |--------------------------------------------------------------------------
    */

    tabs.forEach(function (tab) {

        tab.addEventListener('click', function () {

            const target =
                tab.dataset.tab;

            tabs.forEach(function (item) {

                item.classList.remove('active');

            });

            panels.forEach(function (panel) {

                panel.classList.remove('active');

            });

            tab.classList.add('active');

            const targetPanel =
                document.querySelector(
                    '.settings-panel[data-panel="' + target + '"]'
                );

            if (targetPanel) {

                targetPanel.classList.add('active');

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | CLICKUP CONNECT
    |--------------------------------------------------------------------------
    */

    const connectClickUp =
        document.getElementById('connectClickUp');

    connectClickUp?.addEventListener('click', function () {

        /*
         * UI ONLY
         *
         * OAuth ClickUp akan kita sambungkan
         * pada tahap backend berikutnya.
         */

        alert(
            'ClickUp connection akan kita aktifkan pada tahap berikutnya.'
        );

    });

});

</script>

@endsection