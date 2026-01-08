/**
 * Inline Visual CMS Editor
 * Click on elements to edit them directly
 */

(function () {
    'use strict';

    // Only run in edit mode
    if (!window.CMS_EDIT_MODE) return;

    let currentEditElement = null;
    let isDirty = false;

    // Create admin bar
    function createAdminBar() {
        const bar = document.createElement('div');
        bar.id = 'cms-admin-bar';
        bar.innerHTML = `
            <div class="cms-bar-left">
                <span class="cms-bar-logo">CMS</span>
                <span class="cms-bar-status">Tryb Edycji</span>
            </div>
            <div class="cms-bar-right">
                <button id="cms-save-all" class="cms-bar-btn cms-btn-primary" disabled>
                    <i class="fas fa-save"></i> Zapisz wszystko
                </button>
                <a href="/admin/logout.php" class="cms-bar-btn cms-btn-secondary">
                    <i class="fas fa-sign-out-alt"></i> Wyloguj
                </a>
            </div>
        `;
        document.body.prepend(bar);

        // Add save handler
        document.getElementById('cms-save-all').addEventListener('click', saveAllChanges);
    }

    // Mark editable elements
    function initEditableElements() {
        // Text elements
        document.querySelectorAll('[data-cms]').forEach(el => {
            el.classList.add('cms-editable', 'cms-editable-text');
            el.setAttribute('title', 'Kliknij aby edytować');
            el.addEventListener('click', handleTextClick);
        });

        // Image elements
        document.querySelectorAll('[data-cms-img]').forEach(el => {
            el.classList.add('cms-editable', 'cms-editable-img');
            el.setAttribute('title', 'Kliknij aby zmienić zdjęcie');
            el.addEventListener('click', handleImageClick);
        });
    }

    // Handle text element click
    function handleTextClick(e) {
        e.preventDefault();
        e.stopPropagation();

        const el = e.currentTarget;
        const field = el.getAttribute('data-cms');
        const page = el.getAttribute('data-cms-page') || 'home';
        const currentText = el.innerText;

        showTextEditor(el, page, field, currentText);
    }

    // Show text editor popup
    function showTextEditor(el, page, field, currentText) {
        // Remove any existing editor
        closeEditor();

        currentEditElement = el;
        el.classList.add('cms-editing');

        const editor = document.createElement('div');
        editor.id = 'cms-text-editor';
        editor.className = 'cms-editor-popup';
        editor.innerHTML = `
            <div class="cms-editor-header">
                <span>Edytuj: ${field}</span>
                <button class="cms-editor-close">&times;</button>
            </div>
            <div class="cms-editor-toolbar">
                <button data-cmd="bold" title="Pogrubienie"><i class="fas fa-bold"></i></button>
                <button data-cmd="italic" title="Kursywa"><i class="fas fa-italic"></i></button>
            </div>
            <div class="cms-editor-content" contenteditable="true">${currentText}</div>
            <div class="cms-editor-footer">
                <button class="cms-btn-cancel">Anuluj</button>
                <button class="cms-btn-apply">Zastosuj</button>
            </div>
        `;

        document.body.appendChild(editor);

        // Position near element
        positionEditor(editor, el);

        // Focus content
        editor.querySelector('.cms-editor-content').focus();

        // Event handlers
        editor.querySelector('.cms-editor-close').addEventListener('click', closeEditor);
        editor.querySelector('.cms-btn-cancel').addEventListener('click', closeEditor);
        editor.querySelector('.cms-btn-apply').addEventListener('click', () => applyTextChange(el, page, field, editor));

        // Toolbar commands
        editor.querySelectorAll('[data-cmd]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.execCommand(btn.dataset.cmd, false, null);
            });
        });
    }

    // Apply text change
    function applyTextChange(el, page, field, editor) {
        const newText = editor.querySelector('.cms-editor-content').innerHTML;
        el.innerHTML = newText;

        // Mark as changed
        el.classList.add('cms-changed');
        el.setAttribute('data-cms-new-value', newText);
        isDirty = true;
        document.getElementById('cms-save-all').disabled = false;

        closeEditor();
        showToast('Zmiana zastosowana - kliknij "Zapisz wszystko"', 'info');
    }

    // Handle image click
    function handleImageClick(e) {
        e.preventDefault();
        e.stopPropagation();

        const el = e.currentTarget;
        const field = el.getAttribute('data-cms-img');
        const page = el.getAttribute('data-cms-page') || 'home';

        showImagePicker(el, page, field);
    }

    // Show image picker
    function showImagePicker(el, page, field) {
        closeEditor();
        currentEditElement = el;
        el.classList.add('cms-editing');

        const picker = document.createElement('div');
        picker.id = 'cms-image-picker';
        picker.className = 'cms-editor-popup cms-image-popup';
        picker.innerHTML = `
            <div class="cms-editor-header">
                <span>Zmień zdjęcie</span>
                <button class="cms-editor-close">&times;</button>
            </div>
            <div class="cms-image-preview">
                <img src="${el.src}" alt="Podgląd">
            </div>
            <div class="cms-image-upload">
                <label class="cms-upload-btn">
                    <i class="fas fa-upload"></i> Wybierz nowe zdjęcie
                    <input type="file" accept="image/*" hidden>
                </label>
            </div>
            <div class="cms-editor-footer">
                <button class="cms-btn-cancel">Anuluj</button>
            </div>
        `;

        document.body.appendChild(picker);
        positionEditor(picker, el);

        // Event handlers
        picker.querySelector('.cms-editor-close').addEventListener('click', closeEditor);
        picker.querySelector('.cms-btn-cancel').addEventListener('click', closeEditor);

        picker.querySelector('input[type="file"]').addEventListener('change', (e) => {
            handleImageUpload(e, el, page, field, picker);
        });
    }

    // Handle image upload
    async function handleImageUpload(e, el, page, field, picker) {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);
        formData.append('field', field);

        try {
            const response = await fetch('/admin/upload.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                el.src = data.path;
                el.classList.add('cms-changed');
                el.setAttribute('data-cms-new-value', data.path);
                isDirty = true;
                document.getElementById('cms-save-all').disabled = false;

                closeEditor();
                showToast('Zdjęcie zmienione - kliknij "Zapisz wszystko"', 'info');
            } else {
                showToast(data.error || 'Błąd uploadu', 'error');
            }
        } catch (err) {
            showToast('Błąd połączenia', 'error');
        }
    }

    // Position editor near element
    function positionEditor(editor, el) {
        const rect = el.getBoundingClientRect();
        const scrollY = window.scrollY;

        editor.style.top = (rect.bottom + scrollY + 10) + 'px';
        editor.style.left = Math.max(10, rect.left) + 'px';
    }

    // Close editor
    function closeEditor() {
        const existing = document.querySelector('.cms-editor-popup');
        if (existing) existing.remove();

        if (currentEditElement) {
            currentEditElement.classList.remove('cms-editing');
            currentEditElement = null;
        }
    }

    // Save all changes
    async function saveAllChanges() {
        const changedElements = document.querySelectorAll('.cms-changed');
        if (changedElements.length === 0) return;

        const saveBtn = document.getElementById('cms-save-all');
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Zapisuję...';
        saveBtn.disabled = true;

        let errors = 0;

        for (const el of changedElements) {
            const isImage = el.hasAttribute('data-cms-img');
            const field = isImage ? el.getAttribute('data-cms-img') : el.getAttribute('data-cms');
            const page = el.getAttribute('data-cms-page') || 'home';
            const value = el.getAttribute('data-cms-new-value');

            try {
                const response = await fetch('/admin/save.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ page, field, value, type: isImage ? 'image' : 'text' })
                });

                const data = await response.json();

                if (data.success) {
                    el.classList.remove('cms-changed');
                    el.removeAttribute('data-cms-new-value');
                } else {
                    errors++;
                }
            } catch (err) {
                errors++;
            }
        }

        if (errors === 0) {
            showToast('Wszystkie zmiany zapisane!', 'success');
            isDirty = false;
        } else {
            showToast(`Zapisano z ${errors} błędami`, 'error');
        }

        saveBtn.innerHTML = '<i class="fas fa-save"></i> Zapisz wszystko';
        saveBtn.disabled = document.querySelectorAll('.cms-changed').length === 0;
    }

    // Toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `cms-toast cms-toast-${type}`;
        toast.innerHTML = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('cms-toast-show'), 10);
        setTimeout(() => {
            toast.classList.remove('cms-toast-show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', (e) => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Close editor on escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeEditor();
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        createAdminBar();
        initEditableElements();
    });
})();
