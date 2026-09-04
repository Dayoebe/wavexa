<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';

    public string $role = 'all';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function setAdmin(int $userId, bool $isAdmin): void
    {
        $user = User::query()->findOrFail($userId);

        if (! $isAdmin && $user->is(Auth::user())) {
            $this->addError('role', 'You cannot remove your own administrator access.');

            return;
        }

        if (! $isAdmin && $user->is_admin && User::query()->where('is_admin', true)->count() <= 1) {
            $this->addError('role', 'Wavexa must always have at least one administrator.');

            return;
        }

        $user->forceFill(['is_admin' => $isAdmin])->save();
        session()->flash('status', $isAdmin ? $user->name.' is now an administrator.' : $user->name.' is now a listener.');
    }

    public function render(): View
    {
        $users = User::query()
            ->withCount(['favoriteMedia', 'playbackHistory'])
            ->when($this->q !== '', fn ($query) => $query->where(fn ($nested) => $nested->where('name', 'like', '%'.$this->q.'%')->orWhere('email', 'like', '%'.$this->q.'%')))
            ->when($this->role === 'admins', fn ($query) => $query->where('is_admin', true))
            ->when($this->role === 'listeners', fn ($query) => $query->where('is_admin', false))
            ->orderByDesc('is_admin')->latest()->paginate(20);

        return view('livewire.admin.users.index', compact('users'))->layoutData(['title' => 'Users']);
    }
}
