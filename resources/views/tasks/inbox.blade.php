@extends('layouts.task-manager')

@section('title', 'Settings')

@section('content')

<div class="page settings-page">

    <div class="page-header">
        <div>
            <span class="eyebrow">TASK MANAGEMENT</span>

            <h1>Settings</h1>

            <p class="page-subtitle">
                Kelola pengaturan dan integrasi TaskFlow.
            </p>
        </div>
    </div>


    <div class="settings-layout">

        {{-- ==================================================
             SETTINGS NAVIGATION
        =================================================== --}}

        <aside class="settings-sidebar">

            <button
                type="button"
                class="settings-tab active"
                data-tab="general"
            >
                <span class="settings-icon settings-icon-general">
                    <span></span>
                </span>

                <span class="settings-tab-label">
                    General
                </span>
            </button>


            <button
                type="button"
                class="settings-tab"
                data-tab="notifications"
            >
                <span class="settings-icon settings-icon-notification">
                    <span></span>
                </span>

                <span class="settings-tab-label">
                    Notifications
                </span>
            </button>


            <button
                type="button"
                class="settings-tab"
                data-tab="clickup"
            >
                <span class="settings-icon settings-icon-clickup">
                    <span class="clickup-mark"></span>
                </span>

                <span class="settings-tab-label">
                    ClickUp
                </span>
            </button>

        </aside>


        {{-- ==================================================
             SETTINGS CONTENT
        =================================================== --}}

        <main class="settings-content">


            {{-- ==================================================
                 GENERAL
            =================================================== --}}

            <section
                class="settings-panel active"
                data-panel="general"
            >

                <div class="settings-panel-header">

                    <h2>General</h2>

                    <p>
                        Pengaturan umum TaskFlow.
                    </p>

                </div>


                <div class="settings-card">

                    <div class="settings-card-title">
                        Workspace
                    </div>

                    <p class="settings-card-description">
                        Pengaturan workspace akan tersedia di bagian ini.
                    </p>

                    <div class="settings-placeholder">
                        General settings
                    </div>

                </div>

            </section>


            {{-- ==================================================
                 NOTIFICATIONS
            =================================================== --}}

            <section
                class="settings-panel"
                data-panel="notifications"
            >

                <div class="settings-panel-header">

                    <h2>Notifications</h2>

                    <p>
                        Kelola notifikasi TaskFlow.
                    </p>

                </div>


                <div class="settings-card">

                    <div class="settings-card-title">
                        Notification preferences
                    </div>

                    <p class="settings-card-description">
                        Pengaturan notifikasi akan tersedia di bagian ini.
                    </p>

                    <div class="settings-placeholder">
                        Notification settings
                    </div>

                </div>

            </section>


            {{-- ==================================================
                 CLICKUP
            =================================================== --}}

            <section
                class="settings-panel"
                data-panel="clickup"
            >

                <div class="settings-panel-header">

                    <h2>ClickUp Integration</h2>

                    <p>
                        Hubungkan TaskFlow dengan ClickUp untuk
                        mengimpor task ke dalam workspace TaskFlow.
                    </p>

                </div>


                {{-- ==================================================
                     CLICKUP CONNECTION
                =================================================== --}}

                <div class="settings-card clickup-card">

                    <div class="clickup-card-header">

                        <div class="clickup-brand">

                            <div
                                class="clickup-logo"
                                aria-hidden="true"
                            >
                                <span class="clickup-logo-mark"></span>
                            </div>


                            <div class="clickup-brand-text">

                                <div class="settings-card-title">
                                    ClickUp
                                </div>

                                <div class="settings-card-description">
                                    Import tasks from ClickUp
                                </div>

                            </div>

                        </div>


                        <span class="connection-status disconnected">

                            <span class="status-dot"></span>

                            Not Connected

                        </span>

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

                        <button
                            type="button"
                            class="btn clickup-connect-btn"
                            id="connectClickUp"
                        >

                            <span
                                class="connect-icon"
                                aria-hidden="true"
                            >
                                <span></span>
                            </span>

                            Connect ClickUp

                        </button>

                    </div>

                </div>


                {{-- ==================================================
                     IMPORT FLOW
                =================================================== --}}

                <div class="settings-card clickup-import-card">

                    <div class="settings-card-title">
                        Import from ClickUp
                    </div>

                    <p class="settings-card-description">
                        Setelah ClickUp terhubung, Anda dapat memilih
                        data yang ingin diimpor ke TaskFlow.
                    </p>


                    <div class="import-flow">


                        <div class="import-step">

                            <div class="import-step-number">
                                1
                            </div>

                            <div class="import-step-content">

                                <strong>
                                    Connect
                                </strong>

                                <span>
                                    Hubungkan akun ClickUp
                                </span>

                            </div>

                        </div>


                        <div class="import-arrow">
                            <span></span>
                        </div>


                        <div class="import-step">

                            <div class="import-step-number">
                                2
                            </div>

                            <div class="import-step-content">

                                <strong>
                                    Select
                                </strong>

                                <span>
                                    Pilih Workspace / Space / List
                                </span>

                            </div>

                        </div>


                        <div class="import-arrow">
                            <span></span>
                        </div>


                        <div class="import-step">

                            <div class="import-step-number">
                                3
                            </div>

                            <div class="import-step-content">

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

