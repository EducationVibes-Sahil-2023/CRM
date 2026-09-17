<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- DOMPurify sanitises stored HTML before it is rendered.
     TinyMCE is already loaded by Perfex (assets/plugins/tinymce/tinymce.min.js),
     so this module reuses it instead of loading another editor. -->
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>
<?php init_head(); ?>
  <style>
   :root {
      --font: 'Inter', system-ui, sans-serif;
      --bg-page:        #F5F5F3;
      --bg-primary:     #FFFFFF;
      --bg-secondary:   #F8F8F7;
      --bg-tertiary:    #EFEFED;
      --text-primary:   #1A1A1A;
      --text-secondary: #6B6B6B;
      --text-muted:     #9B9B9B;
      --border-light:   rgba(0,0,0,0.08);
      --border-medium:  rgba(0,0,0,0.13);
      --navy:           #1A2B3C;
      --navy-hover:     #14202E;
      --radius-sm:  6px;
      --radius-md:  8px;
      --radius-lg:  12px;
      --tag-neutral-bg:  #EDF2F7; --tag-neutral-text: #4A5568;
      --tag-urgent-bg:   #FDF2F2; --tag-urgent-text:  #9B4B4B;
      --tag-general-bg:  #EDF7F2; --tag-general-text: #2F6B4A;
      --tag-unread-bg:   #EEF2F7; --tag-unread-text:  #3A567A;
      --tag-edited-bg:   #F5F0FA; --tag-edited-text:  #6B4A8A;
      /* CKEditor theme overrides */
      --ck-border-radius: 6px;
      --ck-color-focus-border: #1A2B3C;
      --ck-color-button-on-background: #EEF2F7;
      --ck-color-button-on-color: #1A2B3C;
    }
  #portal{
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    .portal {
      width: 100%; max-width: 1180px; margin: 0 auto;
      border: 0.5px solid var(--border-light);
      border-radius: var(--radius-lg);
      overflow: hidden; background: var(--bg-primary);
      box-shadow: 0 1px 4px rgba(0,0,0,0.06), 0 4px 24px rgba(0,0,0,0.04);
      position: relative; display: flex; flex-direction: column;
      min-height: 680px; font-family: var(--font); color: var(--text-primary);
    }
    .topbar {
      background: var(--navy) !important; padding: 14px 22px;
      display: flex; align-items: center; justify-content: space-between; gap: 16px;
    }
    .topbar-left { display: flex; align-items: center; gap: 12px; }
    .topbar-logo {
      width: 34px; height: 34px; border-radius: var(--radius-sm);
      background: rgba(255,255,255,0.12);
      display: flex; align-items: center; justify-content: center;
      font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.85);
      letter-spacing: 0.4px; flex-shrink: 0;
    }
    .topbar-title { font-size: 15px; font-weight: 500; color: #fff; }
    .topbar-right { display: flex; align-items: center; gap: 10px; }
    .user-pill {
      display: flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,0.07);
      border: 0.5px solid rgba(255,255,255,0.13);
      border-radius: var(--radius-sm); padding: 5px 10px 5px 6px; cursor: default;
    }
    .user-avatar {
      width: 22px; height: 22px; border-radius: 50%;
      background: rgba(255,255,255,0.18);
      display: flex; align-items: center; justify-content: center;
      font-size: 10px; font-weight: 600; color: #fff; flex-shrink: 0;
    }
    .search-box {
      display: flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,0.07);
      border: 0.5px solid rgba(255,255,255,0.13);
      border-radius: var(--radius-sm); padding: 7px 12px;
    }
    .search-box i     { color: rgba(255,255,255,0.32); font-size: 15px; }
    .search-box input {
      background: none; border: none; outline: none;
      color: #fff; font-size: 13px; font-family: var(--font); width: 170px;
    }
    .search-box input::placeholder { color: rgba(255,255,255,0.3); }
    .btn-post {
      background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.85);
      border: 0.5px solid rgba(255,255,255,0.18); border-radius: var(--radius-sm);
      padding: 7px 15px; font-size: 13px; font-weight: 500; font-family: var(--font);
      cursor: pointer; display: flex; align-items: center; gap: 6px;
      transition: background 0.15s; white-space: nowrap;
    }
    .btn-post:hover { background: rgba(255,255,255,0.16); }
    .stat-bar { display: grid; grid-template-columns: repeat(3, 1fr); border-bottom: 0.5px solid var(--border-light); }
    .stat-item { padding: 14px 24px; border-right: 0.5px solid var(--border-light); }
    .stat-item:last-child { border-right: none; }
    .stat-label { font-size: 10px; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.7px; }
    .stat-val  { font-size: 26px; font-weight: 500; color: var(--text-primary); margin-top: 4px; }
    .stat-sub  { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
    .content { display: flex; flex: 1; }
    .sidebar { width: 205px; border-right: 0.5px solid var(--border-light); padding: 18px 0; background: var(--bg-secondary); flex-shrink: 0; }
    .sidebar-section { margin-bottom: 24px; }
    .sidebar-label { font-size: 10px; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.9px; padding: 0 16px; margin-bottom: 6px; }
    .sidebar-item {
      display: flex; align-items: center; justify-content: space-between;
      padding: 7px 16px; cursor: pointer; font-size: 13px; color: var(--text-secondary);
      border-left: 2px solid transparent; transition: all 0.12s; user-select: none;
    }
    .btn-spin { display:inline-block; width:13px; height:13px; border:2px solid currentColor; border-right-color:transparent; border-radius:50%; animation:spin 0.6s linear infinite; vertical-align:-2px; }
    .sidebar-item span:first-child { display: flex; align-items: center; }
    .sidebar-item:hover { color: var(--text-primary); }
    .sidebar-item.active { color: var(--text-primary); border-left-color: var(--navy); background: var(--bg-primary); font-weight: 500; }
    .sidebar-item i { font-size: 15px; margin-right: 8px; opacity: 0.55; }
    .sidebar-item.active i { opacity: 1; }
    .cnt { font-size: 11px; color: var(--text-muted); background: var(--bg-tertiary); border-radius: 10px; padding: 1px 7px; }
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .filter-bar { padding: 11px 20px; border-bottom: 0.5px solid var(--border-light); display: flex; align-items: center; gap: 7px; flex-wrap: wrap; }
    .filter-label { font-size: 10px; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.6px; }
    .chip { border: 0.5px solid var(--border-light); border-radius: 20px; padding: 4px 13px; font-size: 12px; color: var(--text-secondary); cursor: pointer; background: none; font-family: var(--font); transition: all 0.12s; }
    .chip:hover { color: var(--text-primary); border-color: var(--border-medium); }
    .chip.active { background: var(--navy); color: #fff; border-color: var(--navy); }
    .res-count { font-size: 12px; color: var(--text-muted); }
    .sort-sel { margin-left: auto; border: 0.5px solid var(--border-light); border-radius: var(--radius-sm); padding: 5px 10px; font-size: 12px; color: var(--text-secondary); background: var(--bg-primary); cursor: pointer; outline: none; font-family: var(--font); }
    .list { height: 500px; overflow: auto; padding: 14px 18px; display: flex; flex-direction: column; gap: 8px; }
    .list::-webkit-scrollbar { width: 4px; }
    .list::-webkit-scrollbar-thumb { background: var(--border-light); border-radius: 4px; }
    .card { border: 0.5px solid var(--border-light); border-radius: var(--radius-lg); background: var(--bg-primary); padding: 14px 16px; cursor: pointer; transition: border-color 0.12s, background 0.12s, box-shadow 0.12s; position: relative; }
    .card:hover { border-color: var(--border-medium); box-shadow: 0 1px 6px rgba(0,0,0,0.04); }
    .card.unread { background: var(--bg-secondary); }
    .card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
    .card-title { font-size: 13px; font-weight: 500; color: var(--text-primary); line-height: 1.45; flex: 1; }
    .card-date-block { text-align: right; flex-shrink: 0; }
    .card-date { font-size: 11px; color: var(--text-muted); white-space: nowrap; }
    .card-date-label { font-size: 10px; color: var(--text-muted); opacity: 0.7; display: block; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.4px; }
    .tags { display: flex; gap: 5px; margin-top: 8px; flex-wrap: wrap; }
    .tag { border-radius: 4px; padding: 2px 8px; font-size: 11px; font-weight: 400; display: inline-flex; align-items: center; gap: 3px; background: var(--tag-neutral-bg); color: var(--tag-neutral-text); }
    .t-urgent  { background: var(--tag-urgent-bg);  color: var(--tag-urgent-text); }
    .t-general { background: var(--tag-general-bg); color: var(--tag-general-text); }
    .t-unread  { background: var(--tag-unread-bg);  color: var(--tag-unread-text); }
    .t-edited  { background: var(--tag-edited-bg);  color: var(--tag-edited-text); }
    .t-sub     { background: #EAF1FB; color: #355E8E; }
    .card-preview { font-size: 12px; color: var(--text-secondary); line-height: 1.55; margin-top: 7px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .card-meta { display: flex; align-items: center; gap: 14px; margin-top: 10px; flex-wrap: wrap; }
    .meta-item { display: flex; align-items: center; gap: 4px; font-size: 11px; color: var(--text-muted); }
    .meta-item i { font-size: 12px; }
    .meta-read  { color: #2F6B4A; }
    .empty { padding: 52px 20px; text-align: center; color: var(--text-muted); font-size: 13px; }
    .empty i { font-size: 30px; display: block; margin-bottom: 10px; opacity: 0.35; }
    .spinner { display: inline-block; width: 22px; height: 22px; border: 2px solid var(--border-medium); border-top-color: var(--navy); border-radius: 50%; animation: spin 0.7s linear infinite; margin-bottom: 8px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .overlay { display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,22,30,0.22); z-index: 10; align-items: flex-start; justify-content: flex-end; }
    .overlay.open { display: flex; }
    .panel { background: var(--bg-primary); width: 60%; max-width: 100%; height: 100%; overflow-y: auto; border-left: 0.5px solid var(--border-light); display: flex; flex-direction: column; animation: slideIn 0.18s ease-out; }
    @keyframes slideIn { from { transform: translateX(24px); opacity: 0.6; } to { transform: translateX(0); opacity: 1; } }
    .panel::-webkit-scrollbar { width: 4px; }
    .panel::-webkit-scrollbar-thumb { background: var(--border-light); border-radius: 4px; }
    .panel-hd { padding: 16px 20px; border-bottom: 0.5px solid var(--border-light); display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; background: var(--bg-secondary); position: sticky; top: 0; z-index: 1; }
    .panel-hd-title { font-size: 14px; font-weight: 600; color: var(--text-primary); line-height: 1.45; flex: 1; }
    .panel-hd-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .icon-btn { background: none; border: 0.5px solid var(--border-medium); border-radius: var(--radius-sm); padding: 5px 9px; cursor: pointer; color: var(--text-secondary); font-size: 14px; display: flex; align-items: center; gap: 4px; font-family: var(--font); transition: background 0.12s; }
    .icon-btn:hover { background: var(--bg-tertiary); }
    .panel-body { padding: 20px; flex: 1; }
    .sec-lbl { font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.7px; color: var(--text-muted); margin-bottom: 8px; }
    .body-text { font-size: 13px; color: var(--text-primary); line-height: 1.75; margin-bottom: 22px; white-space: pre-wrap; word-break: break-word; }
    .body-text img { max-width: 100%; height: auto; border-radius: var(--radius-sm); }
    .body-text table { border-collapse: collapse; margin: 8px 0; }
    .body-text table td, .body-text table th { border: 1px solid var(--border-medium); padding: 6px 9px; }
    .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .meta-card { background: var(--bg-secondary); border: 0.5px solid var(--border-light); border-radius: var(--radius-md); padding: 10px 12px; }
    .mc-label { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .mc-val   { font-size: 13px; font-weight: 500; color: var(--text-primary); }
    .mc-val.acknowledged { color: #2F6B4A; }
    .edit-notice { display: flex; align-items: center; gap: 8px; background: var(--tag-edited-bg); border: 0.5px solid rgba(107,74,138,0.15); border-radius: var(--radius-md); padding: 9px 12px; margin-bottom: 18px; font-size: 12px; color: var(--tag-edited-text); }
    .edit-notice i { font-size: 14px; flex-shrink: 0; }
    .panel-foot { padding: 14px 20px; border-top: 0.5px solid var(--border-light); display: flex; gap: 8px; position: sticky; bottom: 0; background: var(--bg-primary); }
    .foot-btn { flex: 1; padding: 9px; border-radius: var(--radius-sm); font-size: 13px; font-family: var(--font); cursor: pointer; font-weight: 400; border: 0.5px solid var(--border-medium); background: none; color: var(--text-secondary); display: flex; align-items: center; justify-content: center; gap: 6px; transition: background 0.12s; }
    .foot-btn:hover { background: var(--bg-secondary); }
    .foot-btn.primary { background: var(--navy); color: #fff; border-color: var(--navy); font-weight: 500; }
    .foot-btn.primary:hover { background: var(--navy-hover); }
    .foot-btn.danger { color: var(--tag-urgent-text); border-color: rgba(155,75,75,0.3); }
    .foot-btn.danger:hover { background: var(--tag-urgent-bg); }
    .form-stack { display: flex; flex-direction: column; gap: 14px; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-group label { font-size: 10px; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.6px; }
    .form-group input, .form-group select, .form-group textarea { border: 0.5px solid var(--border-medium); border-radius: var(--radius-sm); padding: 8px 12px; font-size: 13px; font-family: var(--font); color: var(--text-primary); background: var(--bg-primary); outline: none; transition: border-color 0.12s; width: 100%; }
    .form-group input[type=file] { padding: 7px 10px; font-size: 12px; cursor: pointer; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--navy); }
    .form-group textarea { min-height: 120px; resize: vertical; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
    .att-hint { font-size: 11px; color: var(--text-muted); }
    .edit-mode-banner { display: flex; align-items: center; gap: 8px; background: rgba(26,43,60,0.04); border: 0.5px solid rgba(26,43,60,0.12); border-radius: var(--radius-md); padding: 9px 12px; font-size: 12px; color: var(--text-secondary); }
    .edit-mode-banner i { font-size: 14px; color: var(--navy); flex-shrink: 0; }
    /* ─── Attachments ──────────────────────────────────────────── */
    .att-images { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    .att-thumb { width: 76px; height: 76px; border-radius: var(--radius-md); overflow: hidden; border: 0.5px solid var(--border-light); display: block; }
    .att-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.15s; }
    .att-thumb:hover img { transform: scale(1.05); }
    .att-files { display: flex; flex-direction: column; gap: 6px; margin-bottom: 22px; }
    .att-file { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border: 0.5px solid var(--border-light); border-radius: var(--radius-md); background: var(--bg-secondary); text-decoration: none; color: var(--text-primary); font-size: 12px; transition: background 0.12s; }
    .att-file:hover { background: var(--bg-tertiary); }
    .att-file > i:first-child { font-size: 19px; }
    .att-file .ti-file-type-pdf { color: #C0392B; }
    .att-file .ti-file-type-doc { color: #2B579A; }
    .att-file .ti-file-type-xls { color: #217346; }
    .att-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .att-size { font-size: 11px; color: var(--text-muted); }
    .att-dl { font-size: 14px; color: var(--text-muted); }
    .att-edit-item { display: flex; align-items: center; gap: 8px; padding: 6px 10px; border: 0.5px solid var(--border-light); border-radius: var(--radius-md); font-size: 12px; background: var(--bg-secondary); margin-bottom: 5px; }
    .att-edit-item i:first-child { font-size: 16px; color: var(--navy); }
    .att-edit-item .att-name { flex: 1; }
    .att-remove { cursor: pointer; color: var(--tag-urgent-text); border: none; background: none; font-size: 15px; display: flex; }
    .toast-wrap { position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%); z-index: 50; display: flex; flex-direction: column; gap: 8px; align-items: center; pointer-events: none; }
    .toast { display: flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: var(--radius-md); font-size: 13px; font-weight: 500; box-shadow: 0 4px 16px rgba(0,0,0,0.12); animation: toastIn 0.2s ease-out; }
    .toast i { font-size: 15px; }
    .toast.success { background: #EDF7F2; color: #2F6B4A; border: 0.5px solid rgba(47,107,74,0.2); }
    .toast.error   { background: #FDF2F2; color: #9B4B4B; border: 0.5px solid rgba(155,75,75,0.2); }
    @keyframes toastIn { from { transform: translateY(8px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @media (max-width: 768px) {
      .portal { border-radius: 0; min-height: 100vh; }
      .sidebar { display: none; }
      .panel { width: 100%; }
      .stat-item { padding: 12px 14px; }
      .stat-val { font-size: 20px; }
      .search-box input { width: 110px; }
      .form-row.cols-3 { grid-template-columns: 1fr; }
    }
  }
  /* ─── CKEditor 5 styling (kept outside #portal so balloon panels theme too) ─── */
  .ck.ck-editor { width: 100%; }
  .ck.ck-toolbar.ck-toolbar_grouping { border-color: var(--border-medium); border-top-left-radius: 6px; border-top-right-radius: 6px; background: var(--bg-secondary); }
  .ck.ck-editor__main > .ck-editor__editable { border-color: var(--border-medium); border-bottom-left-radius: 6px; border-bottom-right-radius: 6px; }
  .ck-editor__editable_inline { min-height: 260px; max-height: 520px; font-family: var(--font); font-size: 13px; line-height: 1.6; }
  .ck-editor__editable_inline:focus { border-color: var(--navy) !important; box-shadow: 0 0 0 2px rgba(26,43,60,0.08) !important; }
  </style>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="portal" id="portal">
                            <div class="topbar">
                                <div class="topbar-left">
                                    <div class="topbar-logo">EV</div>
                                    <div class="topbar-title">Announcement Portal</div>
                                </div>
                                <div class="topbar-right">
                                    <div class="search-box">
                                        <i class="ti ti-search"></i>
                                        <input type="text" id="searchInput" placeholder="Search announcements..." />
                                    </div>
                                    <?php if(is_admin() || has_permission('announcements', '', 'create') ) { ?>
                                    <button class="btn-post" onclick="openPostModal()">
                                        <i class="ti ti-plus"></i> Announcement
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="stat-bar">
                                <div class="stat-item">
                                    <div class="stat-label">Total</div>
                                    <div class="stat-val" id="statTotal">0</div>
                                    <div class="stat-sub">All Time</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Unread</div>
                                    <div class="stat-val" id="statUnread">0</div>
                                    <div class="stat-sub">Pending Review</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Avg Views</div>
                                    <div class="stat-val" id="statAvg">0</div>
                                    <div class="stat-sub">Per announcement</div>
                                </div>
                            </div>
                            <div class="content">
                                <div class="sidebar">
                                    <div class="sidebar-section">
                                        <div class="sidebar-label">Department</div>
                                        <div id="sideDept"></div>
                                    </div>
                                    <div class="sidebar-section">
                                        <div class="sidebar-label">Sub Department</div>
                                        <div id="sideSubDept"></div>
                                    </div>
                                    <div class="sidebar-section">
                                        <div class="sidebar-label">Priority</div>
                                        <div id="sidePriority"></div>
                                    </div>
                                </div>
                                <div class="main">
                                    <div class="filter-bar">
                                        <span class="filter-label">Status</span>
                                        <button class="chip active" data-read="all">All</button>
                                        <button class="chip" data-read="unread">Unread</button>
                                        <button class="chip" data-read="read">Read</button>
                                        <span class="res-count" id="resCount"></span>
                                        <select class="sort-sel" id="sortSel">
                                            <option value="date">Newest first</option>
                                            <option value="views">Most viewed</option>
                                            <option value="alpha">A &ndash; Z</option>
                                        </select>
                                    </div>
                                    <div class="list" id="announcementList">
                                        <div class="empty"><span class="spinner"></span><br>Loading&hellip;</div>
                                    </div>
                                </div>
                            </div>
                            <div class="overlay" id="modalOverlay" onclick="closeModal(event)">
                                <div class="panel" onclick="event.stopPropagation()">
                                    <div class="panel-hd" id="modalHeader"></div>
                                    <div class="panel-body" id="modalBody"></div>
                                    <div class="panel-foot" id="modalFooter"></div>
                                </div>
                            </div>
                            <div class="toast-wrap" id="toastWrap"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>


// ============================================================
// CONFIGURATION
// ============================================================
const API_BASE     = '<?php echo admin_url(); ?>announcements/';
const CURRENT_USER = '<?php echo get_staff_user_id(); ?>';
const ALLOWED_EXT  = ['pdf','doc','docx','xls','xlsx','csv','png','jpg','jpeg','gif','webp'];
let department = <?php echo json_encode(isset($departments) ? $departments : []); ?>;
let priority   = <?php echo json_encode(isset($priority)    ? $priority    : []); ?>;
// Sub Department options ({id, name} so buildOptions / labelFor / sidebar all work).
// Pull from PHP $sub_department if provided, otherwise fall back to the defaults below.
let sub_department = <?php echo json_encode(isset($sub_department) ? $sub_department : []); ?>;
if (!Array.isArray(sub_department) || sub_department.length === 0) {
    sub_department = [
         { id: 'all',  name: 'All' },
        { id: 'counsellor',  name: 'Counsellor' },
        { id: 'post_sales', name: 'Post Sales' },
        { id: 'team',       name: 'Team' }
    ];
}
let announcements = [];
let filters = { department: 'all', sub_department: 'all', priority: 'all', read_status: 'all', search: '', sort_by: 'date' };
// ============================================================
// API CALLS
// ============================================================
function btnLoading(btn, isLoading, loadingText) {
    if (!btn) return;
    if (isLoading) {
        if (btn.dataset.orig == null) btn.dataset.orig = btn.innerHTML;
        btn.disabled = true;
        btn.style.opacity = '0.75';
        btn.style.pointerEvents = 'none';
        btn.innerHTML = `<span class="btn-spin"></span> ${loadingText || 'Working…'}`;
    } else {
        btn.disabled = false;
        btn.style.opacity = '';
        btn.style.pointerEvents = '';
        if (btn.dataset.orig != null) { btn.innerHTML = btn.dataset.orig; delete btn.dataset.orig; }
    }
}
async function apiCall(endpoint, method = 'GET', data = {}) {
    const options = { method, headers: { 'X-Requested-With': 'XMLHttpRequest' } };
    let url = API_BASE + endpoint;
    const formData = new FormData();
    if (data && typeof data === 'object') {
        Object.keys(data).forEach(key => {
            if (Array.isArray(data[key]) || data[key] instanceof FileList) {
                Array.from(data[key]).forEach(value => formData.append(`${key}[]`, value));
            } else if (typeof data[key] === 'object' && data[key] !== null && !(data[key] instanceof File)) {
                formData.append(key, JSON.stringify(data[key]));
            } else {
                formData.append(key, data[key]);
            }
        });
    }
    formData.append(csrfData.token_name, csrfData.hash);
    if (method.toUpperCase() === 'GET') {
        const params = new URLSearchParams();
        for (let [key, value] of formData.entries()) params.append(key, value);
        url += '?' + params.toString();
    } else {
        options.body = formData;
    }
    try {
        const response = await fetch(url, options);
        const result = await response.json();
        if (result.csrfHash) csrfData.hash = result.csrfHash;
        return result;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Network error' };
    }
}
async function loadAnnouncements() {
    const params = { department: filters.department, sub_department: filters.sub_department, priority: filters.priority, read_status: filters.read_status, search: filters.search, sort_by: filters.sort_by };
    const result = await apiCall('get_announcements', 'post', params);
    if (result.success) { announcements = result.data || []; renderAnnouncements(); }
    else { showError('Failed to load announcements'); }
}
async function loadStats() {
    const stats = await apiCall('get_stats', 'POST');
    if (stats) {
        document.getElementById('statTotal').textContent  = stats.total || 0;
        document.getElementById('statUnread').textContent = stats.unread || 0;
        document.getElementById('statAvg').textContent    = stats.avg_views || 0;
        const allEl = document.querySelector('[data-count="department-all"]');
        if (allEl) allEl.textContent = stats.total || 0;
        if (stats.by_department) {
            Object.keys(stats.by_department).forEach(key => {
                const el = document.querySelector(`[data-count="department-${key}"]`);
                if (el) el.textContent = stats.by_department[key] || 0;
            });
        }
    }
}
async function markAsRead(announcementId) {
    const result = await apiCall(`mark_as_read/${announcementId}`, 'POST');
    if (result.success) { await loadStats(); await loadAnnouncements(); }
}
// Increment the view counter (server-side). Called when a record is opened.
async function incrementView(id) {
    await apiCall(`increment_view/${id}`, 'POST');
}
async function createAnnouncement(data) {
    const result = await apiCall('create_announcement', 'POST', data);
    if (result.success) { await loadStats(); await loadAnnouncements(); closeModal(); showSuccess('Announcement published successfully'); }
    else { showError(result.message || 'Failed to create announcement'); }
    return result;
}
async function updateAnnouncement(id, data) {
    const result = await apiCall(`update_announcement/${id}`, 'POST', data);
    if (result.success) { await loadStats(); await loadAnnouncements(); closeModal(); showSuccess('Announcement updated successfully'); }
    else { showError(result.message || 'Failed to update announcement'); }
    return result;
}
async function getAnnouncement(id) { return await apiCall('get_announcements', 'post', { id: id }); }
async function deleteAnnouncement(id, btn) {
    if (!confirm('Are you sure you want to delete this announcement?')) return;
    btnLoading(btn, true, 'Deleting…');
    const result = await apiCall(`delete_announcement/${id}`, 'DELETE');
    if (result.success) { await loadStats(); await loadAnnouncements(); closeModal(); showSuccess('Announcement deleted successfully'); }
    else { btnLoading(btn, false); showError(result.message || 'Failed to delete announcement'); }
}
async function removeAttachment(attId, btn) {
    if (!confirm('Remove this attachment?')) return;
    const res = await apiCall(`delete_attachment/${attId}`, 'DELETE');
    if (res.success) { btn.closest('.att-edit-item')?.remove(); showSuccess('Attachment removed'); }
    else showError(res.message || 'Failed to remove attachment');
}
// ============================================================
// DYNAMIC BUILDERS
// ============================================================
function buildOptions(items, selectedValue = '', placeholder = 'Select') {
    let html = `<option value="">${escapeHtml(placeholder)}</option>`;
    (items || []).forEach(item => {
        const sel = String(item.id) === String(selectedValue) ? 'selected' : '';
        html += `<option value="${escapeHtml(String(item.id))}" ${sel}>${escapeHtml(item.name)}</option>`;
    });
    return html;
}
function labelFor(items, id) {
    const found = (items || []).find(item => String(item.id) === String(id));
    return found ? found.name : (id || '');
}
function priorityTagClass(label) {
    const l = (label || '').toLowerCase();
    if (l === 'urgent')  return 't-urgent';
    if (l === 'general') return 't-general';
    return '';
}
function buildSidebarGroup(filterKey, items, allLabel, allIcon, withCount) {
    let html = `
        <div class="sidebar-item active" onclick="d_change(this,'all')" data-filter="${filterKey}" data-value="all">
            <span><i class="ti ${allIcon}"></i>${escapeHtml(allLabel)}</span>
            ${withCount ? `<span class="cnt" data-count="${filterKey}-all">0</span>` : ''}
        </div>`;
    (items || []).forEach(item => {
        html += `
        <div class="sidebar-item" onclick="d_change(this,'${escapeHtml(String(item.id))}')" data-filter="${filterKey}" data-value="${escapeHtml(String(item.id))}">
            <span><i class="ti ti-point"></i>${escapeHtml(item.name)}</span>
            ${withCount ? `<span class="cnt" data-count="${filterKey}-${escapeHtml(String(item.id))}">0</span>` : ''}
        </div>`;
    });
    return html;
}
function d_change(obj, value = '') {
    const filterType = obj.dataset.filter;
    document.querySelectorAll(`[data-filter="${filterType}"]`).forEach(i => i.classList.remove('active'));
    obj.classList.add('active');
    filters[filterType] = value;
    loadAnnouncements();
}
function renderSidebar() {
    document.getElementById('sideDept').innerHTML     = buildSidebarGroup('department', department, 'All', 'ti-layout-grid', true);
    document.getElementById('sideSubDept').innerHTML  = buildSidebarGroup('sub_department', sub_department, 'All', 'ti-sitemap', false);
    document.getElementById('sidePriority').innerHTML = buildSidebarGroup('priority', priority, 'All', 'ti-list', false);
}
// ============================================================
// ATTACHMENT HELPERS
// ============================================================
function fileExt(name) { return (name || '').split('.').pop().toLowerCase(); }
function isImageFile(name) { return ['png','jpg','jpeg','gif','webp'].includes(fileExt(name)); }
function fileIcon(name) {
    const ext = fileExt(name);
    if (ext === 'pdf') return 'ti-file-type-pdf';
    if (['doc','docx'].includes(ext)) return 'ti-file-type-doc';
    if (['xls','xlsx','csv'].includes(ext)) return 'ti-file-type-xls';
    if (isImageFile(name)) return 'ti-photo';
    return 'ti-file';
}
function formatSize(bytes) {
    bytes = Number(bytes) || 0;
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(0) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}
// Detail-view attachments: image thumbnails + file rows
function renderAttachments(atts) {
    if (!atts || !atts.length) return '';
    const images = atts.filter(a => isImageFile(a.name));
    const files  = atts.filter(a => !isImageFile(a.name));
    let html = `<div class="sec-lbl" style="margin-top:4px;">Attachments (${atts.length})</div>`;
    if (images.length) {
        html += `<div class="att-images">` + images.map(a =>
            `<a href="${a.url}" target="_blank" class="att-thumb" title="${escapeHtml(a.name)}"><img src="${a.url}" alt="${escapeHtml(a.name)}"></a>`
        ).join('') + `</div>`;
    }
    if (files.length) {
        html += `<div class="att-files">` + files.map(a =>
            `<a href="${a.url}" target="_blank" class="att-file" title="Open ${escapeHtml(a.name)}">
                <i class="ti ${fileIcon(a.name)}"></i>
                <span class="att-name">${escapeHtml(a.name)}</span>
                <span class="att-size">${formatSize(a.size)}</span>
                <i class="ti ti-download att-dl"></i>
            </a>`
        ).join('') + `</div>`;
    }
    return html;
}
// Edit-form: existing attachments with remove buttons
function buildEditAttachments(atts) {
    if (!atts || !atts.length) return '';
    return `<div class="form-group"><label>Current Attachments</label><div id="editExistingAtts">` +
        atts.map(a =>
            `<div class="att-edit-item" data-att="${a.id}">
                <i class="ti ${fileIcon(a.name)}"></i>
                <span class="att-name">${escapeHtml(a.name)}</span>
                <button type="button" class="att-remove" onclick="removeAttachment(${a.id}, this)"><i class="ti ti-x"></i></button>
            </div>`
        ).join('') + `</div></div>`;
}
// ============================================================
// RENDER
// ============================================================
function renderAnnouncements() {
    const container = document.getElementById('announcementList');
    if (!announcements.length) {
        container.innerHTML = '<div class="empty"><i class="ti ti-inbox"></i>No announcements found</div>';
        document.getElementById('resCount').textContent = '';
        return;
    }
    container.innerHTML = announcements.map(ann => {
        const displayDate   = ann.updated_date_formatted || ann.created_date_formatted;
        const dateLabel     = ann.updated_date_formatted ? 'Updated' : 'Posted';
        const isUnread      = !ann.read;
        const deptLabel     = labelFor(department, ann.department);
        const subDeptLabel  = labelFor(sub_department, ann.sub_department);
        const priorityLabel = labelFor(priority, ann.priority);
        const showPriority  = priorityLabel && priorityLabel.toLowerCase() !== 'general';
        const attCount      = (ann.attachments_count != null) ? ann.attachments_count : (ann.attachments ? ann.attachments.length : 0);
        return `
            <div class="card ${isUnread ? 'unread' : ''}" onclick="viewAnnouncement(${ann.id})">
                <div class="card-top">
                    <div class="card-title">${escapeHtml(ann.title)}</div>
                    <div class="card-date-block">
                        <span class="card-date-label">${dateLabel}</span>
                        <span class="card-date">${formatDate(displayDate)}</span>
                    </div>
                </div>
                <div class="tags">
                    <span class="tag">${escapeHtml(deptLabel)}</span>
                    ${subDeptLabel ? `<span class="tag t-sub">${escapeHtml(subDeptLabel)}</span>` : ''}
                    ${showPriority ? `<span class="tag ${priorityTagClass(priorityLabel)}">${escapeHtml(priorityLabel)}</span>` : ''}
                    ${ann.updated_date_formatted ? '<span class="tag t-edited">Edited</span>' : ''}
                    ${isUnread ? '<span class="tag t-unread">Unread</span>' : ''}
                </div>
                <div class="card-preview"> ${DOMPurify.sanitize(
        (ann.content || '').replace(/<p>(&nbsp;|\s)*<\/p>/g, '')
    )}</div>
                <div class="card-meta">
                    <span class="meta-item"><i class="ti ti-user"></i> ${escapeHtml(ann.stakeholder)}</span>
                    <span class="meta-item"><i class="ti ti-eye"></i> ${ann.views || 0}</span>
                    ${attCount ? `<span class="meta-item"><i class="ti ti-paperclip"></i> ${attCount}</span>` : ''}
                    <span class="meta-item ${ann.read ? 'meta-read' : ''}"><i class="ti ${ann.read ? 'ti-circle-check' : 'ti-circle'}"></i> ${ann.read ? 'Read' : 'Unread'}</span>
                </div>
            </div>
        `;
    }).join('');
    document.getElementById('resCount').textContent = `${announcements.length} result${announcements.length !== 1 ? 's' : ''}`;
}
async function viewAnnouncement(id) {
    showLoading();
    await incrementView(id);                 // bump the view counter first
    const result = await getAnnouncement(id); // then fetch (so the panel shows the updated count)
    if (result.success && result.data) {
        const ann = result.data;
        if (!ann.read) { await markAsRead(id); ann.read = true; }
        showAnnouncementDetail(ann);
    } else {
        closeModal();
        showError('Failed to load announcement details');
    }
}
function showAnnouncementDetail(ann) {
    const canEdit       = ann.can_edit || 0;
    const editHistory   = ann.editHistory || [];
    const deptLabel     = labelFor(department, ann.department);
    const subDeptLabel  = labelFor(sub_department, ann.sub_department);
    const priorityLabel = labelFor(priority, ann.priority);
    const showPriority  = priorityLabel && priorityLabel.toLowerCase() !== 'general';
    document.getElementById('modalHeader').innerHTML = `
        <div class="panel-hd-title">${escapeHtml(ann.title)}</div>
        <div class="panel-hd-actions">
            <button class="icon-btn" onclick="closeModal()" title="Close"><i class="ti ti-x"></i></button>
        </div>
    `;
    document.getElementById('modalBody').innerHTML = `
        <div class="tags" style="margin-bottom:18px;">
            <span class="tag">${escapeHtml(deptLabel)}</span>
            ${subDeptLabel ? `<span class="tag t-sub">${escapeHtml(subDeptLabel)}</span>` : ''}
            ${showPriority ? `<span class="tag ${priorityTagClass(priorityLabel)}">${escapeHtml(priorityLabel)}</span>` : ''}
        </div>
        ${editHistory.length > 0 ? `
            <div class="edit-notice">
                <i class="ti ti-clock-edit"></i>
                <span>Last edited on ${formatDate(ann.updated_date_formatted)} by ${escapeHtml(editHistory[editHistory.length-1]?.by || ann.stakeholder)}${editHistory.length > 1 ? ` · ${editHistory.length} revisions` : ''}</span>
            </div>
        ` : ''}
        <div class="sec-lbl">Content</div>
        <div class="body-text">${DOMPurify.sanitize(
        (ann.content || '').replace(/<p>(&nbsp;|\s)*<\/p>/g, '')
    )}</div>
        ${renderAttachments(ann.attachments)}
        <div class="meta-grid">
            <div class="meta-card"><div class="mc-label">Posted by</div><div class="mc-val">${escapeHtml(ann.stakeholder)}</div></div>
            <div class="meta-card"><div class="mc-label">Sub Department</div><div class="mc-val">${escapeHtml(subDeptLabel || '—')}</div></div>
            <div class="meta-card"><div class="mc-label">Date</div><div class="mc-val">${formatDate(ann.created_date_formatted)}</div></div>
            <div class="meta-card"><div class="mc-label">Views</div><div class="mc-val">${ann.views || 0}</div></div>
            <div class="meta-card"><div class="mc-label">Status</div><div class="mc-val ${ann.read ? 'acknowledged' : ''}">${ann.read ? '✓ Read' : '○ Unread'}</div></div>
        </div>
    `;
    document.getElementById('modalFooter').innerHTML = `
        <button class="foot-btn" onclick="closeModal()">Close</button>
        ${canEdit ? `
            <button class="foot-btn primary" onclick="editAnnouncement(${ann.id})"><i class="ti ti-pencil"></i> Edit</button>
            <button class="foot-btn danger" onclick="deleteAnnouncement(${ann.id},this)"><i class="ti ti-trash"></i> Delete</button>
        ` : ''}
    `;
    openModal();   // detail view is read-only – no editor needed
}
function openPostModal() {
    document.getElementById('modalHeader').innerHTML = `
        <div class="panel-hd-title">Post New Announcement</div>
        <div class="panel-hd-actions"><button class="icon-btn" onclick="closeModal()" title="Close"><i class="ti ti-x"></i></button></div>
    `;
    document.getElementById('modalBody').innerHTML = `
        <div class="form-stack">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" id="postTitle" placeholder="Announcement title">
            </div>
            <div class="form-row cols-3">
                <div class="form-group">
                    <label>Department *</label>
                    <select id="postDept">${buildOptions(department, '', 'Select Department')}</select>
                </div>
                <div class="form-group">
                    <label>Sub Department *</label>
                    <select id="postSubDept">${buildOptions(sub_department, 'counsellor', 'Select Sub Department')}</select>
                </div>
                <div class="form-group">
                    <label>Priority *</label>
                    <select id="postPriority">${buildOptions(priority, '', 'Select Priority')}</select>
                </div>
            </div>
            <div class="form-group">
                <label>Content *</label>
                <textarea id="postContent" placeholder="Write your announcement here..."></textarea>
            </div>
            <div class="form-group">
                <label>Attachments</label>
                <input type="file" id="postFiles" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.png,.jpg,.jpeg,.gif,.webp">
                <span class="att-hint">Word, PDF, Excel, images · multiple files allowed</span>
            </div>
        </div>
    `;
    document.getElementById('modalFooter').innerHTML = `
        <button class="foot-btn" onclick="closeModal()">Cancel</button>
        <button class="foot-btn primary" onclick="submitNewAnnouncement(this)"><i class="ti ti-send"></i> Publish</button>
    `;
    openModal();
    setEditor('postContent');   // init TinyMCE for the new-post form
}
async function editAnnouncement(id) {
    showLoading();
    const result = await getAnnouncement(id);
    if (!result.success || !result.data) { closeModal(); showError('Failed to load announcement'); return; }
    const ann = result.data;
    document.getElementById('modalHeader').innerHTML = `
        <div class="panel-hd-title">Edit Announcement</div>
        <div class="panel-hd-actions"><button class="icon-btn" onclick="closeModal()" title="Close"><i class="ti ti-x"></i></button></div>
    `;
    document.getElementById('modalBody').innerHTML = `
        <div class="form-stack">
            <div class="edit-mode-banner"><i class="ti ti-pencil"></i><span>You are editing an existing announcement.</span></div>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" id="editTitle" value="${escapeHtml(ann.title)}">
            </div>
            <div class="form-row cols-3">
                <div class="form-group">
                    <label>Department</label>
                    <select id="editDept">${buildOptions(department, ann.department, 'Select Department')}</select>
                </div>
                <div class="form-group">
                    <label>Sub Department</label>
                    <select id="editSubDept">${buildOptions(sub_department, ann.sub_department, 'Select Sub Department')}</select>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select id="editPriority">${buildOptions(priority, ann.priority, 'Select Priority')}</select>
                </div>
            </div>
            <div class="form-group">
                <label>Content *</label>
                <textarea id="editContent">${escapeHtml(ann.content || '')}</textarea>
            </div>
            ${buildEditAttachments(ann.attachments)}
            <div class="form-group">
                <label>Add Attachments</label>
                <input type="file" id="editFiles" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.png,.jpg,.jpeg,.gif,.webp">
                <span class="att-hint">Word, PDF, Excel, images · multiple files allowed</span>
            </div>
        </div>
    `;
    document.getElementById('modalFooter').innerHTML = `
        <button class="foot-btn" onclick="closeModal()">Cancel</button>
        <button class="foot-btn primary" onclick="submitEditAnnouncement(${id},this)"><i class="ti ti-device-floppy"></i> Save Changes</button>
    `;
    openModal();
    setEditor('editContent');   // init TinyMCE for the edit form
}
function validateFiles(fileList) {
    for (const f of Array.from(fileList || [])) {
        if (!ALLOWED_EXT.includes(fileExt(f.name))) {
            showError(`"${f.name}" is not an allowed file type`);
            return false;
        }
    }
    return true;
}
async function submitNewAnnouncement(btn) {
    const title   = document.getElementById('postTitle')?.value.trim();
    const content = getEditorHTML('postContent');
    const department = document.getElementById('postDept')?.value.trim();
    const sub_department = document.getElementById('postSubDept')?.value.trim();
    const priority = document.getElementById('postPriority')?.value.trim();
    if (!title || !content || !department || !sub_department || !priority) { showError('Please fill in all required fields'); return; }
    const files = document.getElementById('postFiles')?.files;
    if (!validateFiles(files)) return;
    const data = { title, content, department: department, sub_department: sub_department, priority: priority };
    if (files && files.length) data.attachments = files;
    btnLoading(btn, true, 'Publishing…');
    await createAnnouncement(data);
    btnLoading(btn, false);          // harmless if modal already closed on success
}
async function submitEditAnnouncement(id, btn) {
    const title   = document.getElementById('editTitle')?.value.trim();
    const content = getEditorHTML('editContent');
    if (!title || !content) { showError('Please fill in title and content'); return; }
    const files = document.getElementById('editFiles')?.files;
    if (!validateFiles(files)) return;
    const data = {
        title,
        content,
        department: document.getElementById('editDept')?.value,
        sub_department: document.getElementById('editSubDept')?.value,
        priority: document.getElementById('editPriority')?.value
    };
    if (files && files.length) data.attachments = files;
    btnLoading(btn, true, 'Saving…');
    await updateAnnouncement(id, data);
    btnLoading(btn, false);
}
// ============================================================
// HELPERS
// ============================================================
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
}
function formatDate(dateStr) {
    if (!dateStr) return '—';
    try { return new Date(dateStr).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }); }
    catch (e) { return dateStr; }
}
function showToast(message, type = 'success') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) { alert(message); return; }
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<i class="ti ${type === 'success' ? 'ti-circle-check' : 'ti-alert-circle'}"></i><span>${escapeHtml(message)}</span>`;
    wrap.appendChild(el);
    setTimeout(() => { el.style.transition = 'opacity 0.3s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3000);
}
function showError(message)   { showToast(message, 'error'); }
function showSuccess(message) { showToast(message, 'success'); }
function showLoading() {
    document.getElementById('modalHeader').innerHTML = '';
    document.getElementById('modalBody').innerHTML   = '<div class="empty"><span class="spinner"></span><br>Loading&hellip;</div>';
    document.getElementById('modalFooter').innerHTML = '';
    openModal();
}
function hideLoading() {}
// ─── Modal open / close ──────────────────────────────────────
function openModal()  { document.getElementById('modalOverlay').classList.add('open'); }
async function closeModal(event) {
    if (!event || event.target === document.getElementById('modalOverlay')) {
        await destroyEditors();   // tear down CKEditor cleanly before hiding
        document.getElementById('modalOverlay').classList.remove('open');
    }
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
// ============================================================
// EVENT HANDLERS
// ============================================================
function initEventListeners() {
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => { filters.search = this.value; loadAnnouncements(); }, 300);
    });
    document.getElementById('sortSel').addEventListener('change', function() {
        filters.sort_by = this.value; loadAnnouncements();
    });
    // Sidebar filtering is handled by the inline d_change() handlers.
    document.querySelectorAll('[data-read]').forEach(el => {
        el.addEventListener('click', function() {
            filters.read_status = this.dataset.read;
            document.querySelectorAll('[data-read]').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            loadAnnouncements();
        });
    });
}
// ============================================================
// RICH TEXT EDITOR — TinyMCE (already loaded by Perfex CRM)
// ============================================================
// Perfex ships TinyMCE at assets/plugins/tinymce/tinymce.min.js, so we reuse
// the global `tinymce` instead of loading another editor. TinyMCE renders the
// editable inside an iframe, which also sidesteps any global "disable
// backspace" handler in the admin theme.
window.editors = window.editors || {};

const TINYMCE_CONFIG = {
    menubar: false,
    height: 340,
    branding: false,
    statusbar: false,
    plugins: 'advlist autolink lists link image charmap searchreplace visualblocks code fullscreen insertdatetime media table wordcount',
    toolbar: 'undo redo | blocks | bold italic underline forecolor backcolor | alignleft aligncenter alignright | bullist numlist outdent indent | link image media table | removeformat code fullscreen',
    content_style: "body { font-family: Inter, system-ui, sans-serif; font-size: 13px; line-height: 1.6; }"
};

// Remove any live TinyMCE instances for our two fields (safe to call anytime).
function destroyEditors() {
    if (typeof tinymce === 'undefined') return;
    ['postContent', 'editContent'].forEach(id => {
        const ed = tinymce.get(id);
        if (ed) { try { ed.remove(); } catch (e) { /* already gone */ } }
        delete window.editors[id];
    });
}

// Initialise TinyMCE on the given textarea id ('postContent' or 'editContent').
function setEditor(id) {
    if (typeof tinymce === 'undefined') { console.error('TinyMCE is not loaded on this page'); return; }
    const existing = tinymce.get(id);
    if (existing) { try { existing.remove(); } catch (e) {} }    // never double-init
    tinymce.init(Object.assign({
        selector: '#' + id,
        setup: function (editor) { window.editors[id] = editor; }
    }, TINYMCE_CONFIG));
}

// Read editor HTML, or '' when the user left it empty.
function getEditorHTML(id) {
    const ed = (typeof tinymce !== 'undefined') ? tinymce.get(id) : null;
    return ed ? ed.getContent().trim() : '';
}
async function init() {
    renderSidebar();
    initEventListeners();
    await loadStats();
    await loadAnnouncements();


}
init();
</script>