<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\View;
use App\Filament\Pages\Auth\LoginActive;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // 1. Tentukan URL favicon default
        $faviconUrl = asset('css/logo_default.png'); // Ganti dengan path ke file default Anda

        // 2. Periksa apakah tabel 'pengaturans' sudah ada di database
        if (\Illuminate\Support\Facades\Schema::hasTable('pengaturans')) {
            // 3. Jika tabel ada, ambil logo dari database
            //    gunakan optional() untuk mencegah error jika tabel kosong
            $logoPath = \App\Models\Pengaturan::first()?->logo_sekolah;

            // 4. Jika logo ditemukan, gunakan path tersebut
            if ($logoPath) {
                $faviconUrl = asset('storage/' . $logoPath);
            }
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(LoginActive::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->userMenuItems([
                MenuItem::make('')
                    ->label('Take Absen')
                    ->icon('heroicon-o-camera')
                    ->url('/admin/guru-scan')
                    ->visible(fn ():bool => auth()->user()->isAdmin() || auth()->user()->isGuru()),
                MenuItem::make('')
                    ->label('Scan Siswa')
                    ->icon('heroicon-o-qr-code')
                    ->url('/admin/scan-siswa')
                    ->visible(fn ():bool => !auth()->user()->isSiswa()),
                MenuItem::make('')
                    ->label('Setoran Hafalan')
                    ->icon('heroicon-o-book-open')
                    ->url('/admin/setor-hafalan')
                    ->visible(fn ():bool => !auth()->user()->isSiswa()),
            ])
            ->favicon($faviconUrl)
            ->databaseNotifications()
            ->maxContentWidth('full')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //Widgets\AccountWidget::class,
                //Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([

            ]);
    }
}
