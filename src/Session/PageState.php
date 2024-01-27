<?php

namespace SequentSoft\ThreadFlow\Session;

use DateTimeImmutable;
use Exception;
use SequentSoft\ThreadFlow\Contracts\Session\PageStateInterface;

class PageState implements PageStateInterface
{
    final public function __construct(
        protected string $id,
        protected ?string $pageClass = null,
        protected array $attributes = [],
        protected ?DateTimeImmutable $dontDisturbMarkedAt = null,
    ) {
    }

    public function getDontDisturbMarkedAt(): ?DateTimeImmutable
    {
        return $this->dontDisturbMarkedAt;
    }

    public function setDontDisturb(bool $dontDisturb = true): void
    {
        if ($dontDisturb) {
            $this->dontDisturbMarkedAt = new DateTimeImmutable();
        } else {
            $this->dontDisturbMarkedAt = null;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPageClass(): ?string
    {
        return $this->pageClass;
    }

    public function setPageClass(string $pageClass): void
    {
        $this->pageClass = $pageClass;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @throws Exception
     */
    public static function create(
        ?string $pageClass = null,
        array $attributes = [],
    ): PageStateInterface {
        return new static(
            md5(random_bytes(32)),
            $pageClass,
            $attributes,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'pageClass' => $this->pageClass,
            'attributes' => $this->attributes,
            'dontDisturbMarkedAt' => $this->dontDisturbMarkedAt?->getTimestamp(),
        ];
    }

    public function fromArray(array $data): self
    {
        $this->id = $data['id'];
        $this->pageClass = $data['pageClass'];
        $this->attributes = $data['attributes'];

        if (isset($data['dontDisturbMarkedAt']) && is_int($data['dontDisturbMarkedAt'])) {
            $this->dontDisturbMarkedAt = (new DateTimeImmutable())->setTimestamp($data['dontDisturbMarkedAt']);
        } else {
            $this->dontDisturbMarkedAt = null;
        }

        return $this;
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        $this->fromArray($data);
    }
}
