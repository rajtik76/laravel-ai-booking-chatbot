# Laravel AI Booking Chatbot

A hotel booking assistant built with Laravel, Livewire and the [Laravel AI SDK](https://github.com/laravel/ai). Guests ask about hotel availability in plain language and the assistant answers from real data in the database, using a tool call instead of guessing.

## How it works

- **Chat UI** (`resources/views/pages/⚡chat/`) - a Livewire single-file component served at `/`. It keeps the conversation history (last 40 messages), sends the browser's timezone along with each prompt and lets the guest start a new chat.
- **Agent** (`app/Ai/Agents/BookingAssistant.php`) - a conversational agent running on Anthropic with the cheapest available model and up to 5 steps. It knows today's date in the guest's timezone and the list of supported cities, asks for missing details (city, check-in, check-out) and replies in the guest's language.
- **Tool** (`app/Ai/Tools/FindAvailableHotels.php`) - validates the city and dates, then returns hotels whose availability windows cover every night of the requested stay. Overlapping or adjacent windows are merged, so a stay spanning two windows still matches.

### Data model

| Table                  | Columns                                    |
|------------------------|--------------------------------------------|
| `hotels`               | `name`, `city` (`City` enum), `country` (`Country` enum) |
| `hotel_availabilities` | `hotel_id`, `from`, `to` (inclusive dates) |

Supported cities are defined in `app/Enums/City.php`: Prague, Paris and Berlin. Each city maps to its country.

## Tech stack

- PHP 8.4, Laravel 13
- Laravel AI SDK (`laravel/ai`) with Anthropic as the provider
- Livewire 4, Tailwind CSS 4, Vite
- SQLite by default
- Pest, Larastan, Pint

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- An Anthropic API key

## Installation

```bash
git clone <repository-url> laravel-ai-booking-chatbot
cd laravel-ai-booking-chatbot
composer setup
```

`composer setup` installs PHP and JS dependencies, creates `.env`, generates the app key, runs migrations and builds the frontend assets.

Add your API key to `.env`:

```dotenv
ANTHROPIC_API_KEY=your-key
```

Seed the demo hotels (one per city, available from today until the end of next year):

```bash
php artisan db:seed
```

## Running locally

```bash
composer dev
```

Then open the app in your browser and ask something like *"Is there a free hotel in Prague from next Friday to Sunday?"*

## Testing and code quality

```bash
composer test        # Pest test suite (parallel)
composer lint        # fix code style with Pint
composer lint:check  # check code style without changes
composer stan        # static analysis with Larastan
composer review      # lint:check + stan + test
```

The tests cover the chat page, the availability tool and the model factories. AI calls are faked, so no API key is needed to run them.

## License

MIT
