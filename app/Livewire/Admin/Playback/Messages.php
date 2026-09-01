<?php
namespace App\Livewire\Admin\Playback;
use App\Models\PlaybackMessage; use Illuminate\View\View; use Livewire\Attributes\Layout; use Livewire\Component;
#[Layout('layouts.dashboard')]
class Messages extends Component
{
    public array $messages=[];
    public function mount(): void { $this->messages=PlaybackMessage::orderBy('label')->get()->mapWithKeys(fn($item)=>[$item->id=>['message'=>$item->message,'is_active'=>$item->is_active]])->all(); }
    public function save(): void { $this->validate(['messages'=>['array'],'messages.*.message'=>['required','string','max:500'],'messages.*.is_active'=>['boolean']]); foreach($this->messages as $id=>$data) PlaybackMessage::findOrFail($id)->update($data); session()->flash('status','Playback messages updated.'); }
    public function render(): View { $records=PlaybackMessage::orderBy('label')->get(); return view('livewire.admin.playback.messages',compact('records'))->layoutData(['title'=>'Playback messages']); }
}
