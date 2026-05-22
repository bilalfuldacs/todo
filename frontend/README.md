# Todo React Frontend

React + TypeScript + Vite app for the PHP Todo API.

## Requirements

- Node.js 18+
- PHP API running at `http://localhost/todo` (Apache + MySQL)

## Setup

```bash
cd frontend
npm install
```

Copy `.env.example` to `.env` if needed (default API URL is already set).

## Run (development)

```bash
npm start
```

(or `npm run dev` — same command)

Open **http://localhost:5173**

The dev server proxies `/api` to `http://localhost/todo` so you can also set:

```
VITE_API_URL=/api/v1
```

## Build for production

```bash
npm run build
npm run preview
```

Deploy the `dist/` folder to any static host. Set `VITE_API_URL` to your production API URL before building.

## Features

- Register & login
- JWT-style Bearer token (stored in `localStorage`)
- Create, edit, delete todos
- Toggle complete
- Filter: All / Active / Completed

## Project structure

```
src/
├── api/client.ts       # API calls
├── context/            # Auth state
├── components/         # UI components
├── pages/              # Login, Register, Todos
└── types/              # TypeScript types
```
