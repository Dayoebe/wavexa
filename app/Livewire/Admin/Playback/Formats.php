<?php
namespace App\Livewire\Admin\Playback;
use App\Models\PlaybackFormat;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
#[Layout('layouts.dashboard')]
class Formats extends Component
{
    public string $key=''; public string $label=''; public string $mediaKind='both'; public string $mimeType=''; public bool $usesHls=false;
    public function toggle(int $id): void { $item=PlaybackFormat::findOrFail($id); $item->update(['is_enabled'=>!$item->is_enabled]); }
    public function save(): void { $data=$this->validate(['key'=>['required','alpha_dash','max:32','unique:playback_formats,key'],'label'=>['required','string','max:255'],'mediaKind'=>['required','in:audio,video,both'],'mimeType'=>['nullable','string','max:120'],'usesHls'=>['boolean']]); PlaybackFormat::create(['key'=>strtolower($data['key']),'label'=>$data['label'],'media_kind'=>$data['mediaKind'],'mime_type'=>$data['mimeType']?:null,'uses_hls'=>$data['usesHls'],'is_enabled'=>true]); $this->reset('key','label','mimeType','usesHls'); }
    public function render(): View { $formats=PlaybackFormat::withCount('streamSources')->orderBy('label')->get(); return view('livewire.admin.playback.formats',compact('formats'))->layoutData(['title'=>'Stream formats']); }
}
