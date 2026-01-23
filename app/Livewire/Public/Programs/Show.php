<?php

namespace App\Livewire\Public\Programs;

use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    /**
     * The program being displayed.
     */
    public Program $program;

    /**
     * Mount the component.
     */
    public function mount(Program $program): void
    {
        $this->program = $program;
    }

    /**
     * Get the program configuration (colors, icon) based on code.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function programConfig(): array
    {
        $code = $this->program->code ?? '';

        return match (true) {
            // Specific program types first (order matters!)
            str_contains($code, 'VET') => [
                'icon' => 'briefcase',
                'color' => 'emerald',
                'gradient' => 'from-emerald-500 to-emerald-600',
                'gradientDark' => 'from-emerald-600 to-emerald-800',
                'bgLight' => 'bg-emerald-50 dark:bg-emerald-900/20',
                'textColor' => 'text-emerald-600 dark:text-emerald-400',
                'badgeColor' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                'type' => __('Formación Profesional'),
            ],
            str_contains($code, 'HED') => [
                'icon' => 'building-library',
                'color' => 'violet',
                'gradient' => 'from-violet-500 to-violet-600',
                'gradientDark' => 'from-violet-600 to-violet-800',
                'bgLight' => 'bg-violet-50 dark:bg-violet-900/20',
                'textColor' => 'text-violet-600 dark:text-violet-400',
                'badgeColor' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
                'type' => __('Educación Superior'),
            ],
            str_contains($code, 'SCH') => [
                'icon' => 'academic-cap',
                'color' => 'blue',
                'gradient' => 'from-blue-500 to-blue-600',
                'gradientDark' => 'from-blue-600 to-blue-800',
                'bgLight' => 'bg-blue-50 dark:bg-blue-900/20',
                'textColor' => 'text-blue-600 dark:text-blue-400',
                'badgeColor' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                'type' => __('Educación Escolar'),
            ],
            str_contains($code, 'ADU') => [
                'icon' => 'users',
                'color' => 'teal',
                'gradient' => 'from-teal-500 to-teal-600',
                'gradientDark' => 'from-teal-600 to-teal-800',
                'bgLight' => 'bg-teal-50 dark:bg-teal-900/20',
                'textColor' => 'text-teal-600 dark:text-teal-400',
                'badgeColor' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300',
                'type' => __('Educación de Adultos'),
            ],
            // General KA types (after specific subtypes)
            str_contains($code, 'KA1') => [
                'icon' => 'academic-cap',
                'color' => 'blue',
                'gradient' => 'from-blue-500 to-blue-600',
                'gradientDark' => 'from-blue-600 to-blue-800',
                'bgLight' => 'bg-blue-50 dark:bg-blue-900/20',
                'textColor' => 'text-blue-600 dark:text-blue-400',
                'badgeColor' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                'type' => __('Movilidad'),
            ],
            str_contains($code, 'KA2') => [
                'icon' => 'users',
                'color' => 'amber',
                'gradient' => 'from-amber-500 to-amber-600',
                'gradientDark' => 'from-amber-600 to-amber-800',
                'bgLight' => 'bg-amber-50 dark:bg-amber-900/20',
                'textColor' => 'text-amber-600 dark:text-amber-400',
                'badgeColor' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                'type' => __('Cooperación'),
            ],
            str_contains($code, 'JM') => [
                'icon' => 'building-office-2',
                'color' => 'indigo',
                'gradient' => 'from-indigo-500 to-indigo-600',
                'gradientDark' => 'from-indigo-600 to-indigo-800',
                'bgLight' => 'bg-indigo-50 dark:bg-indigo-900/20',
                'textColor' => 'text-indigo-600 dark:text-indigo-400',
                'badgeColor' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                'type' => __('Jean Monnet'),
            ],
            str_contains($code, 'DISCOVER') => [
                'icon' => 'map',
                'color' => 'rose',
                'gradient' => 'from-rose-500 to-rose-600',
                'gradientDark' => 'from-rose-600 to-rose-800',
                'bgLight' => 'bg-rose-50 dark:bg-rose-900/20',
                'textColor' => 'text-rose-600 dark:text-rose-400',
                'badgeColor' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                'type' => __('DiscoverEU'),
            ],
            default => [
                'icon' => 'globe-europe-africa',
                'color' => 'erasmus',
                'gradient' => 'from-erasmus-500 to-erasmus-600',
                'gradientDark' => 'from-erasmus-600 to-erasmus-800',
                'bgLight' => 'bg-erasmus-50 dark:bg-erasmus-900/20',
                'textColor' => 'text-erasmus-600 dark:text-erasmus-400',
                'badgeColor' => 'bg-erasmus-100 text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300',
                'type' => __('Erasmus+'),
            ],
        };
    }

    /**
     * Get related calls (convocatorias) for this program.
     *
     * @return Collection<int, Call>
     */
    #[Computed]
    public function relatedCalls(): Collection
    {
        return Call::query()
            ->with(['program', 'academicYear'])
            ->where('program_id', $this->program->id)
            ->whereIn('status', ['abierta', 'cerrada'])
            ->whereNotNull('published_at')
            ->orderByRaw("CASE WHEN status = 'abierta' THEN 0 ELSE 1 END")
            ->orderBy('published_at', 'desc')
            ->limit(4)
            ->get();
    }

    /**
     * Get related news posts for this program.
     *
     * @return Collection<int, NewsPost>
     */
    #[Computed]
    public function relatedNews(): Collection
    {
        return NewsPost::query()
            ->with(['program', 'author'])
            ->where('program_id', $this->program->id)
            ->where('status', 'publicado')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Get other active programs to suggest.
     *
     * @return Collection<int, Program>
     */
    #[Computed]
    public function otherPrograms(): Collection
    {
        return Program::query()
            ->where('id', '!=', $this->program->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->limit(3)
            ->get();
    }

    /**
     * Get the program image URL from Media Library.
     */
    #[Computed]
    public function programImage(): ?string
    {
        return $this->program->getFirstMediaUrl('image', 'large')
            ?: $this->program->getFirstMediaUrl('image', 'medium')
            ?: $this->program->getFirstMediaUrl('image');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.programs.show')
            ->layout('components.layouts.public', [
                'title' => $this->program->name.' - Erasmus+',
                'description' => $this->program->description,
                'image' => $this->programImage,
            ]);
    }
}
