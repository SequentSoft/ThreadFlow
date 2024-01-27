<?php

namespace SequentSoft\ThreadFlow\Session;

use Exception;
use SequentSoft\ThreadFlow\Contracts\Session\BackgroundPageStatesCollectionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\BreadcrumbsCollectionInterface;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionDataInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;

class Session implements SessionInterface
{
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

    /**
     * @throws Exception
     */
    public function reset(): void
    {
        $this->data = SessionData::create();
        $this->pageState = PageState::create();
        $this->backgroundPageStates = BackgroundPageStatesCollection::create();
        $this->breadcrumbs = BreadcrumbsCollection::create();
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

    public function toArray(): array
    {
        return [
            'data' => $this->data->all(),
            'pageState' => $this->pageState->toArray(),
            'backgroundPageStates' => $this->backgroundPageStates->all(),
            'breadcrumbs' => $this->breadcrumbs->all(),
        ];
    }

    public static function fromArray(array $data): SessionInterface
    {
        return new static(
            SessionData::create($data['data']),
            PageState::create()->fromArray($data['pageState']),
            BackgroundPageStatesCollection::create($data['backgroundPageStates']),
            BreadcrumbsCollection::create($data['breadcrumbs']),
        );
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        $this->data = SessionData::create($data['data']);
        $this->pageState = PageState::create()->fromArray($data['pageState']);
        $this->backgroundPageStates = BackgroundPageStatesCollection::create($data['backgroundPageStates']);
        $this->breadcrumbs = BreadcrumbsCollection::create($data['breadcrumbs']);
    }
}
