<?php

namespace App\Livewire\Language;

use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Switcher extends Component
{
    /**
     * Variant of the switcher: 'dropdown', 'buttons', 'select'.
     */
    public string $variant = 'dropdown';

    /**
     * Size of the switcher: 'sm', 'md', 'lg'.
     */
    public string $size = 'md';

    /**
     * Get available languages.
     *
     * @return Collection<int, Language>
     */
    #[Computed]
    public function availableLanguages(): Collection
    {
        return getAvailableLanguages();
    }

    /**
     * Get current language.
     */
    #[Computed]
    public function currentLanguage(): ?Language
    {
        return getCurrentLanguage();
    }

    /**
     * Get current language code.
     */
    #[Computed]
    public function currentLanguageCode(): string
    {
        return getCurrentLanguageCode();
    }

    /**
     * Switch language.
     *
     * @param  string  $code  Language code
     * @return void
     */
    public function switchLanguage(string $code): void
    {
        // Validar que el idioma existe y está activo
        if (! isLanguageAvailable($code)) {
            $this->dispatch('language-error', message: __('El idioma seleccionado no está disponible.'));
            
            return;
        }

        // Establecer el idioma
        if (! setLanguage($code, persist: true)) {
            $this->dispatch('language-error', message: __('Error al cambiar el idioma.'));
            
            return;
        }

        // Establecer cookie para persistencia entre sesiones
        cookie()->queue('locale', $code, 60 * 24 * 365); // 1 año

        // Disparar evento para notificar el cambio
        $this->dispatch('language-changed', code: $code);

        // Redirigir a la misma página para aplicar el cambio
        $this->redirect(request()->header('Referer') ?: route('home'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.language.switcher');
    }
}

