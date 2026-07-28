@php
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;

    $renderHookScopes = $page->getRenderHookScopes();

    $beforeActions = FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE, scopes: $renderHookScopes);
    $afterActions = FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER, scopes: $renderHookScopes);
@endphp

{{-- Mirrors filament-panels::components.header so third-party render hooks and
     the actions row keep working; only the heading and subheading are replaced
     by the page's header schema. --}}
<header
    @class([
        'fi-header',
        'fi-hs-header',
        'fi-header-has-breadcrumbs' => $breadcrumbs,
    ])
>
    <div class="fi-hs-header-main">
        @if ($breadcrumbs)
            <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
        @endif

        {{ FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE, scopes: $renderHookScopes) }}

        {{ $schema }}

        {{ FilamentView::renderHook(PanelsRenderHook::PAGE_HEADER_HEADING_AFTER, scopes: $renderHookScopes) }}
    </div>

    @if (filled($beforeActions) || $actions || filled($afterActions))
        <div class="fi-header-actions-ctn">
            {{ $beforeActions }}

            @if ($actions)
                <x-filament::actions
                    :actions="$actions"
                    :alignment="$actionsAlignment"
                />
            @endif

            {{ $afterActions }}
        </div>
    @endif
</header>
