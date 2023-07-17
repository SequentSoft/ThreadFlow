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

    protected ?OutgoingMessageInterface $lastOutgoingMessage = null;

    protected array $lastKeyboardOptions = [];

    protected function processOutgoing(
        OutgoingMessageInterface $message,
        SessionInterface $session
    ): OutgoingMessageInterface {
        $this->lastOutgoingMessage = $message;
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
        }

        return $message;
    }

    /**
     * Handles the console command.
     */
    public function handle()
    {
        $channelName = $this->option('channel');

        $messageContext = MessageContext::createFromIds(
            $this->option('participant-id'),
            $this->option('room-id'),
        );

        $incomingChannel = new CliIncomingChannel($messageContext);

        $dispatcher = new SyncIncomingDispatcher(
            app(BotInterface::class),
        );

        $dataFetcher = new InvokableDataFetcher();

        $this->output->title('ThreadFlow Cli');

        $incomingChannel->listen(
            $dataFetcher,
            function (IncomingMessageInterface $message) use ($channelName, $dispatcher) {
                $outgoingCallback = fn(
                    OutgoingMessageInterface $message,
                    SessionInterface $session
                ) => $this->processOutgoing($message, $session);

                $dispatcher->dispatch(
                    channelName: $channelName,
                    message: $message,
                    outgoingCallback: $outgoingCallback,
                );
            }
        );

        while (true) {
            $text = $this->anticipate('Enter message text', $this->lastKeyboardOptions);
            $dataFetcher([
                'id' => Str::uuid(),
                'text' => $text,
            ]);
        }
    }
}