/* =========================================================
   SETTINGS PAGE
========================================================= */

.settings-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
}


.settings-page .page-header {
    margin-bottom: 24px;
}


.settings-page .page-header h1 {
    margin: 4px 0 6px;
    font-size: 30px;
    line-height: 1.15;
    letter-spacing: -0.4px;
}


.settings-page .page-subtitle {
    margin: 0;
    color: var(--muted);
    font-size: 14px;
    line-height: 1.5;
}


/* =========================================================
   SETTINGS LAYOUT
========================================================= */

.settings-layout {
    display: grid;
    grid-template-columns: 190px minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}


/* =========================================================
   SETTINGS SIDEBAR
========================================================= */

.settings-sidebar {
    padding: 6px;

    display: flex;
    flex-direction: column;

    gap: 3px;

    border: 1px solid var(--line);
    border-radius: 12px;

    background: var(--panel);

    box-shadow:
        0 1px 2px rgba(0, 0, 0, 0.02);
}


.settings-tab {
    position: relative;

    width: 100%;
    min-height: 40px;

    padding: 0 11px;

    display: flex;
    align-items: center;

    gap: 10px;

    border: 0;
    border-radius: 8px;

    background: transparent;

    color: var(--muted);

    font-family: inherit;
    font-size: 13px;
    font-weight: 500;

    text-align: left;

    cursor: pointer;

    transition:
        background 0.15s ease,
        color 0.15s ease;
}


.settings-tab:hover {
    background: #f6f7f9;
    color: var(--text);
}


.settings-tab.active {
    background: var(--primary-soft);
    color: var(--primary);
    font-weight: 600;
}


.settings-tab-label {
    line-height: 1;
}


/* =========================================================
   SETTINGS ICONS
   CSS ONLY
   Tidak menggunakan Unicode / Emoji
========================================================= */

.settings-icon {
    position: relative;

    width: 20px;
    height: 20px;

    flex: 0 0 20px;

    display: inline-flex;

    align-items: center;
    justify-content: center;
}


/* ---------------------------------------------------------
   GENERAL
--------------------------------------------------------- */

.settings-icon-general::before {
    content: '';

    width: 12px;
    height: 12px;

    border: 1.7px solid currentColor;

    border-radius: 50%;
}


.settings-icon-general::after {
    content: '';

    position: absolute;

    width: 4px;
    height: 4px;

    border-radius: 50%;

    background: currentColor;
}


/* ---------------------------------------------------------
   NOTIFICATION
--------------------------------------------------------- */

.settings-icon-notification::before {
    content: '';

    position: absolute;

    width: 10px;
    height: 11px;

    top: 3px;

    border: 1.5px solid currentColor;

    border-bottom: 0;

    border-radius:
        7px 7px 2px 2px;
}


