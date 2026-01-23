<?php

namespace App\Livewire\Public\Calls;

use App\Models\Call;
use App\Models\NewsPost;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    /**
     * The call being displayed.
     */
    public Call $call;

    /**
     * Mount the component.
     */
    public function mount(Call $call): void
    {
        // Only show published calls with status 'abierta' or 'cerrada'
        if (! in_array($call->status, ['abierta', 'cerrada']) || ! $call->published_at) {
            abort(404);
        }

        // Eager load relationships to avoid N+1 queries
        $this->call = $call->load([
            'program',
            'academicYear',
            'phases' => fn ($q) => $q->orderBy('order'),
            'resolutions' => fn ($q) => $q->whereNotNull('published_at')
                ->with('callPhase')
                ->orderBy('official_date', 'desc')
                ->orderBy('published_at', 'desc'),
        ]);
    }

    /**
     * Get the call configuration (colors, icon) based on status and program.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function callConfig(): array
    {
        $status = $this->call->status;
        $programCode = $this->call->program->code ?? '';

        return match ($status) {
            'abierta' => [
                'icon' => 'check-circle',
                'color' => 'emerald',
                'gradient' => 'from-emerald-500 to-emerald-600',
                'gradientDark' => 'from-emerald-600 to-emerald-800',
                'bgLight' => 'bg-emerald-50 dark:bg-emerald-900/20',
                'textColor' => 'text-emerald-600 dark:text-emerald-400',
                'badgeColor' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                'statusLabel' => __('Abierta'),
            ],
            'cerrada' => [
                'icon' => 'x-circle',
                'color' => 'red',
                'gradient' => 'from-red-500 to-red-600',
                'gradientDark' => 'from-red-600 to-red-800',
                'bgLight' => 'bg-red-50 dark:bg-red-900/20',
                'textColor' => 'text-red-600 dark:text-red-400',
                'badgeColor' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                'statusLabel' => __('Cerrada'),
            ],
            default => [
                'icon' => 'clock',
                'color' => 'zinc',
                'gradient' => 'from-zinc-500 to-zinc-600',
                'gradientDark' => 'from-zinc-600 to-zinc-800',
                'bgLight' => 'bg-zinc-50 dark:bg-zinc-900/20',
                'textColor' => 'text-zinc-600 dark:text-zinc-400',
                'badgeColor' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-900/30 dark:text-zinc-300',
                'statusLabel' => __('En proceso'),
            ],
        };
    }

    /**
     * Get current phases for this call (from eager-loaded relation).
     *
     * @return Collection<int, CallPhase>
     */
    #[Computed]
    public function currentPhases(): Collection
    {
        // Use the pre-loaded relation (already ordered by 'order' in mount)
        return $this->call->phases;
    }

    /**
     * Get published resolutions for this call (from eager-loaded relation).
     *
     * @return Collection<int, Resolution>
     */
    #[Computed]
    public function publishedResolutions(): Collection
    {
        // Use the pre-loaded relation (already filtered and ordered in mount)
        return $this->call->resolutions;
    }

    /**
     * Get related news posts for this call's program.
     *
     * @return Collection<int, NewsPost>
     */
    #[Computed]
    public function relatedNews(): Collection
    {
        return NewsPost::query()
            ->with(['program', 'author'])
            ->where('program_id', $this->call->program_id)
            ->where('status', 'publicado')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Get other calls from the same program.
     *
     * @return Collection<int, Call>
     */
    #[Computed]
    public function otherCalls(): Collection
    {
        return Call::query()
            ->with(['program', 'academicYear'])
            ->where('id', '!=', $this->call->id)
            ->where('program_id', $this->call->program_id)
            ->whereIn('status', ['abierta', 'cerrada'])
            ->whereNotNull('published_at')
            ->orderByRaw("CASE WHEN status = 'abierta' THEN 0 ELSE 1 END")
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.calls.show')
            ->layout('components.layouts.public', [
                'title' => $this->call->title.' - Convocatorias Erasmus+',
                'description' => $this->call->requirements ? Str::limit(strip_tags($this->call->requirements), 160) : __('Convocatoria de movilidad internacional Erasmus+.'),
            ]);
    }
}
