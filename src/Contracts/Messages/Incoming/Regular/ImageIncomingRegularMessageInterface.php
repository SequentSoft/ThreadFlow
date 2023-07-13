<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface ImageIncomingRegularMessageInterface extends IncomingRegularMessageInterface {
    public function getImageUrl(): string;
    public function getCaption(): ?string;
}
