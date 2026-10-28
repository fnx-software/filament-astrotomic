<?php

declare(strict_types=1);

namespace Fnxsoftware\FilamentAstrotomic\Schemas\Components\Concerns;

use Closure;
use Filament\Actions\Action;
use Illuminate\Support\Arr;

trait HasHeaderActions
{
    /**
     * @var array<Action> | null
     */
    protected ?array $cachedPrefixActions = null;

    /**
     * @var array<Action | Closure>
     */
    protected array $prefixActions = [];

    /**
     * @var array<Action> | null
     */
    protected ?array $cachedSuffixActions = null;

    /**
     * @var array<Action | Closure>
     */
    protected array $suffixActions = [];

    public function prefixAction(Action | Closure $action): static
    {
        return $this->prefixActions([$action]);
    }

    /**
     * @param  array<Action | Closure>  $actions
     */
    public function prefixActions(array $actions): static
    {
        $this->prefixActions = [
            ...$this->prefixActions,
            ...$actions,
        ];

        $this->cachedPrefixActions = null;

        return $this;
    }

    public function suffixAction(Action | Closure $action): static
    {
        return $this->suffixActions([$action]);
    }

    /**
     * @param  array<Action | Closure>  $actions
     */
    public function suffixActions(array $actions): static
    {
        $this->suffixActions = [
            ...$this->suffixActions,
            ...$actions,
        ];

        $this->cachedSuffixActions = null;

        return $this;
    }

    /**
     * @return array<Action>
     */
    public function getPrefixActions(): array
    {
        return $this->cachedPrefixActions ??= $this->cachePrefixActions();
    }

    /**
     * @return array<Action>
     */
    public function cachePrefixActions(): array
    {
        $this->cachedPrefixActions = [];

        foreach ($this->prefixActions as $prefixAction) {
            foreach (Arr::wrap($this->evaluate($prefixAction)) as $action) {
                if (! $action instanceof Action) {
                    continue;
                }

                $this->cachedPrefixActions[$action->getName()] = $this->prepareAction($action);
            }
        }

        return $this->cachedPrefixActions;
    }

    /**
     * @return array<Action>
     */
    public function getSuffixActions(): array
    {
        return $this->cachedSuffixActions ??= $this->cacheSuffixActions();
    }

    /**
     * @return array<Action>
     */
    public function cacheSuffixActions(): array
    {
        $this->cachedSuffixActions = [];

        foreach ($this->suffixActions as $suffixAction) {
            foreach (Arr::wrap($this->evaluate($suffixAction)) as $action) {
                if (! $action instanceof Action) {
                    continue;
                }

                $this->cachedSuffixActions[$action->getName()] = $this->prepareAction($action);
            }
        }

        return $this->cachedSuffixActions;
    }
}
