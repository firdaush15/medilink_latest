{{-- resources/views/admin/admin_articleManagementShow.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink - Article Preview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/sidebar.css', 'resources/css/admin/admin_articleManagementShow.css'])
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
                <h2>Article Preview</h2>
                <p>ID #{{ $article->article_id }}</p>
            </div>
        </div>
        @if(!$article->trashed())
        <div class="header-actions">
            <a href="{{ route('admin.articles.edit', $article->article_id) }}" class="btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form method="POST"
                  action="{{ route('admin.articles.destroy', $article->article_id) }}"
                  onsubmit="return confirm('Delete this article?')"
                  style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
        @endif
    </div>

    <div class="show-layout">

        {{-- ── Main Content ── --}}
        <div class="show-main">

            {{-- Cover Image --}}
            @if($article->image_url)
            <div class="cover-wrap">
                <img src="{{ $article->image_url }}"
                     alt="{{ $article->title }}"
                     onerror="this.style.display='none'">
            </div>
            @endif

            {{-- Title --}}
            <h1 class="article-title">{{ $article->title }}</h1>

            {{-- Meta row --}}
            <div class="meta-row">
                @if($article->author)
                    <span><i class="fas fa-user"></i> {{ $article->author }}</span>
                @endif
                @if($article->source_name)
                    <span><i class="fas fa-globe"></i> {{ $article->source_name }}</span>
                @endif
                @if($article->published_at)
                    <span><i class="fas fa-calendar"></i> {{ $article->published_at->format('d M Y') }}</span>
                @endif
                <span><i class="fas fa-eye"></i> {{ number_format($article->view_count) }} views</span>
            </div>

            {{-- Summary --}}
            @if($article->summary)
            <div class="summary-box">
                <strong>Summary:</strong> {{ $article->summary }}
            </div>
            @endif

            {{-- Full Content --}}
            @if($article->content)
            <div class="section content-box">
                <p class="content-label">FULL CONTENT</p>
                <div class="article-content">{{ $article->content }}</div>
            </div>
            @else
            <div class="empty-content">
                <i class="fas fa-file-alt"></i>
                <p>No full content saved for this article.</p>
            </div>
            @endif

        </div>

        {{-- ── Sidebar ── --}}
        <div class="show-sidebar">

            {{-- Status Card --}}
            <div class="section">
                <h3>Status</h3>
                <div class="badge-row">
                    <span class="badge {{ $article->is_published ? 'badge-success' : 'badge-secondary' }}">
                        {{ $article->is_published ? '✅ Published' : '⛔ Unpublished' }}
                    </span>
                    @if($article->is_featured)
                        <span class="badge badge-warning">⭐ Featured</span>
                    @endif
                    @if($article->trashed())
                        <span class="badge badge-danger">🗑 Deleted</span>
                    @endif
                </div>
                <hr>
                <div class="info-row">
                    <span>Category</span>
                    <span class="badge-category">{{ $article->category }}</span>
                </div>
                <div class="info-row">
                    <span>Sort Order</span>
                    <strong>{{ $article->sort_order }}</strong>
                </div>
                <div class="info-row">
                    <span>Created</span>
                    <span>{{ $article->created_at->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span>Last Updated</span>
                    <span>{{ $article->updated_at->diffForHumans() }}</span>
                </div>
            </div>

            {{-- Source Link --}}
            @if($article->source_url)
            <div class="section">
                <h3>Original Source</h3>
                <a href="{{ $article->source_url }}" target="_blank" class="btn-outline btn-full">
                    <i class="fas fa-external-link-alt"></i> Visit Original Article
                </a>
            </div>
            @endif

            {{-- Restore if deleted --}}
            @if($article->trashed())
            <form method="POST"
                  action="{{ route('admin.articles.restore', $article->article_id) }}">
                @csrf
                <button type="submit" class="btn-success btn-full">
                    <i class="fas fa-undo"></i> Restore Article
                </button>
            </form>
            @endif

        </div>
    </div>

</div>

@vite(['resources/js/sidebar.js'])
</body>
</html>