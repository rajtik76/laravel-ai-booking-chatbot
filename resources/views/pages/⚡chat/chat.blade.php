@php
    $suggestions = [
        'Is there a free hotel in Prague next weekend?',
        'Find me a hotel in Paris from Nov 3 to Nov 7.',
        'What cities do you cover?',
    ];
@endphp

<div
    x-data
    x-init="$wire.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone"
    class="flex h-dvh justify-center sm:p-6"
>
    <div class="flex w-full max-w-3xl flex-col overflow-hidden bg-white shadow-xl ring-1 ring-zinc-200 sm:rounded-3xl dark:bg-zinc-900 dark:ring-zinc-800">
        <header class="flex items-center gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
            <div class="flex size-10 items-center justify-center rounded-full bg-indigo-600 text-white">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21" />
                </svg>
            </div>

            <div class="flex-1">
                <h1 class="font-semibold leading-tight">Booking assistant</h1>
                <p class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="size-2 rounded-full bg-emerald-500"></span>
                    Online
                </p>
            </div>

            @if ($messages)
                <button
                    type="button"
                    wire:click="newChat"
                    class="rounded-full px-3 py-1.5 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                >New chat</button>
            @endif
        </header>

        <div
            x-data
            x-init="new MutationObserver(() => $el.scrollTop = $el.scrollHeight).observe($el, { childList: true, subtree: true, attributes: true })"
            class="flex flex-1 flex-col gap-4 overflow-y-auto px-5 py-6"
        >
            @if (! $messages)
                <div wire:loading.remove wire:target="send" class="m-auto flex max-w-md flex-col items-center gap-6 text-center">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                        </svg>
                    </div>

                    <div class="flex flex-col gap-1">
                        <h2 class="text-lg font-semibold">How can I help you?</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">I check hotel availability in Prague, Paris and Berlin.</p>
                    </div>

                    <div class="flex flex-wrap justify-center gap-2">
                        @foreach ($suggestions as $suggestion)
                            <button
                                type="button"
                                wire:key="suggestion-{{ $loop->index }}"
                                x-on:click="$wire.prompt = @js($suggestion); $wire.send()"
                                class="rounded-full border border-zinc-200 px-3.5 py-1.5 text-sm text-zinc-700 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 dark:border-zinc-700 dark:text-zinc-300 dark:hover:border-indigo-500/50 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300"
                            >{{ $suggestion }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            @foreach ($messages as $index => $message)
                @if ($message['role'] === 'user')
                    <div wire:key="message-{{ $index }}" class="flex justify-end">
                        <div class="max-w-[80%] whitespace-pre-wrap rounded-2xl rounded-br-md bg-indigo-600 px-4 py-2.5 text-white">{{ $message['content'] }}</div>
                    </div>
                @else
                    <div wire:key="message-{{ $index }}" class="flex items-end gap-2">
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white">AI</div>
                        <div class="max-w-[80%] rounded-2xl rounded-bl-md bg-zinc-100 px-4 py-2.5 dark:bg-zinc-800 [&_a]:underline [&_li]:mt-1 [&_ol]:list-decimal [&_ol]:pl-5 [&_p+*]:mt-2 [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:pl-5">
                            {!! Str::markdown($message['content'], ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
                        </div>
                    </div>
                @endif
            @endforeach

            <div wire:loading.flex wire:target="send" class="flex-col gap-4">
                <div class="flex justify-end">
                    <div x-text="$wire.prompt" class="max-w-[80%] whitespace-pre-wrap rounded-2xl rounded-br-md bg-indigo-600 px-4 py-2.5 text-white"></div>
                </div>

                <div class="flex items-end gap-2">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white">AI</div>
                    <div class="flex gap-1 rounded-2xl rounded-bl-md bg-zinc-100 px-4 py-3.5 dark:bg-zinc-800">
                        <span class="size-2 animate-bounce rounded-full bg-zinc-400 [animation-delay:-0.3s]"></span>
                        <span class="size-2 animate-bounce rounded-full bg-zinc-400 [animation-delay:-0.15s]"></span>
                        <span class="size-2 animate-bounce rounded-full bg-zinc-400"></span>
                    </div>
                </div>
            </div>
        </div>

        <form wire:submit="send" class="border-t border-zinc-200 p-4 dark:border-zinc-800">
            @error('prompt')
                <p class="mb-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="flex items-center gap-2 rounded-full bg-zinc-100 py-1.5 pl-5 pr-1.5 ring-indigo-500 focus-within:ring-2 dark:bg-zinc-800">
                <input
                    wire:model="prompt"
                    wire:loading.attr="readonly"
                    wire:target="send"
                    type="text"
                    placeholder="Ask about hotel availability…"
                    autocomplete="off"
                    autofocus
                    class="flex-1 bg-transparent py-1.5 outline-none placeholder:text-zinc-400 read-only:text-zinc-400"
                >
                <button
                    type="submit"
                    x-bind:disabled="! $wire.prompt.trim()"
                    aria-label="Send"
                    class="flex size-9 items-center justify-center rounded-full bg-indigo-600 text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:bg-zinc-300 data-loading:pointer-events-none data-loading:opacity-50 dark:disabled:bg-zinc-700"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
