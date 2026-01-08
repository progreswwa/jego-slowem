/**
 * JEGO SŁOWEM - Admin Panel JavaScript
 * Enhanced with Toast Notifications, Save Confirmation, Spinners
 */

document.addEventListener('DOMContentLoaded', function () {

    // ========== TOAST NOTIFICATION SYSTEM ==========
    window.showToast = function (message, type = 'success', duration = 4000) {
        // Create toast container if not exists
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
            `;
            document.body.appendChild(container);
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        const icon = type === 'success' ? 'fa-check-circle' :
            type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
        const bgColor = type === 'success' ? 'rgba(34, 197, 94, 0.95)' :
            type === 'error' ? 'rgba(239, 68, 68, 0.95)' : 'rgba(201, 167, 83, 0.95)';

        toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
        toast.style.cssText = `
            background: ${bgColor};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.3s ease;
            cursor: pointer;
        `;

        container.appendChild(toast);

        // Click to dismiss
        toast.addEventListener('click', () => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        });

        // Auto dismiss
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
    };

    // Add CSS animations for toasts
    const toastStyles = document.createElement('style');
    toastStyles.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .btn-saving {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }
        .btn-saving::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-left: 8px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(toastStyles);

    // Convert existing alerts to toasts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const isSuccess = alert.classList.contains('alert-success');
        const message = alert.textContent.trim();
        alert.remove();
        showToast(message, isSuccess ? 'success' : 'error');
    });

    // ========== SAVE CONFIRMATION ==========
    const visualForm = document.querySelector('.visual-form');
    if (visualForm) {
        visualForm.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('btn-saving')) {
                // Add spinner to button
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Zapisywanie...';
                submitBtn.classList.add('btn-saving');

                // Form will submit normally
            }
        });
    }

    // ========== EXISTING FEATURES ==========

    // Dropzone file input highlighting
    const dropzone = document.getElementById('dropzone');
    const imageInput = document.getElementById('imageInput');

    if (dropzone && imageInput) {
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = '#C9A753';
            dropzone.style.background = 'rgba(201, 167, 83, 0.1)';
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.style.borderColor = '';
            dropzone.style.background = '';
        });

        dropzone.addEventListener('drop', (e) => {
            dropzone.style.borderColor = '';
            dropzone.style.background = '';
        });

        imageInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const fileName = this.files[0].name;
                dropzone.querySelector('p').textContent = `Wybrany: ${fileName}`;
            }
        });
    }

    // Search and replace functionality
    window.searchReplace = function () {
        const editor = document.getElementById('editor');
        if (!editor) return;

        const searchTerm = prompt('Tekst do znalezienia:');
        if (!searchTerm) return;

        const replaceTerm = prompt('Zamień na:');
        if (replaceTerm === null) return;

        const content = editor.value;
        const newContent = content.split(searchTerm).join(replaceTerm);

        if (content !== newContent) {
            editor.value = newContent;
            const count = (content.match(new RegExp(searchTerm, 'g')) || []).length;
            showToast(`Zamieniono ${count} wystąpień`, 'success');
        } else {
            showToast('Nie znaleziono tekstu', 'error');
        }
    };

    // Text formatting helpers
    window.formatText = function (type) {
        const editor = document.getElementById('editor');
        if (!editor) return;

        const start = editor.selectionStart;
        const end = editor.selectionEnd;
        const text = editor.value;
        const selectedText = text.substring(start, end);

        let formattedText;
        switch (type) {
            case 'bold':
                formattedText = `<strong>${selectedText}</strong>`;
                break;
            case 'italic':
                formattedText = `<em>${selectedText}</em>`;
                break;
            default:
                return;
        }

        editor.value = text.substring(0, start) + formattedText + text.substring(end);
        editor.focus();
        editor.setSelectionRange(start, start + formattedText.length);
    };

    // Confirm before leaving page with unsaved changes
    const editorForm = document.querySelector('.editor-form');
    if (editorForm) {
        let originalContent = document.getElementById('editor')?.value || '';

        window.addEventListener('beforeunload', function (e) {
            const currentContent = document.getElementById('editor')?.value || '';
            if (currentContent !== originalContent) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        editorForm.addEventListener('submit', function () {
            originalContent = document.getElementById('editor')?.value || '';
        });
    }

    // Password confirmation validation
    const confirmPassword = document.getElementById('confirm_password');
    const newPassword = document.getElementById('new_password');

    if (confirmPassword && newPassword) {
        confirmPassword.addEventListener('input', function () {
            if (this.value !== newPassword.value) {
                this.setCustomValidity('Hasła nie są identyczne');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // ========== MOBILE SIDEBAR TOGGLE ==========
    const mobileToggle = document.querySelector('.mobile-admin-toggle');
    const sidebar = document.querySelector('.admin-sidebar');

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function () {
            this.classList.toggle('active');
            sidebar.classList.toggle('mobile-open');
        });

        // Close sidebar when clicking a link
        sidebar.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileToggle.classList.remove('active');
                sidebar.classList.remove('mobile-open');
            });
        });
    }
});

