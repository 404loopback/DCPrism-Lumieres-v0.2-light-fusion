<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'livewire' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'livewire' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $renderHookScopes = $livewire?->getRenderHookScopes();
?>

<!DOCTYPE html>
<html
    lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"
    dir="<?php echo e(__('filament-panels::layout.direction') ?? 'ltr'); ?>"
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'fi',
        'dark' => filament()->hasDarkModeForced(),
    ]); ?>"
>
    <head>
        <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_START, scopes: $renderHookScopes)); ?>


        <meta charset="utf-8" />
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <?php if($favicon = filament()->getFavicon()): ?>
            <link rel="icon" href="<?php echo e($favicon); ?>" />
        <?php endif; ?>

        <?php
            $title = trim(strip_tags($livewire?->getTitle() ?? ''));
            $brandName = trim(strip_tags(filament()->getBrandName()));
        ?>

        <title>
            <?php echo e(filled($title) ? "{$title} - " : null); ?> <?php echo e($brandName); ?>

        </title>

        <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::STYLES_BEFORE, scopes: $renderHookScopes)); ?>


        <style>
            [x-cloak=''],
            [x-cloak='x-cloak'],
            [x-cloak='1'] {
                display: none !important;
            }

            [x-cloak='inline-flex'] {
                display: inline-flex !important;
            }

            @media (max-width: 1023px) {
                [x-cloak='-lg'] {
                    display: none !important;
                }
            }

            @media (min-width: 1024px) {
                [x-cloak='lg'] {
                    display: none !important;
                }
            }
        </style>

        <?php echo \Filament\Support\Facades\FilamentAsset::renderStyles() ?>

        <?php echo e(filament()->getTheme()->getHtml()); ?>

        <?php echo e(filament()->getFontHtml()); ?>

        <?php echo e(filament()->getMonoFontHtml()); ?>

        <?php echo e(filament()->getSerifFontHtml()); ?>


        <style>
            :root {
                --font-family: '<?php echo filament()->getFontFamily(); ?>';
                --mono-font-family: '<?php echo filament()->getMonoFontFamily(); ?>';
                --serif-font-family: '<?php echo filament()->getSerifFontFamily(); ?>';
                --sidebar-width: <?php echo e(filament()->getSidebarWidth()); ?>;
                --collapsed-sidebar-width: <?php echo e(filament()->getCollapsedSidebarWidth()); ?>;
                --default-theme-mode: <?php echo e(filament()->getDefaultThemeMode()->value); ?>;
            }
        </style>

        <?php echo $__env->yieldPushContent('styles'); ?>

        <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::STYLES_AFTER, scopes: $renderHookScopes)); ?>


        <?php if(! filament()->hasDarkMode()): ?>
            <script>
                localStorage.setItem('theme', 'light')
            </script>
        <?php elseif(filament()->hasDarkModeForced()): ?>
            <script>
                localStorage.setItem('theme', 'dark')
            </script>
        <?php else: ?>
            <script>
                const loadDarkMode = () => {
                    window.theme = localStorage.getItem('theme') ?? <?php echo \Illuminate\Support\Js::from(filament()->getDefaultThemeMode()->value)->toHtml() ?>

                    if (
                        window.theme === 'dark' ||
                        (window.theme === 'system' &&
                            window.matchMedia('(prefers-color-scheme: dark)')
                                .matches)
                    ) {
                        document.documentElement.classList.add('dark')
                    }
                }

                loadDarkMode()

                document.addEventListener('livewire:navigated', loadDarkMode)
            </script>
        <?php endif; ?>

        <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::HEAD_END, scopes: $renderHookScopes)); ?>

    </head>

    <body
        <?php echo e($attributes
                ->merge($livewire?->getExtraBodyAttributes() ?? [], escape: false)
                ->class([
                    'fi-body',
                    'fi-panel-' . filament()->getId(),
                ])); ?>

    >
        <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_START, scopes: $renderHookScopes)); ?>


        <?php echo e($slot); ?>


        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split(Filament\Livewire\Notifications::class);

$__html = app('livewire')->mount($__name, $__params, 'lw-2854695384-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

        <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_BEFORE, scopes: $renderHookScopes)); ?>


        <?php echo \Filament\Support\Facades\FilamentAsset::renderScripts(withCore: true) ?>

        <?php if(filament()->hasBroadcasting() && config('filament.broadcasting.echo')): ?>
            <script data-navigate-once>
                window.Echo = new window.EchoFactory(<?php echo \Illuminate\Support\Js::from(config('filament.broadcasting.echo'))->toHtml() ?>)

                window.dispatchEvent(new CustomEvent('EchoLoaded'))
            </script>
        <?php endif; ?>

        <?php if(filament()->hasDarkMode() && (! filament()->hasDarkModeForced())): ?>
            <script>
                loadDarkMode()
            </script>
        <?php endif; ?>

        <?php echo $__env->yieldPushContent('scripts'); ?>

        <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SCRIPTS_AFTER, scopes: $renderHookScopes)); ?>


        <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::BODY_END, scopes: $renderHookScopes)); ?>

        
        <!-- ENHANCED LOOP PREVENTION SCRIPT -->
        <script>
            console.log('🚺 Enhanced Loop Prevention loaded');
            
            const original = window.history.replaceState;
            let lastUrl = null;
            let sameUrlCount = 0;
            let totalBlocked = 0;
            
            window.history.replaceState = function(state, title, url) {
                // Prévenir les boucles infinies
                if (url === lastUrl) {
                    sameUrlCount++;
                    if (sameUrlCount > 3) {
                        totalBlocked++;
                        console.group('🚨 LOOP PREVENTION: Blocking replaceState loop');
                        console.log('URL:', url);
                        console.log('State:', state);
                        console.log('Title:', title);
                        console.log('Total blocked:', totalBlocked);
                        console.log('Current location:', window.location.href);
                        console.log('URL params:', new URLSearchParams(window.location.search).toString());
                        console.trace('Stack trace:');
                        console.groupEnd();
                        
                        // Après 50 blocages, essayer de nettoyer l'URL
                        if (totalBlocked === 50) {
                            console.warn('🧼 Attempting to clean URL after 50 blocks...');
                            const cleanUrl = window.location.pathname;
                            window.history.pushState(null, '', cleanUrl);
                        }
                        
                        return; // BLOQUER l'appel
                    }
                } else {
                    sameUrlCount = 0;
                    lastUrl = url;
                }
                
                return original.apply(this, arguments);
            };
            
            // Détecter les paramètres suspects dans l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const suspiciousParams = [];
            
            for (const [key, value] of urlParams) {
                if (key === 's' || key === 'record' || value === 'arr' || value === 'null') {
                    suspiciousParams.push({key, value});
                }
            }
            
            if (suspiciousParams.length > 0) {
                console.warn('⚠️ Suspicious URL parameters detected:', suspiciousParams);
                console.log('Full URL:', window.location.href);
            }
            
            console.log('✅ Enhanced loop prevention active');
        </script>
    </body>
</html>
<?php /**PATH /var/www/resources/views/vendor/filament-panels/components/layout/base.blade.php ENDPATH**/ ?>