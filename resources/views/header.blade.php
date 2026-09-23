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
{{-- The header queries this wrapper's width rather than the window's: a page
     with the sidebar open can be 1100px of window and 800px of content, and a
     media query cannot tell those apart. --}}
<div class="fi-hs-header-ctn">
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
</div>
