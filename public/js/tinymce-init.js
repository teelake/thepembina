document.addEventListener('DOMContentLoaded', function () {
    if (typeof tinymce === 'undefined') {
        return;
    }
    tinymce.init({
        selector: 'textarea.tinymce',
        height: 400,
        menubar: false,
        plugins: 'link lists table code',
        toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link table | code',
        branding: false,
        content_css: [
            'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css'
        ]
    });
});

