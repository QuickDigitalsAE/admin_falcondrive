@push('styles')
<style>
    .resource-ckeditor-shell .cke {
        border: 1px solid #e5d7b1 !important;
        border-radius: 18px !important;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .resource-ckeditor-shell .cke_top {
        border-bottom: 1px solid #f0e6ca !important;
        background: linear-gradient(180deg, #fffdf8 0%, #fff7e6 100%) !important;
        padding: 10px 12px !important;
    }

    .resource-ckeditor-shell .cke_bottom {
        border-top: 1px solid #f0e6ca !important;
        background: #fffaf0 !important;
    }

    .resource-ckeditor-shell .cke_contents {
        background: #fffdf8 !important;
    }

    .resource-ckeditor-shell .cke_editable {
        min-height: 220px;
        padding: 16px !important;
        color: #1e293b !important;
        font-size: 14px !important;
    }

    .resource-ckeditor-shell .cke_focus {
        border-color: #caa23c !important;
        box-shadow: 0 0 0 4px rgba(247, 233, 181, 0.9) !important;
    }

    .resource-ckeditor-shell.is-invalid .cke {
        border-color: #fca5a5 !important;
        box-shadow: 0 0 0 4px rgba(254, 226, 226, 0.9) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.CKEDITOR) {
            return;
        }

        const buildEditorConfig = function (direction) {
            return {
                height: 260,
                versionCheck: false,
                removePlugins: 'elementspath',
                resize_enabled: true,
                contentsLangDirection: direction,
                contentsCss: [
                    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap'
                ],
                toolbar: [
                    { name: 'clipboard', items: ['Undo', 'Redo'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                    { name: 'styles', items: ['Styles', 'Format', 'FontSize'] },
                    { name: 'document', items: ['Source'] }
                ]
            };
        };

        window.buildResourceCkeditorConfig = buildEditorConfig;
        window.createResourceCkeditor = function (fieldId, direction) {
            const textarea = document.getElementById(fieldId);
            if (!textarea || CKEDITOR.instances[fieldId]) {
                return CKEDITOR.instances[fieldId] || null;
            }

            return CKEDITOR.replace(fieldId, buildEditorConfig(direction || 'ltr'));
        };

        [
            'blog_description_en',
            'blog_description_ar',
            'description_en',
            'description_ar',
            'first_section_en',
            'first_section_ar',
            'mission_en',
            'mission_ar',
            'vision_en',
            'vision_ar'
        ].forEach(function (fieldId) {
            const textarea = document.getElementById(fieldId);
            if (!textarea || CKEDITOR.instances[fieldId]) {
                return;
            }

            window.createResourceCkeditor(fieldId, fieldId.endsWith('_ar') ? 'rtl' : 'ltr');
        });

        document.querySelectorAll('textarea[data-resource-ckeditor="true"]').forEach(function (textarea) {
            window.createResourceCkeditor(
                textarea.id,
                textarea.dataset.ckeditorDirection || 'ltr'
            );
        });
    });
</script>
@endpush
