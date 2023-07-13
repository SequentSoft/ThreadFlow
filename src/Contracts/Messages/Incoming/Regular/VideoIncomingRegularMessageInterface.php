<?php

namespace SequentSoft\ThreadFlow\Contracts\Messages\Incoming\Regular;

interface VideoIncomingRegularMessageInterface extends IncomingRegularMessageInterface {
    public function getVideoUrl(): string;
    public function getCaption(): ?string;
}
