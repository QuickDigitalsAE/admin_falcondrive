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

    .resource-ckeditor-shell .cke_contents,
    .resource-ckeditor-shell .cke_wysiwyg_frame {
        scrollbar-width: thin;
        scrollbar-color: rgba(201, 166, 74, 0.82) rgba(246, 239, 224, 0.45);
    }

    .resource-ckeditor-shell .cke_contents::-webkit-scrollbar {
        width: 12px;
        height: 12px;
    }

    .resource-ckeditor-shell .cke_contents::-webkit-scrollbar-track {
        background: linear-gradient(180deg, rgba(255, 250, 240, 0.98) 0%, rgba(246, 239, 224, 0.92) 100%);
        border-left: 1px solid rgba(229, 215, 177, 0.85);
    }

    .resource-ckeditor-shell .cke_contents::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(216, 183, 96, 0.95) 0%, rgba(180, 138, 31, 0.95) 100%);
        border: 2px solid rgba(255, 250, 240, 0.96);
        border-radius: 999px;
    }

    .resource-ckeditor-shell .cke_contents::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, rgba(202, 162, 60, 1) 0%, rgba(147, 107, 16, 1) 100%);
    }

    .resource-ckeditor-shell .cke_editable {
        min-height: 280px;
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

        CKEDITOR.addCss(`
            html {
                scrollbar-width: thin;
                scrollbar-color: rgba(201, 166, 74, 0.82) rgba(246, 239, 224, 0.45);
            }

            body {
                scrollbar-width: thin;
                scrollbar-color: rgba(201, 166, 74, 0.82) rgba(246, 239, 224, 0.45);
            }

            body::-webkit-scrollbar {
                width: 12px;
                height: 15px;
            }

            body::-webkit-scrollbar-track {
                background: linear-gradient(180deg, rgba(255, 250, 240, 0.98) 0%, rgba(246, 239, 224, 0.92) 100%);
            }

            body::-webkit-scrollbar-thumb {
                background: linear-gradient(180deg, rgba(216, 183, 96, 0.95) 0%, rgba(180, 138, 31, 0.95) 100%);
                border: 2px solid rgba(255, 250, 240, 0.96);
                border-radius: 999px;
            }

            body::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(180deg, rgba(202, 162, 60, 1) 0%, rgba(147, 107, 16, 1) 100%);
            }
        `);

        const buildEditorConfig = function (direction) {
            return {
                height: 320,
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
