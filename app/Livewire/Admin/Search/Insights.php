<?php

namespace App\Livewire\Admin\Search;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Media;
use App\Models\SearchQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Insights extends Component
{
    use WithPagination;

    public string $kind = 'index';

    #[Url(except: '')]
    public string $q = '';

    public function mount(?string $kind = null): void
    {
        abort_unless(in_array($kind, ['index', 'popular', 'no-results'], true), 404);
        $this->kind = $kind;
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $mediaCounts = Media::query()->where('status', MediaStatus::Published)->whereIn('type', [MediaType::Radio, MediaType::Television, MediaType::Podcast])->selectRaw('type,count(*) as total')->groupBy('type')->pluck('total', 'type');
        $searches = null;
        if ($this->kind !== 'index') {
            $searches = SearchQuery::query()->when($this->kind === 'no-results', fn (Builder $query) => $query->where('results_count', 0))->when($this->kind === 'popular', fn (Builder $query) => $query->where('results_count', '>', 0))->when($this->q, fn (Builder $query) => $query->where('normalized_query', 'like', '%'.strtolower($this->q).'%'))
                ->selectRaw('normalized_query, MAX(query) as display_query, COUNT(*) as searches, ROUND(AVG(results_count)) as average_results, MAX(searched_at) as last_searched_at')->groupBy('normalized_query')->orderByDesc('searches')->paginate(30);
        }
        $stats = ['searchable_media' => $mediaCounts->sum(), 'searches_30_days' => SearchQuery::where('searched_at', '>=', now()->subDays(30))->count(), 'no_results_30_days' => SearchQuery::where('results_count', 0)->where('searched_at', '>=', now()->subDays(30))->count(), 'unique_terms_30_days' => SearchQuery::where('searched_at', '>=', now()->subDays(30))->distinct('normalized_query')->count('normalized_query')];

        return view('livewire.admin.search.insights', compact('mediaCounts', 'searches', 'stats'))->layoutData(['title' => match ($this->kind) {
            'popular' => 'Popular searches','no-results' => 'No-result searches',default => 'Search index'
        }]);
    }
}
