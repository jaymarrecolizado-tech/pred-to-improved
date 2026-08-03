<style>
    .fi-simple-layout > header,
    .fi-simple-header,
    .fi-logo,
    .fi-simple-layout > .fi-simple-header {
        display: none !important;
    }

    .fi-simple-layout {
        background: transparent !important;
        padding: 0 !important;
        min-height: 100vh;
    }

    .fi-simple-main {
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
        width: auto !important;
        max-width: none !important;
    }

    body { overflow: hidden !important; }

    .dict-login-wrapper {
        position: fixed;
        inset: 0;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
        background:
            radial-gradient(1200px 600px at 10% -10%, rgba(30, 78, 140, 0.12), transparent 55%),
            linear-gradient(180deg, #f4f6f9 0%, #e8edf4 100%);
    }

    .dark .dict-login-wrapper,
    html.dark .dict-login-wrapper {
        background:
            radial-gradient(1000px 500px at 0% 0%, rgba(30, 78, 140, 0.25), transparent 50%),
            linear-gradient(180deg, #0b1220 0%, #111a2b 100%);
    }

    .dict-login-card {
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        width: 100%;
        max-width: 920px;
        min-height: 520px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid rgba(11, 31, 58, 0.12);
        box-shadow: 0 18px 48px rgba(11, 31, 58, 0.12);
        background: #fff;
    }

    .dark .dict-login-card,
    html.dark .dict-login-card {
        background: #111a2b;
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 18px 48px rgba(0, 0, 0, 0.45);
    }

    .dict-brand {
        background: linear-gradient(160deg, #0b1f3a 0%, #143156 55%, #1e4e8c 100%);
        padding: 40px 36px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        color: #fff;
    }

    .dict-logo-wrap img {
        width: 100%;
        max-width: 280px;
        height: auto;
        object-fit: contain;
    }

    .dict-office-label {
        margin-top: 14px;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.9);
    }

    .dict-region-sub {
        margin-top: 4px;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.65);
    }

    .dict-headline {
        margin-top: 36px;
        font-size: 32px;
        font-weight: 700;
        line-height: 1.15;
        letter-spacing: -0.02em;
    }

    .dict-desc {
        margin-top: 12px;
        font-size: 14px;
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.72);
        max-width: 280px;
    }

    .dict-brand-footer {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.55);
    }

    .dict-form-panel {
        padding: 44px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #fff;
    }

    .dark .dict-form-panel,
    html.dark .dict-form-panel {
        background: #111a2b;
    }

    .dict-eyebrow {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #1e4e8c;
        margin-bottom: 8px;
    }

    .dark .dict-eyebrow,
    html.dark .dict-eyebrow {
        color: #8fb4e8;
    }

    .dict-form-title {
        font-size: 26px;
        font-weight: 700;
        color: #0b1f3a;
        letter-spacing: -0.02em;
    }

    .dark .dict-form-title,
    html.dark .dict-form-title {
        color: #f3f6fb;
    }

    .dict-form-sub {
        margin-top: 6px;
        font-size: 13px;
        color: #64748b;
    }

    .dark .dict-form-sub,
    html.dark .dict-form-sub {
        color: #94a3b8;
    }

    .dict-divider {
        width: 36px;
        height: 3px;
        background: #1e4e8c;
        border-radius: 2px;
        margin: 16px 0 24px;
    }

    .dict-form-panel .fi-fo-field-wrp {
        margin-bottom: 14px;
    }

    .dict-form-panel input[type="email"],
    .dict-form-panel input[type="password"],
    .dict-form-panel input[type="text"],
    .dict-form-panel .fi-input {
        border-radius: 6px !important;
        font-size: 14px !important;
    }

    .dict-form-panel button[type="submit"],
    .dict-form-panel .fi-btn-primary {
        width: 100% !important;
        border-radius: 6px !important;
        background: #0b1f3a !important;
        font-weight: 600 !important;
        letter-spacing: 0.02em !important;
        padding: 0.75rem !important;
        box-shadow: none !important;
        transition: background-color 140ms ease !important;
    }

    .dict-form-panel button[type="submit"]:hover,
    .dict-form-panel .fi-btn-primary:hover {
        background: #143156 !important;
    }

    @media (max-width: 720px) {
        .dict-login-card {
            grid-template-columns: 1fr;
            min-height: auto;
        }
        .dict-brand {
            display: none;
        }
        .dict-form-panel {
            padding: 32px 24px;
        }
    }
</style>

<div class="dict-login-wrapper">
<div class="dict-login-card">

    <div class="dict-brand">
        <div>
            <div class="dict-logo-wrap">
                <img src="{{ asset('images/DICT_logo3.png') }}" alt="DICT Logo">
            </div>
            <div class="dict-office-label">DICT Regional Office II</div>
            <div class="dict-region-sub">Department of Information and Communications Technology</div>
        </div>

        <div>
            <div class="dict-headline">Travel Order System</div>
            <p class="dict-desc">Official travel authorization and multi-step approval for DICT Region 2 personnel.</p>
        </div>

        <div class="dict-brand-footer">Region 2 · Restricted to @dict.gov.ph accounts</div>
    </div>

    <div class="dict-form-panel">
        <div class="dict-eyebrow">Sign in</div>
        <div class="dict-form-title">Welcome</div>
        <div class="dict-form-sub">Use your DICT government email and password.</div>
        <div class="dict-divider"></div>
