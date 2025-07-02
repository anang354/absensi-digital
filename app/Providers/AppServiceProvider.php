<?php

namespace App\Providers;

//use Filament\Facades\Filament;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Contracts\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'),
            // Js::make('absen-guru', asset('js/absen-guru.js')),
            Css::make('absen-guru', asset('css/absen-guru.css')),
        ]);
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): View => view('components.absen-guru-floating'),
        );
    }
}