.settings-icon-notification::after {
    content: '';

    position: absolute;

    width: 14px;
    height: 2px;

    left: 3px;
    bottom: 3px;

    border-radius: 2px;

    background: currentColor;
}


.settings-icon-notification span {
    position: absolute;

    width: 3px;
    height: 3px;

    bottom: 1px;

    border-radius: 50%;

    background: currentColor;
}


/* ---------------------------------------------------------
   CLICKUP
--------------------------------------------------------- */

.settings-icon-clickup::before,
.settings-icon-clickup::after {
    content: '';

    position: absolute;

    width: 8px;
    height: 2px;

    border-radius: 2px;

    background: currentColor;

    transform: rotate(45deg);
}


.settings-icon-clickup::before {
    top: 6px;
    left: 3px;
}


.settings-icon-clickup::after {
    top: 11px;
    left: 8px;
}


.clickup-mark {
    position: absolute;

    width: 7px;
    height: 7px;

    top: 4px;
    left: 8px;

    border-left: 2px solid currentColor;
    border-bottom: 2px solid currentColor;

    transform: rotate(-45deg);
}


/* =========================================================
   SETTINGS CONTENT
========================================================= */

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

    letter-spacing: -0.2px;
}


.settings-panel-header p {
    max-width: 720px;

    margin: 0;

    color: var(--muted);

    font-size: 13px;
    line-height: 1.5;
}


/* =========================================================
   SETTINGS CARD
========================================================= */

.settings-card {
    padding: 20px;

    border: 1px solid var(--line);
    border-radius: 12px;

    background: var(--panel);

    box-shadow:
        0 1px 2px rgba(0, 0, 0, 0.02);
}


.settings-card + .settings-card {
    margin-top: 14px;
}


.settings-card-title {
    font-size: 14px;
    line-height: 1.3;

    font-weight: 600;
}


.settings-card-description {
    margin: 5px 0 0;

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


/* =========================================================
   CLICKUP CONNECTION CARD
========================================================= */

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

    min-width: 0;
}


.clickup-brand-text {
    min-width: 0;
}


/* =========================================================
   CLICKUP LOGO
   CSS ONLY
========================================================= */

.clickup-logo {
    position: relative;

    width: 42px;
    height: 42px;

    flex: 0 0 42px;

    display: flex;

    align-items: center;
    justify-content: center;

    border: 1px solid var(--line);
    border-radius: 10px;

    background: var(--panel);
}


.clickup-logo-mark {
    position: relative;

    width: 17px;
    height: 17px;

    display: block;

    border: 2px solid var(--primary);

    border-top-color: transparent;
    border-right-color: transparent;

    border-radius: 5px;

    transform: rotate(-45deg);
}


.clickup-logo-mark::before,
.clickup-logo-mark::after {
    content: '';

    position: absolute;

    border-radius: 50%;

    background: var(--primary);
}


.clickup-logo-mark::before {
    width: 5px;
    height: 5px;

    top: -3px;
    left: 0;
}


.clickup-logo-mark::after {
    width: 5px;
    height: 5px;

    right: -3px;
    bottom: 0;
}


/* =========================================================
   CONNECTION STATUS
========================================================= */

.connection-status {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 6px 10px;

    border-radius: 7px;

    font-size: 11px;
    font-weight: 600;

    white-space: nowrap;
}


.connection-status.disconnected {
    background: #f4f5f7;
    color: var(--muted);
}


.status-dot {
    width: 7px;
    height: 7px;

    flex: 0 0 7px;

    border-radius: 50%;

    background: currentColor;
}


/* =========================================================
   DIVIDER
========================================================= */

.clickup-divider {
    height: 1px;

    margin: 20px 0;

    background: var(--line);
}


/* =========================================================
   CLICKUP INFORMATION
========================================================= */

.clickup-info h3 {
    margin: 0;

    font-size: 15px;
    line-height: 1.4;
}


