{{-- Rich text editor (Quill via CDN) bound to a hidden textarea. Expects $name and $value. --}}
@once
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
        <style>.ql-container { min-height: 260px; font-size: 15px; font-family: inherit; } .ql-toolbar, .ql-container { border-color: #d9dde3 !important; } .ql-toolbar { border-radius: 7px 7px 0 0; } .ql-container { border-radius: 0 0 7px 7px; }</style>
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
        <script>
            document.querySelectorAll('[data-editor]').forEach((wrap) => {
                const textarea = wrap.querySelector('textarea');
                const holder = wrap.querySelector('.editor-holder');
                const quill = new Quill(holder, {
                    theme: 'snow',
                    modules: { toolbar: [[{ header: [2, 3, 4, false] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link', 'image', 'blockquote'], [{ align: [] }], ['clean']] }
                });
                quill.root.innerHTML = textarea.value;
                textarea.closest('form').addEventListener('submit', () => {
                    textarea.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
                });
            });
        </script>
    @endpush
@endonce
<div data-editor>
    <textarea name="{{ $name }}" hidden>{{ $value }}</textarea>
    <div class="editor-holder"></div>
</div>
