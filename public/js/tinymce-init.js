document.addEventListener('DOMContentLoaded', function () {
    if (typeof tinymce === 'undefined') {
        console.error('TinyMCE is not loaded. Please check the CDN URL.');
        return;
    }
    
    tinymce.init({
        selector: 'textarea.tinymce',
        height: 400,
        menubar: false,
        plugins: [
            'link', 'lists', 'table', 'code', 'image', 'charmap', 'preview', 
            'searchreplace', 'wordcount', 'media', 'codesample'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
                 'alignleft aligncenter alignright alignjustify | ' +
                 'bullist numlist | outdent indent | link image table | ' +
                 'charmap codesample | code preview',
        branding: false,
        promotion: false, // Remove "Powered by Tiny" promotion
        content_css: [
            'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css'
        ],
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
        // Image upload configuration (can be enhanced later)
        images_upload_url: false, // Disable for now, can be enabled when image upload is implemented
        automatic_uploads: false,
        // Better mobile support
        mobile: {
            theme: 'mobile',
            plugins: ['autosave', 'lists', 'autolink']
        },
        // Accessibility
        accessibility_focus: true,
        // Better paste handling
        paste_as_text: false,
        paste_auto_cleanup_on_paste: true,
        paste_remove_styles: true,
        paste_remove_styles_if_webkit: true,
        paste_strip_class_attributes: 'all'
    });
});

