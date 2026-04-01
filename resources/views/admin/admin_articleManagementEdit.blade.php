{{-- resources/views/admin/admin_articleManagementEdit.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink - Edit Article</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/sidebar.css', 'resources/css/admin/admin_articleManagementEdit.css'])
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
                <h2>Edit Article</h2>
                <p>ID #{{ $article->article_id }} &middot; Created {{ $article->created_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.articles.update', $article->article_id) }}">
        @csrf @method('PUT')

        <div class="form-layout">

            {{-- ── Left Column ── --}}
            <div class="form-left">
                <div class="section">
                    <h3>Article Details</h3>

                    {{-- Title --}}
                    <div class="form-group">
                        <label>Title <span class="required">*</span></label>
                        <input type="text" name="title"
                               class="{{ $errors->has('title') ? 'input-error' : '' }}"
                               value="{{ old('title', $article->title) }}" required>
                        @error('title')
                            <span class="error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- URLs --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label>Source URL</label>
                            <div class="input-with-action">
                                <input type="url" name="source_url" id="fieldSourceUrl"
                                       value="{{ old('source_url', $article->source_url) }}">
                                @if($article->source_url)
                                <a href="{{ $article->source_url }}" target="_blank"
                                   class="btn-icon btn-view" title="Open source">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Cover Image URL</label>
                            <input type="url" name="image_url" id="fieldImageUrl"
                                   value="{{ old('image_url', $article->image_url) }}"
                                   oninput="updateImagePreview(this.value)">
                        </div>
                    </div>

                    {{-- Image Preview --}}
                    <div id="imagePreviewWrap"
                         class="image-preview-wrap {{ $article->image_url ? '' : 'hidden' }}">
                        <label>Cover Preview</label>
                        <img id="imagePreview" src="{{ $article->image_url }}" alt="preview"
                             onerror="document.getElementById('imagePreviewWrap').classList.add('hidden')">
                    </div>

                    {{-- Summary --}}
                    <div class="form-group">
                        <label>Summary</label>
                        <textarea name="summary" id="fieldSummary" rows="3"
                                  maxlength="500">{{ old('summary', $article->summary) }}</textarea>
                        <div class="char-count"><span id="summaryCount">0</span>/500</div>
                    </div>

                    {{-- Content --}}
                    <div class="form-group">
                        <label>Full Article Content</label>
                        <textarea name="content" rows="14"
                                  class="monospace">{{ old('content', $article->content) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ── Right Column ── --}}
            <div class="form-right">

                {{-- Stats Banner --}}
                <div class="section stats-banner">
                    <div class="stat-row">
                        <span>Total Views</span>
                        <strong>{{ number_format($article->view_count) }}</strong>
                    </div>
                    <div class="stat-row">
                        <span>Created</span>
                        <span>{{ $article->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="stat-row">
                        <span>Last Updated</span>
                        <span>{{ $article->updated_at->diffForHumans() }}</span>
                    </div>
                </div>

                {{-- Publish Settings --}}
                <div class="section">
                    <h3>Publish Settings</h3>
                    <div class="toggle-group">
                        <div class="toggle-row">
                            <label class="toggle-label">Published</label>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_published" id="isPublished"
                                       value="1"
                                       {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="toggle-row">
                            <label class="toggle-label">Featured</label>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_featured" id="isFeatured"
                                       value="1"
                                       {{ old('is_featured', $article->is_featured) ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" min="0"
                               value="{{ old('sort_order', $article->sort_order) }}">
                    </div>
                </div>

                {{-- Category --}}
                <div class="section">
                    <h3>Category</h3>
                    <div class="form-group">
                        <select name="category" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}"
                                    {{ old('category', $article->category) === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Source Metadata --}}
                <div class="section">
                    <h3>Source Metadata</h3>
                    <div class="form-group">
                        <label>Author</label>
                        <input type="text" name="author"
                               value="{{ old('author', $article->author) }}">
                    </div>
                    <div class="form-group">
                        <label>Source Name</label>
                        <input type="text" name="source_name"
                               value="{{ old('source_name', $article->source_name) }}">
                    </div>
                    <div class="form-group">
                        <label>Published Date</label>
                        <input type="date" name="published_at"
                               value="{{ old('published_at', $article->published_at?->format('Y-m-d')) }}">
                    </div>
                </div>

                {{-- Submit --}}
                <div class="form-actions">
                    <button type="submit" class="btn-primary btn-full">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.articles.index') }}" class="btn-secondary btn-full">
                        Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>

</div>

@vite(['resources/js/sidebar.js'])
<script>
const summaryField = document.getElementById('fieldSummary');
const summaryCount = document.getElementById('summaryCount');
function updateCount() { summaryCount.textContent = summaryField.value.length; }
summaryField.addEventListener('input', updateCount);
updateCount();

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
</script>
@vite(['resources/js/sidebar.js'])
</body>
</html>