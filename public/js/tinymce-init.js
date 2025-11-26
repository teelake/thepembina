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
        // Image upload configuration
        images_upload_url: (typeof BASE_URL !== 'undefined' ? BASE_URL : window.location.origin) + '/admin/pages/upload-image',
        automatic_uploads: true,
        images_upload_handler: function (blobInfo, progress) {
            return new Promise(function (resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                var baseUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : window.location.origin);
                var uploadUrl = baseUrl + '/admin/pages/upload-image';
                xhr.open('POST', uploadUrl);
                
                xhr.upload.onprogress = function (e) {
                    progress(e.loaded / e.total * 100);
                };
                
                xhr.onload = function () {
                    if (xhr.status === 403) {
                        reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                        return;
                    }
                    
                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject('HTTP Error: ' + xhr.status);
                        return;
                    }
                    
                    var json;
                    try {
                        json = JSON.parse(xhr.responseText);
                    } catch (e) {
                        reject('Invalid JSON: ' + xhr.responseText);
                        return;
                    }
                    
                    if (!json || typeof json.location !== 'string') {
                        reject('Invalid JSON: ' + xhr.responseText);
                        return;
                    }
                    
                    resolve(json.location);
                };
                
                xhr.onerror = function () {
                    reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                };
                
                var formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                
                xhr.send(formData);
            });
        },
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