.clickup-info p {
    max-width: 700px;

    margin: 7px 0 0;

    color: var(--muted);

    font-size: 12px;
    line-height: 1.65;
}


/* =========================================================
   CLICKUP ACTION
========================================================= */

.clickup-actions {
    margin-top: 20px;

    display: flex;

    justify-content: flex-end;
}


.clickup-connect-btn {
    min-height: 36px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 8px;

    padding: 0 14px;

    font-size: 12px;
}


/* =========================================================
   CONNECT ICON
   CSS ONLY
========================================================= */

.connect-icon {
    position: relative;

    width: 14px;
    height: 14px;
}


.connect-icon::before {
    content: '';

    position: absolute;

    width: 7px;
    height: 7px;

    top: 1px;
    left: 1px;

    border: 1.5px solid currentColor;

    border-radius: 50%;
}


.connect-icon::after {
    content: '';

    position: absolute;

    width: 7px;
    height: 7px;

    right: 1px;
    bottom: 1px;

    border: 1.5px solid currentColor;

    border-radius: 50%;
}


.connect-icon span {
    position: absolute;

    width: 6px;
    height: 1.5px;

    top: 6px;
    left: 5px;

    background: currentColor;

    transform: rotate(45deg);
}


/* =========================================================
   IMPORT CARD
========================================================= */

.clickup-import-card {
    padding: 18px 20px;
}


.import-flow {
    margin-top: 19px;

    display: flex;

    align-items: center;

    gap: 12px;
}


.import-step {
    min-width: 0;

    display: flex;

    align-items: center;

    gap: 10px;

    flex: 1;
}


.import-step-number {
    width: 28px;
    height: 28px;

    flex: 0 0 28px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: var(--primary-soft);
    color: var(--primary);

    font-size: 11px;
    font-weight: 700;
}


.import-step-content {
    min-width: 0;
}


.import-step-content strong {
    display: block;

    font-size: 12px;
    line-height: 1.3;
}


.import-step-content span {
    display: block;

    margin-top: 3px;

    color: var(--muted);

    font-size: 10px;
    line-height: 1.4;
}


/* =========================================================
   IMPORT ARROW
========================================================= */

.import-arrow {
    width: 24px;

    flex: 0 0 24px;

    display: flex;

    align-items: center;
    justify-content: center;
}


.import-arrow span {
    position: relative;

    width: 16px;
    height: 1px;

    background: var(--line);
}


.import-arrow span::after {
    content: '';

    position: absolute;

    width: 5px;
    height: 5px;

    top: -2px;
    right: 0;

    border-top: 1px solid var(--muted);
    border-right: 1px solid var(--muted);

    transform: rotate(45deg);
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .settings-layout {
        grid-template-columns: 1fr;
        gap: 16px;
    }


    .settings-sidebar {
        flex-direction: row;
        overflow-x: auto;
    }


    .settings-tab {
        width: auto;
        min-width: max-content;
    }

}


@media (max-width: 700px) {

    .clickup-card-header {
        align-items: flex-start;
        flex-direction: column;
    }


    .connection-status {
        align-self: flex-start;
    }


    .import-flow {
        align-items: stretch;
        flex-direction: column;
        gap: 14px;
    }


    .import-arrow {
        display: none;
    }


    .import-step {
        flex: none;
    }

}


@media (max-width: 500px) {

    .settings-page .page-header h1 {
        font-size: 26px;
    }


    .clickup-card {
        padding: 18px;
    }


    .clickup-actions {
        justify-content: stretch;
    }


    .clickup-connect-btn {
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
                    '.settings-panel[data-panel="' +
                    target +
                    '"]'
                );


            if (targetPanel) {

                targetPanel.classList.add('active');

            }

        });

    });


    const connectClickUp =
        document.getElementById('connectClickUp');


    if (connectClickUp) {

        connectClickUp.addEventListener(
            'click',
            function () {

                alert(
                    'Koneksi ClickUp akan kita aktifkan pada tahap berikutnya.'
                );

            }
        );

    }

});

</script>

@endsection