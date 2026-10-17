<?php

use App\Ai\Agents\BookingAssistant;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Booking assistant')] class extends Component
{
    private const int MAX_MESSAGES = 40;

    /** @var list<array{role: string, content: string}> */
    #[Locked]
    public array $messages = [];

    #[Validate('required|string|max:1000')]
    public string $prompt = '';

    #[Validate('required|timezone:all')]
    public string $timezone = 'UTC';

    public function send(): void
    {
        $this->validate();

        try {
            $response = (new BookingAssistant($this->messages, $this->timezone))->prompt($this->prompt);
        } catch (Throwable $exception) {
            report($exception);

            $this->addError('prompt', 'The assistant is unavailable right now. Please try again.');

            return;
        }

        $this->messages = array_slice([
            ...$this->messages,
            ['role' => 'user', 'content' => $this->prompt],
            ['role' => 'assistant', 'content' => $response->text],
        ], -self::MAX_MESSAGES);

        $this->reset('prompt');
    }

    public function newChat(): void
    {
        $this->reset('messages', 'prompt');
        $this->resetErrorBag();
    }
};
