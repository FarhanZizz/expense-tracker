# Expense Tracker

A full-stack expense tracking system built with Laravel and WordPress, featuring a Telegram bot with natural language support.

## Features

- Track income and expenses across multiple payment sources (bKash, Nagad, Rocket, Bank, Cash)
- Real-time balance per source
- Monthly and overall summaries
- WordPress dashboard for visual tracking
- Telegram bot with natural language support in Bangla and English

## Tech Stack

- **Backend:** Laravel 13, MySQL
- **Frontend:** WordPress (custom dashboard)
- **Bot:** Telegram Bot API + Groq (Llama 3.1)
- **Auth:** Laravel Sanctum

## API Endpoints

| Method | Endpoint              | Description          |
| ------ | --------------------- | -------------------- |
| GET    | /api/sources          | List payment sources |
| POST   | /api/sources          | Add payment source   |
| GET    | /api/transactions     | List transactions    |
| POST   | /api/transactions     | Add transaction      |
| GET    | /api/summary          | Overall summary      |
| GET    | /api/summary/monthly  | Monthly summary      |
| POST   | /api/telegram/webhook | Telegram webhook     |

## Telegram Bot Usage

Talk to the bot naturally in Bangla or English:

- "bkash e 500 taka kharoch korsi" → adds expense
- "nagad theke 2000 taka আসছে" → adds income
- "ei mase koto kharoch hoise?" → monthly summary
- "bkash er balance koto?" → per source summary

## Local Setup

```bash
git clone https://github.com/FarhanZizz/expense-tracker.git
cd expense-tracker
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Add to `.env`:

```
TELEGRAM_BOT_TOKEN=your_token
GROQ_API_KEY=your_key
```
