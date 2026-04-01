{{-- resources/views/admin/admin_articleManagementCreate.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink - Add Article</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/sidebar.css', 'resources/css/admin/admin_articleManagementCreate.css'])
</head>

<body>
@include('admin.sidebar.admin_sidebar')

<div class="main">

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('admin.articles.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2>Add New Article</h2>
                <p>Paste a URL to auto-fill, or fill in manually</p>
            </div>
        </div>
    </div>

    {{-- ── Validation Errors ── --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ── URL Fetcher ── --}}
    <div class="section fetch-section">
        <h3><i class="fas fa-magic"></i> Auto-Fill from URL</h3>
        <p>
            Paste any health article URL (e.g. Healthline, WHO, WebMD).
            We'll extract the title, summary, cover image, and body text automatically.
        </p>
        <p class="fetch-hint">
            <i class="fas fa-info-circle"></i>
            URLs with <code>#</code> anchors are supported
            (e.g. <code>who.int/health-topics/cancer#tab=tab_1</code>).
        </p>
        <div class="fetch-row">
            <input type="url" id="urlInput"
                   placeholder="https://www.who.int/health-topics/cancer"
                   autocomplete="off">
            <button class="btn-primary" id="fetchBtn" type="button">
                <span id="fetchSpinner" class="spinner hidden"></span>
                <i class="fas fa-download" id="fetchIcon"></i>
                Fetch Article
            </button>
        </div>
        <div id="fetchStatus" class="fetch-status"></div>
    </div>

    {{-- ── Main Form ── --}}
    <form method="POST" action="{{ route('admin.articles.store') }}" id="articleForm">
        @csrf

        <div class="form-layout">

            {{-- ══════════════════════════════
                 LEFT COLUMN
            ══════════════════════════════ --}}
            <div class="form-left">
                <div class="section">
                    <h3>Article Details</h3>

                    {{-- Title --}}
                    <div class="form-group">
                        <label>Title <span class="required">*</span></label>
                        <input type="text" name="title" id="fieldTitle"
                               class="{{ $errors->has('title') ? 'input-error' : '' }}"
                               value="{{ old('title') }}" required>
                        @error('title')
                            <span class="error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- URLs --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label>Source URL</label>
                            <input type="text" name="source_url" id="fieldSourceUrl"
                                   value="{{ old('source_url') }}"
                                   placeholder="https://…">
                            <span class="hint">This is the original article link shown to users.</span>
                        </div>
                        <div class="form-group">
                            <label>Cover Image URL</label>
                            <input type="text" name="image_url" id="fieldImageUrl"
                                   value="{{ old('image_url') }}"
                                   placeholder="https://…/image.jpg"
                                   oninput="updateImagePreview(this.value)">
                        </div>
                    </div>

                    {{-- Image Preview --}}
                    <div id="imagePreviewWrap" class="image-preview-wrap hidden">
                        <label>Cover Preview</label>
                        <img id="imagePreview" src="" alt="preview"
                             onerror="document.getElementById('imagePreviewWrap').classList.add('hidden')">
                    </div>

                    {{-- Summary --}}
                    <div class="form-group">
                        <label>Summary</label>
                        <textarea name="summary" id="fieldSummary" rows="3"
                                  maxlength="500"
                                  placeholder="Short description shown on the mobile app card…">{{ old('summary') }}</textarea>
                        <div class="char-count"><span id="summaryCount">0</span>/500</div>
                    </div>

                    {{-- Content --}}
                    <div class="form-group">
                        <label>Full Article Content</label>
                        <textarea name="content" id="fieldContent" rows="14"
                                  class="monospace"
                                  placeholder="Paste or auto-fill the full article body here…">{{ old('content') }}</textarea>
                        <span class="hint">This text is shown when users tap "Read More" in the mobile app.</span>
                    </div>

                </div>
            </div>

            {{-- ══════════════════════════════
                 RIGHT COLUMN
            ══════════════════════════════ --}}
            <div class="form-right">

                {{-- Publish Settings --}}
                <div class="section">
                    <h3>Publish Settings</h3>

                    <div class="toggle-group">
                        <div class="toggle-row">
                            <div>
                                <span class="toggle-label">Published</span>
                                <p class="toggle-hint">Visible to mobile app users immediately. Turn off to save as a draft.</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_published" id="isPublished"
                                       value="1" {{ old('is_published', '1') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="toggle-row">
                            <div>
                                <span class="toggle-label">Featured</span>
                                <p class="toggle-hint">Shown with a ⭐ badge; appears first in the list.</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_featured" id="isFeatured"
                                       value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Display Order</label>
                        <input type="number" name="sort_order"
                               value="{{ old('sort_order', 0) }}" min="0">
                        <span class="hint">
                            <i class="fas fa-info-circle"></i>
                            <strong>0</strong> = shown first &nbsp;|&nbsp;
                            <strong>1</strong> = second &nbsp;|&nbsp;
                            <strong>2</strong> = third.
                            Leave at <strong>0</strong> if order doesn't matter.
                        </span>
                    </div>
                </div>

                {{-- Category --}}
                <div class="section">
                    <h3>Category</h3>
                    <div class="form-group">
                        <select name="category"
                                class="{{ $errors->has('category') ? 'input-error' : '' }}"
                                required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}"
                                    {{ old('category') === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <span class="error-msg">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Source Metadata --}}
                <div class="section">
                    <h3>Source Metadata</h3>
                    <div class="form-group">
                        <label>Author</label>
                        <input type="text" name="author" id="fieldAuthor"
                               value="{{ old('author') }}"
                               placeholder="e.g. Dr. Jane Smith">
                    </div>
                    <div class="form-group">
                        <label>Source Name</label>
                        <input type="text" name="source_name" id="fieldSourceName"
                               value="{{ old('source_name') }}"
                               placeholder="e.g. WHO, Healthline">
                    </div>
                    <div class="form-group">
                        <label>Published Date</label>
                        <input type="date" name="published_at"
                               value="{{ old('published_at') }}">
                    </div>
                </div>

                {{-- Submit --}}
                <div class="form-actions">
                    <button type="submit" class="btn-primary btn-full" id="saveBtn">
                        <i class="fas fa-save"></i> Save Article
                    </button>
                    <a href="{{ route('admin.articles.index') }}"
                       class="btn-secondary btn-full">
                        Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>

</div>

@vite(['resources/js/sidebar.js'])
<script>
// ── Summary counter ──────────────────────────────────────────
const summaryField = document.getElementById('fieldSummary');
const summaryCount = document.getElementById('summaryCount');
function updateCount() { summaryCount.textContent = summaryField.value.length; }
summaryField.addEventListener('input', updateCount);
updateCount();

// ── Image preview ────────────────────────────────────────────
function updateImagePreview(url) {
    const wrap = document.getElementById('imagePreviewWrap');
    const img  = document.getElementById('imagePreview');
    if (url && url.startsWith('http')) {
        img.src = url;
        wrap.classList.remove('hidden');
    } else {
        wrap.classList.add('hidden');
    }
}

// ── Prevent double-submit ────────────────────────────────────
document.getElementById('articleForm').addEventListener('submit', function () {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Saving…';
});

// ── URL Fetch ─────────────────────────────────────────────────
document.getElementById('fetchBtn').addEventListener('click', async function () {
    const url    = document.getElementById('urlInput').value.trim();
    const status = document.getElementById('fetchStatus');
    const btn    = this;
    const icon   = document.getElementById('fetchIcon');
    const spin   = document.getElementById('fetchSpinner');

    if (!url) {
        status.innerHTML = '<span class="status-error">Please enter a URL first.</span>';
        return;
    }

    btn.disabled = true;
    icon.classList.add('hidden');
    spin.classList.remove('hidden');
    status.innerHTML = '<span class="status-info">Fetching article data…</span>';

    try {
        const resp = await fetch('{{ route("admin.articles.fetch-url") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ url }),
        });
        const data = await resp.json();

        if (!data.success) {
            status.innerHTML = `<span class="status-warning">
                <i class="fas fa-exclamation-triangle"></i> ${data.message}
                <br><small>You can still fill in the fields manually below.</small>
            </span>`;
            return;
        }

        if (data.title)       document.getElementById('fieldTitle').value      = data.title;
        if (data.summary)     document.getElementById('fieldSummary').value     = data.summary;
        if (data.image_url)   document.getElementById('fieldImageUrl').value    = data.image_url;
        if (data.author)      document.getElementById('fieldAuthor').value      = data.author;
        if (data.source_name) document.getElementById('fieldSourceName').value  = data.source_name;
        if (data.content)     document.getElementById('fieldContent').value     = data.content;
        document.getElementById('fieldSourceUrl').value = url;

        if (data.image_url) updateImagePreview(data.image_url);
        updateCount();

        status.innerHTML = `<span class="status-success">
            <i class="fas fa-check-circle"></i>
            Fields filled from <strong>${data.source_name || url}</strong>.
            Review everything below, then click <strong>Save Article</strong>.
        </span>`;

    } catch (e) {
        status.innerHTML = `<span class="status-error">
            <i class="fas fa-times-circle"></i> Network error: ${e.message}
        </span>`;
    } finally {
        btn.disabled = false;
        icon.classList.remove('hidden');
        spin.classList.add('hidden');
    }
});
</script>
@vite(['resources/js/sidebar.js'])
</body>
</html>