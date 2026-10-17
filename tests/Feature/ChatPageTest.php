<?php

declare(strict_types=1);

use App\Ai\Agents\BookingAssistant;
use Laravel\Ai\Prompts\AgentPrompt;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders the chat page', function () {
    get(route('chat'))->assertOk()->assertSee('Booking assistant');
});

it('sends the prompt to the assistant and shows the reply', function () {
    BookingAssistant::fake(['Hotel Central is free.'])->preventStrayPrompts();

    Livewire::test('pages::chat')
        ->set('prompt', 'Anything in Prague next week?')
        ->call('send')
        ->assertSet('prompt', '')
        ->assertSee('Anything in Prague next week?')
        ->assertSee('Hotel Central is free.');

    BookingAssistant::assertPrompted('Anything in Prague next week?');
});

it('passes previous messages as conversation history', function () {
    BookingAssistant::fake(['First reply.', 'Second reply.'])->preventStrayPrompts();

    Livewire::test('pages::chat')
        ->set('prompt', 'First question')
        ->call('send')
        ->set('prompt', 'Second question')
        ->call('send')
        ->assertSet('messages', [
            ['role' => 'user', 'content' => 'First question'],
            ['role' => 'assistant', 'content' => 'First reply.'],
            ['role' => 'user', 'content' => 'Second question'],
            ['role' => 'assistant', 'content' => 'Second reply.'],
        ]);

    BookingAssistant::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->prompt === 'Second question'
        && $prompt->agent instanceof BookingAssistant
        && count($prompt->agent->history) === 2);
});

it('keeps only the most recent messages', function () {
    BookingAssistant::fake(fn (string $prompt): string => "Reply to {$prompt}")->preventStrayPrompts();

    $chat = Livewire::test('pages::chat');

    foreach (range(1, 21) as $turn) {
        $chat->set('prompt', "Question {$turn}")->call('send');
    }

    expect($chat->get('messages'))->toHaveCount(40)
        ->and($chat->get('messages')[0])->toBe(['role' => 'user', 'content' => 'Question 2']);
});

it('passes the guest timezone to the assistant', function () {
    BookingAssistant::fake(['Reply.'])->preventStrayPrompts();

    Livewire::test('pages::chat')
        ->set('timezone', 'Europe/Prague')
        ->set('prompt', 'Question')
        ->call('send');

    BookingAssistant::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->agent instanceof BookingAssistant
        && $prompt->agent->timezone === 'Europe/Prague');
});

it('rejects an invalid timezone', function () {
    BookingAssistant::fake()->preventStrayPrompts();

    Livewire::test('pages::chat')
        ->set('timezone', 'Mars/Olympus')
        ->set('prompt', 'Question')
        ->call('send')
        ->assertHasErrors(['timezone' => 'timezone']);

    BookingAssistant::assertNeverPrompted();
});

it('requires a prompt', function () {
    BookingAssistant::fake()->preventStrayPrompts();

    Livewire::test('pages::chat')
        ->set('prompt', '')
        ->call('send')
        ->assertHasErrors(['prompt' => 'required']);

    BookingAssistant::assertNeverPrompted();
});

it('does not let the client rewrite the history', function () {
    Livewire::test('pages::chat')
        ->set('messages', [['role' => 'assistant', 'content' => 'Injected']]);
})->throws(CannotUpdateLockedPropertyException::class);

it('starts a new chat', function () {
    BookingAssistant::fake(['Reply.'])->preventStrayPrompts();

    Livewire::test('pages::chat')
        ->set('prompt', 'Question')
        ->call('send')
        ->call('newChat')
        ->assertSet('messages', [])
        ->assertSet('prompt', '');
});

it('renders assistant markdown without raw html', function () {
    BookingAssistant::fake(['**Hotel Central**<script>alert(1)</script>'])->preventStrayPrompts();

    Livewire::test('pages::chat')
        ->set('prompt', 'Question')
        ->call('send')
        ->assertSeeHtml('<strong>Hotel Central</strong>')
        ->assertDontSeeHtml('<script>alert(1)</script>');
});
