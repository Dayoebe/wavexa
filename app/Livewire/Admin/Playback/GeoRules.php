<?php
namespace App\Livewire\Admin\Playback;
use App\Models\Country; use App\Models\StreamGeoRule; use App\Models\StreamSource; use Illuminate\Database\Eloquent\Builder; use Illuminate\View\View; use Livewire\Attributes\Layout; use Livewire\Component; use Livewire\WithPagination;
#[Layout('layouts.dashboard')]
class GeoRules extends Component
{
    use WithPagination;
    public string $q=''; public string $streamId=''; public string $countryId=''; public string $mode='blocked';
    public function updatedQ(): void { $this->resetPage(); }
    public function save(): void { $data=$this->validate(['streamId'=>['required','exists:stream_sources,id'],'countryId'=>['required','exists:countries,id'],'mode'=>['required','in:blocked,allowed']]); StreamGeoRule::updateOrCreate(['stream_source_id'=>$data['streamId'],'country_id'=>$data['countryId']],['mode'=>$data['mode']]); $this->reset('streamId','countryId'); }
    public function delete(int $id): void { StreamGeoRule::findOrFail($id)->delete(); }
    public function render(): View { $rules=StreamGeoRule::with(['streamSource.media','country'])->latest()->paginate(30); $streams=StreamSource::query()->when($this->q,fn(Builder $query)=>$query->whereHas('media',fn(Builder $media)=>$media->where('name','like','%'.$this->q.'%')))->with('media')->limit(50)->get(); $countries=Country::orderBy('name')->get(['id','name','iso_alpha_2']); return view('livewire.admin.playback.geo-rules',compact('rules','streams','countries'))->layoutData(['title'=>'Geoblocking']); }
}
