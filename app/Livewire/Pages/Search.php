<?php

namespace App\Livewire\Pages;

use App\Enums\MediaStatus;
use App\Enums\MediaType;
use App\Models\Media;
use App\Services\Search\SearchAnalytics;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Search extends Component
{
    use WithPagination;

    #[Url(except: '')] public string $q = '';
    #[Url(except: 'all')] public string $type = 'all';

    public function mount(SearchAnalytics $analytics): void { if (mb_strlen(trim($this->q)) >= 2) $this->track($analytics); }
    public function updatedQ(SearchAnalytics $analytics): void { $this->resetPage(); $this->track($analytics); }
    public function updatedType(): void { $this->resetPage(); }

    public function render(): View
    {
        $results = $this->results()->with(['country','radioStation','tvChannel','podcast','artworks','primaryStream'])->orderBy('name')->paginate(24);
        $counts = collect();
        if (mb_strlen(trim($this->q)) >= 2) $counts=$this->results(false)->selectRaw('type,count(*) as total')->groupBy('type')->pluck('total','type');
        $title = $this->q ? 'Search for '.$this->q.' — Wavexa' : 'Search Global Media — Wavexa';
        $description = 'Search Wavexa radio stations, television channels, and podcasts from one global discovery page.';
        $canonical = route('search');
        return view('livewire.pages.search',compact('results','counts'))->layoutData(compact('title','description','canonical')+['structuredData'=>Seo::schema($title,$description,$canonical)]);
    }

    private function track(SearchAnalytics $analytics): void { $analytics->record($this->q,$this->results(false)->count()); }
    private function results(bool $applyType=true): Builder
    {
        return Media::query()->where('status',MediaStatus::Published)->whereIn('type',[MediaType::Radio,MediaType::Television,MediaType::Podcast])
            ->when(mb_strlen(trim($this->q))<2,fn(Builder $query)=>$query->whereRaw('1 = 0'))
            ->when(mb_strlen(trim($this->q))>=2,fn(Builder $query)=>$query->where(function(Builder $query): void { $term='%'.trim($this->q).'%'; $query->where('name','like',$term)->orWhere('description','like',$term)->orWhereHas('country',fn(Builder $country)=>$country->where('name','like',$term))->orWhereHas('genres',fn(Builder $genre)=>$genre->where('name','like',$term))->orWhereHas('languages',fn(Builder $language)=>$language->where('name','like',$term)); }))
            ->when($applyType && $this->type!=='all',fn(Builder $query)=>$query->where('type',$this->type));
    }
}
