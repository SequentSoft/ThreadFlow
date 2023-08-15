<?php

namespace SequentSoft\ThreadFlow\Session;

use Closure;
use Exception;
use RuntimeException;
use SequentSoft\ThreadFlow\Contracts\Session\BackgroundPageStatesCollectionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\BreadcrumbsCollectionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionDataInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class Session implements SessionInterface
{
    protected bool $isClosed = false;

    protected SessionDataInterface $data;

    protected PageStateInterface $pageState;

    protected BackgroundPageStatesCollectionInterface $backgroundPageStates;

    protected BreadcrumbsCollectionInterface $breadcrumbs;

    /**
     * @throws Exception
     */
    final public function __construct(
        SessionDataInterface|array $data = [],
        PageStateInterface|string|null $pageState = null,
        BackgroundPageStatesCollectionInterface|array $backgroundPageStates = [],
        BreadcrumbsCollectionInterface|array $breadcrumbs = [],
        protected ?Closure $saveCallback = null,
        protected ?Closure $closedCallback = null,
    ) {
        $this->data = $data instanceof SessionDataInterface
            ? $data
            : SessionData::create($data);

        $this->pageState = $pageState instanceof PageStateInterface
            ? $pageState
            : PageState::create($pageState);

        $this->backgroundPageStates = $backgroundPageStates instanceof BackgroundPageStatesCollectionInterface
            ? $backgroundPageStates
            : BackgroundPageStatesCollection::create($backgroundPageStates);

        $this->breadcrumbs = $breadcrumbs instanceof BreadcrumbsCollectionInterface
            ? $breadcrumbs
            : BreadcrumbsCollection::create($breadcrumbs);
    }

    public function setSaveCallback(Closure $callback): void
    {
        $this->saveCallback = $callback;
    }

    public function setClosedCallback(Closure $callback): void
    {
        $this->closedCallback = $callback;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close(): void
    {
        if ($this->isClosed) {
            return;
        }

        $this->isClosed = true;

        if ($this->closedCallback) {
            call_user_func($this->closedCallback, $this);
        }
    }

    public function getData(): SessionDataInterface
    {
        return $this->data;
    }

    public function getPageState(): PageStateInterface
    {
        return $this->pageState;
    }

    public function setPageState(PageStateInterface $pageState): void
    {
        $this->pageState = $pageState;
    }

    public function getBackgroundPageStates(): BackgroundPageStatesCollectionInterface
    {
        return $this->backgroundPageStates;
    }

    public function getBreadcrumbs(): BreadcrumbsCollectionInterface
    {
        return $this->breadcrumbs;
    }

    public function delete(string $key): void
    {
        $this->data->delete($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data->get($key, $default);
    }

    public function set(string $key, mixed $data): void
    {
        $this->data->set($key, $data);
    }

    public function save(): void
    {
        if ($this->isClosed) {
            throw new RuntimeException('Session is closed.');
        }

        if ($this->saveCallback) {
            call_user_func($this->saveCallback, $this);
        }

        $this->close();
    }

    public function __serialize(): array
    {
        return [
            'data' => $this->data,
            'pageState' => $this->pageState,
            'backgroundPageStates' => $this->backgroundPageStates,
            'breadcrumbs' => $this->breadcrumbs,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->data = $data['data'];
        $this->pageState = $data['pageState'];
        $this->backgroundPageStates = $data['backgroundPageStates'];
        $this->breadcrumbs = $data['breadcrumbs'];
        $this->closedCallback = null;
        $this->saveCallback = null;
    }
}
