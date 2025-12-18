<?php

namespace App\Livewire\Public;

use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class Home extends Component
{
    /**
     * Active programs to display.
     *
     * @var Collection<int, Program>
     */
    public Collection $programs;

    /**
     * Open calls to display.
     *
     * @var Collection<int, Call>
     */
    public Collection $calls;

    /**
     * Recent news posts to display.
     *
     * @var Collection<int, NewsPost>
     */
    public Collection $news;

    /**
     * Upcoming events to display.
     *
     * @var Collection<int, ErasmusEvent>
     */
    public Collection $events;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->loadPrograms();
        $this->loadCalls();
        $this->loadNews();
        $this->loadEvents();
    }

    /**
     * Load active programs.
     */
    protected function loadPrograms(): void
    {
        $this->programs = Program::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->limit(6)
            ->get();
    }

    /**
     * Load open calls.
     */
    protected function loadCalls(): void
    {
        $this->calls = Call::query()
            ->with(['program', 'academicYear'])
            ->where('status', 'abierta')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->limit(4)
            ->get();
    }

    /**
     * Load recent news posts.
     */
    protected function loadNews(): void
    {
        $this->news = NewsPost::query()
            ->with(['program', 'author'])
            ->where('status', 'publicado')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Load upcoming events.
     */
    protected function loadEvents(): void
    {
        $this->events = ErasmusEvent::query()
            ->with(['program', 'call'])
            ->where('is_public', true)
            ->where('start_date', '>=', now()->startOfDay())
            ->orderBy('start_date')
            ->limit(5)
            ->get();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.home')
            ->layout('components.layouts.public', [
                'title' => __('Erasmus+ - Movilidad Internacional'),
                'description' => __('Portal de gestión de movilidades Erasmus+ para alumnado y personal docente. Descubre convocatorias, programas y oportunidades de movilidad internacional.'),
            ]);
    }
}
