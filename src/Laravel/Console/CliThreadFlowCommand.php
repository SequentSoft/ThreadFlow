<?php

namespace SequentSoft\ThreadFlow\Laravel\Console;

use DateTimeImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use SequentSoft\ThreadFlow\Channel\CliChannel;
use SequentSoft\ThreadFlow\Chat\MessageContext;
use SequentSoft\ThreadFlow\Config;
use SequentSoft\ThreadFlow\Contracts\Chat\MessageContextInterface;
use SequentSoft\ThreadFlow\Contracts\Dispatcher\DispatcherFactoryInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\BackButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\ContactButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\LocationButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Keyboard\Buttons\TextButtonInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Incoming\CommonIncomingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\CommonOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\Regular\TextOutgoingMessageInterface;
use SequentSoft\ThreadFlow\Contracts\Messages\Outgoing\WithKeyboardInterface;
use SequentSoft\ThreadFlow\DataFetchers\InvokableDataFetcher;
use SequentSoft\ThreadFlow\Events\EventBus;
use SequentSoft\ThreadFlow\Messages\Incoming\Regular\ClickIncomingMessage;
use SequentSoft\ThreadFlow\Session\ArraySessionStore;
use SequentSoft\ThreadFlow\Session\ArraySessionStoreStorage;

class CliThreadFlowCommand extends Command
{
    protected $signature = 'threadflow:cli';

    protected $description = 'Starts ThreadFlow CLI';

    protected array $lastKeyboardOptions = [];

    protected function processOutgoing(
        CommonOutgoingMessageInterface $message
    ): CommonOutgoingMessageInterface {
        $this->lastKeyboardOptions = [];

        $this->comment('[BOT ANSWER]:');

        if ($message instanceof TextOutgoingMessageInterface) {
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
                    $key = match (true) {
                        $button instanceof TextButtonInterface => $button->getCallbackData(),
                        $button instanceof BackButtonInterface => $button->getCallbackData(),
                        $button instanceof ContactButtonInterface => '::contact::',
                        $button instanceof LocationButtonInterface => '::location::',
                    };

                    $text = match (true) {
                        $button instanceof TextButtonInterface => $button->getTitle(),
                        $button instanceof BackButtonInterface => '[Back]',
                        $button instanceof ContactButtonInterface => '[Contact]',
                        $button instanceof LocationButtonInterface => '[Location]',
                    };

                    $this->lastKeyboardOptions[$key] = $button;
                    $data[$rowIndex][] = "<comment>{$key}</comment>: {$text}";
                }
            }

            $this->table(['Keyboard'], $data);
        }

        $this->newLine();

        return $message;
    }

    protected function inputTextFromUser(MessageContextInterface $messageContext): string|CommonIncomingMessageInterface
    {
        if ($this->lastKeyboardOptions && function_exists('Laravel\Prompts\suggest')) {
            $answer = \Laravel\Prompts\suggest(
                'Message or keyboard button',
                array_keys($this->lastKeyboardOptions),
            );
        } elseif (function_exists('Laravel\Prompts\text')) {
            $answer = \Laravel\Prompts\text(
                'Message',
                'Enter message text'
            );
        } else {
            $answer = $this->anticipate('Enter message text', array_keys($this->lastKeyboardOptions));
        }

        if ($this->lastKeyboardOptions[$answer] ?? null) {
            return new ClickIncomingMessage(
                Str::uuid(),
                $messageContext,
                new DateTimeImmutable(),
                $this->lastKeyboardOptions[$answer],
            );
        }

        return $answer;
    }

    public function handle(): void
    {
        $this->output->title('ThreadFlow Cli');

        $messageContext = MessageContext::createFromIds(
            'cli',
            'cli-user',
            'cli-room'
        );

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

        $channel->setCallback(fn (CommonOutgoingMessageInterface $message) => $this->processOutgoing($message));

        $dataFetcher = new InvokableDataFetcher();

        $channel->listen($messageContext, $dataFetcher);

        while (true) {
            $dataFetcher([
                'id' => Str::uuid(),
                'message' => $this->inputTextFromUser($messageContext),
            ]);
        }
    }
}
