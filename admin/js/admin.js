/**
 * JEGO SŁOWEM - Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

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
            alert(`Zamieniono ${count} wystąpień.`);
        } else {
            alert('Nie znaleziono tekstu.');
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
});
