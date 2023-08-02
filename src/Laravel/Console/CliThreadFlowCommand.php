<?php

namespace SequentSoft\ThreadFlow\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use SequentSoft\ThreadFlow\Channel\Incoming\CliIncomingChannel;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Contracts\BotInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\IncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\OutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Session\SessionInterface;
use SequentSoft\ThreadFlow\DataFetchers\InvokableDataFetcher;
use SequentSoft\ThreadFlow\Dispatcher\SyncIncomingDispatcher;

class CliThreadFlowCommand extends Command
{
    protected $signature = 'thread-flow:cli {--channel=cli} {--participant-id=1} {--room-id=1}';

    protected $description = 'Starts ThreadFlow CLI';

    protected array $lastKeyboardOptions = [];

    protected function processOutgoing(
        OutgoingMessageInterface $message
    ): OutgoingMessageInterface {
        $this->lastKeyboardOptions = [];

        if ($message instanceof OutgoingRegularMessageInterface) {
            $this->comment('[BOT ANSWER]:');
            $this->line($message->getText());

            if ($message->getKeyboard()) {
                $data = [];
                $rows = $message->getKeyboard()->getRows();
                foreach ($rows as $rowIndex => $row) {
                    $buttons = $row->getButtons();
                    foreach ($buttons as $button) {
                        $this->lastKeyboardOptions[] = $button->getCallbackData();
                        $data[$rowIndex][] = '<comment>' . $button->getCallbackData(
                        ) . '</comment>: ' . $button->getText();
                    }
                }

                $this->table(['Keyboard'], $data);
            }

            $this->newLine();
        }

        return $message;
    }

    protected function inputTextFromUser(): string
    {
        if ($this->lastKeyboardOptions && function_exists('Laravel\Prompts\suggest')) {
            return \Laravel\Prompts\suggest(
                'Message or keyboard button',
                $this->lastKeyboardOptions,
            );
        }

        if (function_exists('Laravel\Prompts\text')) {
            return \Laravel\Prompts\text(
                'Message',
                'Enter message text'
            );
        }

        return $this->anticipate('Enter message text', $this->lastKeyboardOptions);
    }

    /**
     * Handles the console command.
     */
    public function handle(): void
    {
        $channelName = $this->option('channel');

        $config = app(BotInterface::class)->getChannelConfig($channelName);

        $messageContext = MessageContext::createFromIds(
            $this->option('participant-id'),
            $this->option('room-id'),
        );

        $incomingChannel = new CliIncomingChannel($messageContext, $config);

        $dispatcher = new SyncIncomingDispatcher(
            app(BotInterface::class),
        );

        $dataFetcher = new InvokableDataFetcher();

        $this->output->title('ThreadFlow Cli');

        $incomingChannel->listen(
            $dataFetcher,
            function (IncomingMessageInterface $message) use ($channelName, $dispatcher) {
                $outgoingCallback = fn(OutgoingMessageInterface $message) => $this->processOutgoing($message);

                $dispatcher->dispatch(
                    channelName: $channelName,
                    message: $message,
                    outgoingCallback: $outgoingCallback,
                );
            }
        );

        while (true) {
            $dataFetcher([
                'id' => Str::uuid(),
                'text' => $this->inputTextFromUser(),
            ]);
        }
    }
}
