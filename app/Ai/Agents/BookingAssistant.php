<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\FindAvailableHotels;
use App\Enums\City;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\ProviderTool;
use Stringable;

#[Provider(Lab::Anthropic)]
#[UseCheapestModel]
#[MaxSteps(5)]
class BookingAssistant implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * @param  list<array{role: string, content: string}>  $history
     */
    public function __construct(
        public array $history = [],
        public string $timezone = 'UTC',
    ) {}

    public function instructions(): Stringable|string
    {
        $cities = implode(', ', array_column(City::cases(), 'value'));
        $today = now($this->timezone)->toDateString();

        return <<<PROMPT
            You are a friendly hotel booking assistant.
            Today is {$today}. We only offer hotels in: {$cities}.
            Always use the FindAvailableHotels tool before answering availability questions; never invent hotels.
            If the city, check-in or check-out date is missing, ask the guest for it.
            Reply in the language the guest writes in. Keep answers short.
            PROMPT;
    }

    /**
     * @return Message[]
     */
    public function messages(): iterable
    {
        return array_map(
            fn (array $message): Message => new Message($message['role'], $message['content']),
            $this->history,
        );
    }

    /**
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable
    {
        return [
            new FindAvailableHotels($this->timezone),
        ];
    }
}
