{{-- resources/views/admin/admin_articleManagement.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink - Article Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/sidebar.css', 'resources/css/admin/admin_articleManagement.css'])
</head>

<body>
@include('admin.sidebar.admin_sidebar')

<div class="main">

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <div>
            <h2>📰 Article Management</h2>
            <p>Manage health articles displayed in the MediLink mobile app</p>
        </div>
        <a href="{{ route('admin.articles.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> Add Article
        </a>
    </div>

    {{-- ── Flash Message ── --}}
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ── Stats Cards ── --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-blue"><i class="fas fa-newspaper"></i></div>
            <div class="stat-info">
                <span class="stat-number">{{ $stats['total'] }}</span>
                <span class="stat-label">Total Articles</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-green"><i class="fas fa-eye"></i></div>
            <div class="stat-info">
                <span class="stat-number">{{ $stats['published'] }}</span>
                <span class="stat-label">Published</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-yellow"><i class="fas fa-eye-slash"></i></div>
            <div class="stat-info">
                <span class="stat-number">{{ $stats['unpublished'] }}</span>
                <span class="stat-label">Unpublished</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-cyan"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <span class="stat-number">{{ $stats['featured'] }}</span>
                <span class="stat-label">Featured</span>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="section filter-section">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label>Search</label>
                <input type="text" name="search" placeholder="Title, source, category…"
                       value="{{ request('search') }}">
            </div>
            <div class="filter-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="published"   {{ request('status') === 'published'   ? 'selected' : '' }}>Published</option>
                    <option value="unpublished" {{ request('status') === 'unpublished' ? 'selected' : '' }}>Unpublished</option>
                    <option value="deleted"     {{ request('status') === 'deleted'     ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('admin.articles.index') }}" class="btn-secondary" title="Reset">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ── Articles Table ── --}}
    <div class="section">
        @if($articles->isEmpty())
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <p><strong>No articles found.</strong></p>
                <p>Start by adding your first health article.</p>
                <a href="{{ route('admin.articles.create') }}" class="btn-primary">
                    <i class="fas fa-plus"></i> Add Article
                </a>
            </div>
        @else
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                    <tr class="{{ $article->trashed() ? 'row-deleted' : '' }}">

                        <td class="text-muted">{{ $article->article_id }}</td>

                        {{-- Cover --}}
                        <td>
                            @if($article->image_url)
                                <img src="{{ $article->image_url }}" alt=""
                                     class="cover-thumb"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="cover-placeholder" style="display:none;">
                                    <i class="fas fa-image"></i>
                                </div>
                            @else
                                <div class="cover-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </td>

                        {{-- Title --}}
                        <td>
                            <div class="article-title">
                                {{ Str::limit($article->title, 60) }}
                                @if($article->is_featured)
                                    <span class="badge-featured">⭐</span>
                                @endif
                            </div>
                            @if($article->source_url)
                                <a href="{{ $article->source_url }}" target="_blank" class="source-link">
                                    <i class="fas fa-external-link-alt"></i>
                                    {{ Str::limit($article->source_url, 45) }}
                                </a>
                            @endif
                        </td>

                        {{-- Category --}}
                        <td><span class="badge-category">{{ $article->category }}</span></td>

                        {{-- Source name --}}
                        <td class="text-muted">{{ $article->source_name ?: '—' }}</td>

                        {{-- Status toggle --}}
                        <td>
                            @if($article->trashed())
                                <span class="badge-deleted">Deleted</span>
                            @else
                                <label class="toggle-switch">
                                    <input type="checkbox"
                                           class="toggle-publish"
                                           data-id="{{ $article->article_id }}"
                                           {{ $article->is_published ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            @endif
                        </td>

                        {{-- Views --}}
                        <td class="text-muted">{{ number_format($article->view_count) }}</td>

                        {{-- ✅ Sort order — shows saved value, uses dedicated endpoint --}}
                        <td>
                            <input type="number"
                                   class="sort-order-input"
                                   data-id="{{ $article->article_id }}"
                                   value="{{ $article->sort_order }}"
                                   min="0">
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="action-buttons">
                                @if(!$article->trashed())
                                <a href="{{ route('admin.articles.show', $article->article_id) }}"
                                   class="btn-icon btn-view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.articles.edit', $article->article_id) }}"
                                   class="btn-icon btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('admin.articles.destroy', $article->article_id) }}"
                                      onsubmit="return confirm('Delete this article?')"
                                      style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon btn-delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @else
                                <form method="POST"
                                      action="{{ route('admin.articles.restore', $article->article_id) }}"
                                      style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-icon btn-restore">
                                        <i class="fas fa-undo"></i> Restore
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination">
            <p>
                Showing {{ $articles->firstItem() }}–{{ $articles->lastItem() }}
                of {{ $articles->total() }} articles
            </p>
            <div class="pages">
                @if($articles->onFirstPage())
                    <button disabled>&laquo; Prev</button>
                @else
                    <a href="{{ $articles->previousPageUrl() }}"><button>&laquo; Prev</button></a>
                @endif

                @foreach($articles->getUrlRange(1, $articles->lastPage()) as $page => $url)
                    @if($page == $articles->currentPage())
                        <button class="active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}"><button>{{ $page }}</button></a>
                    @endif
                @endforeach

                @if($articles->hasMorePages())
                    <a href="{{ $articles->nextPageUrl() }}"><button>Next &raquo;</button></a>
                @else
                    <button disabled>Next &raquo;</button>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>

@vite(['resources/js/sidebar.js'])
<script>
// ── Publish toggle ───────────────────────────────────────────
document.querySelectorAll('.toggle-publish').forEach(toggle => {
    toggle.addEventListener('change', function () {
        const id   = this.dataset.id;
        const self = this;
        fetch(`/admin/articles/${id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => { if (!data.success) self.checked = !self.checked; })
        .catch(() => { self.checked = !self.checked; });
    });
});

// ── Sort order auto-save ─────────────────────────────────────
// ✅ Uses dedicated PATCH /admin/articles/{id}/sort-order endpoint
//    so it does NOT require title/category — previous PUT failed silently
document.querySelectorAll('.sort-order-input').forEach(input => {
    let timer;
    let savedValue = parseInt(input.value) || 0;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const id  = this.dataset.id;
        const val = parseInt(this.value) || 0;

        timer = setTimeout(() => {
            fetch(`/admin/articles/${id}/sort-order`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ sort_order: val }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    savedValue = val;
                    // Brief visual confirmation
                    input.style.borderColor = '#10b981';
                    setTimeout(() => { input.style.borderColor = ''; }, 1000);
                } else {
                    input.value = savedValue; // revert on failure
                }
            })
            .catch(() => { input.value = savedValue; });
        }, 700);
    });
});
</script>
@vite(['resources/js/sidebar.js'])
</body>
</html>