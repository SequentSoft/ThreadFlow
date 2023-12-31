<?php

namespace SequentSoft\ThreadFlow\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use SequentSoft\ThreadFlow\Channel\CliChannel;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\OutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingRegularMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\WithKeyboardInterface;
use SequentSoft\ThreadFlow\DataFetchers\InvokableDataFetcher;
use SequentSoft\ThreadFlow\Events\EventBus;
use SequentSoft\ThreadFlow\Session\ArraySessionStore;
use SequentSoft\ThreadFlow\Session\ArraySessionStoreStorage;

class CliThreadFlowCommand extends Command
{
    protected $signature = 'thread-flow:cli';

    protected $description = 'Starts ThreadFlow CLI';

    protected array $lastKeyboardOptions = [];

    protected function processOutgoing(
        OutgoingMessageInterface $message
    ): OutgoingMessageInterface {
        $this->lastKeyboardOptions = [];

        $this->comment('[BOT ANSWER]:');

        if ($message instanceof TextOutgoingRegularMessageInterface) {
            $this->line($message->getText());
        } else {
            $this->line('Message type: '.get_class($message));
        }

        if ($message instanceof WithKeyboardInterface && $message->getKeyboard()) {
            $data = [];
            $rows = $message->getKeyboard()->getRows();
            foreach ($rows as $rowIndex => $row) {
                $buttons = $row->getButtons();
                foreach ($buttons as $button) {
                    $this->lastKeyboardOptions[] = $button->getCallbackData();
                    $data[$rowIndex][] = '<comment>'.$button->getCallbackData().'</comment>: '.$button->getText();
                }
            }

            $this->table(['Keyboard'], $data);
        }

        $this->newLine();

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

    public function handle(): void
    {
        $this->output->title('ThreadFlow Cli');

        $messageContext = MessageContext::createFromIds('cli-user', 'cli-room');

        $cliConfig = new Config([
            'entry' => \App\ThreadFlow\Pages\IndexPage::class,
            'dispatcher' => 'sync',
        ]);

        $eventBus = new EventBus();

        $channel = new CliChannel(
            'cli',
            $cliConfig,
            new ArraySessionStore('cli', app(ArraySessionStoreStorage::class)),
            app(DispatcherFactoryInterface::class),
            $eventBus
        );

        $channel->setCallback(fn (OutgoingMessageInterface $message) => $this->processOutgoing($message));

        $dataFetcher = new InvokableDataFetcher();

        $channel->listen($messageContext, $dataFetcher);

        while (true) {
            $dataFetcher([
                'id' => Str::uuid(),
                'text' => $this->inputTextFromUser(),
            ]);
        }
    }
}
