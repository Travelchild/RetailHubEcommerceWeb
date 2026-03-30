// Mobile navigation
document.querySelectorAll('[data-nav-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('[data-mobile-nav]').forEach((panel) => {
            panel.classList.toggle('hidden');
        });
    });
});

// Product image dropzone (admin)
document.querySelectorAll('[data-dropzone]').forEach((dropzone) => {
    const input = dropzone.querySelector('[data-dropzone-input]');
    const previewImg = dropzone.querySelector('[data-dropzone-preview]');
    const previewWrap = dropzone.querySelector('[data-dropzone-preview-wrap]');
    const fileLabel = dropzone.querySelector('[data-dropzone-file]');

    if (!input) return;

    const showPreview = (file) => {
        if (!file) return;
        if (fileLabel) fileLabel.textContent = file.name;
        if (previewImg && previewWrap) {
            previewWrap.classList.remove('hidden');
            previewImg.src = URL.createObjectURL(file);
        }
    };

    dropzone.addEventListener('click', (e) => {
        if (e.target === input) return;
        input.click();
    });

    input.addEventListener('change', (event) => {
        const file = event.target.files && event.target.files[0];
        showPreview(file);
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.add('dropzone-active');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.remove('dropzone-active');
        });
    });

    dropzone.addEventListener('drop', (event) => {
        const dt = event.dataTransfer;
        if (!dt || !dt.files || dt.files.length === 0) return;
        input.files = dt.files;
        showPreview(dt.files[0]);
    });
});
