<style>
.menu-builder { display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start; }
@media (max-width: 1100px) { .menu-builder { grid-template-columns: 1fr; } }

.menu-item-row {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 8px;
    transition: border-color 0.2s, box-shadow 0.2s, margin-left 0.15s;
    position: relative;
}
.menu-item-row:hover { border-color: color-mix(in srgb, var(--primary) 30%, transparent); }
.menu-item-row.dragging { opacity: 0.45; }

/* Un enfant est décalé et rattaché visuellement à son parent : la hiérarchie
   doit se lire sans ouvrir les champs. */
.menu-item-row.child { margin-left: 36px; border-left: 3px solid color-mix(in srgb, var(--primary) 45%, transparent); }
.menu-item-row.inactive { opacity: 0.62; }

.item-header { display: flex; align-items: center; gap: 12px; }
.item-drag-handle { color: var(--text-muted); cursor: grab; flex-shrink: 0; }
.item-drag-handle:active { cursor: grabbing; }
.item-label { font-weight: 600; font-size: 0.9rem; }
.item-url-preview { font-family: monospace; font-size: 0.78rem; color: var(--text-muted); word-break: break-all; }
.item-actions { display: flex; gap: 6px; align-items: center; flex-shrink: 0; }

.item-badge {
    font-size: 0.7rem; padding: 2px 8px; border-radius: 4px;
    background: var(--border); color: var(--text-muted); white-space: nowrap;
}

.item-details {
    display: none;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid var(--border);
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.item-details.open { display: grid; }
.item-details .form-group { margin-bottom: 0; }

.add-link-panel { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; padding: 20px; }
.add-link-panel h4 { font-size: 0.95rem; font-weight: 700; margin-bottom: 16px; }

.btn-nest {
    padding: 4px 8px; font-size: 0.78rem; line-height: 1;
    background: transparent; border: 1px solid var(--border);
    border-radius: 6px; cursor: pointer; color: var(--text-muted);
}
.btn-nest:hover:not(:disabled) { border-color: var(--primary); color: var(--primary); }
.btn-nest:disabled { opacity: 0.3; cursor: not-allowed; }

.menu-alert {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 12px 14px; border-radius: 10px; font-size: 0.84rem; line-height: 1.5;
    background: rgba(245, 158, 11, 0.08);
    border: 1px solid rgba(245, 158, 11, 0.25);
    color: #b45309;
    margin-bottom: 18px;
}

.btn-page-add {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 12px; background: var(--bg-surface);
    border: 1px solid var(--border); border-radius: 8px;
    cursor: pointer; width: 100%; text-align: left; transition: all 0.2s;
}
.btn-page-add:hover { border-color: var(--primary); }
.btn-page-add[disabled] { opacity: 0.45; cursor: default; }
</style>
