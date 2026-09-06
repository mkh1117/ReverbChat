# ReverbChat

ReverbChat is a real-time chat application built with **Laravel, Vue.js, Inertia.js, and Laravel Reverb**.

The application provides private and group conversations, real-time messaging, room and member management, user profiles, message read status, file attachments, and role-based room administration.

The project also implements application-level encryption for chat messages and streaming encryption for uploaded files.

## Features

* User registration and authentication
* Username-based authentication
* Private conversations
* Group chat rooms
* Real-time messaging
* Real-time events with Laravel Reverb
* Message read status
* User online / last seen status
* User profiles and avatars
* Room avatars and descriptions
* Room member management
* Room roles and permissions
* Admin controls for rooms
* Adding and removing room members
* Changing member roles
* Kicking users from rooms
* Message deletion
* Message replies
* Message forwarding
* File attachments
* Image and media viewing
* Encrypted chat messages
* Encrypted file storage
* Streaming encrypted files
* Contact and recent conversation management
* Database queue support
* Redis-backed caching

## Tech Stack

### Backend

* **PHP 8.2+**
* **Laravel 11**
* **Laravel Reverb** — Real-time WebSocket communication
* **Laravel Sanctum**
* **Eloquent ORM**
* **Pest** — Testing

### Frontend

* **Vue.js 3**
* **Inertia.js**
* **Laravel Echo**
* **Pusher JS**
* **Tailwind CSS**
* **Vite**
* **Axios**

### Infrastructure

* **MySQL** — Primary database
* **Redis** — Application cache
* **Laravel Reverb** — WebSocket server
* **Database Queue** — Background job processing

## Real-Time Architecture

ReverbChat uses **Laravel Reverb** to provide real-time WebSocket communication between connected clients.

Laravel events are broadcast through Reverb and received by the Vue.js frontend using **Laravel Echo**.

The basic communication flow is:

```text
Vue.js
   │
   │ Laravel Echo
   ▼
Laravel Reverb
   │
   │ WebSocket Events
   ▼
Connected Clients
```

The application uses real-time events for functionality such as:

* New messages
* Message deletion
* Message read status
* User profile updates
* Avatar updates
* Room changes
* Member role changes
* User removal from rooms
* User presence

The local Reverb server is configured to run on:

```text
http://localhost:8080
```

## Chat & Rooms

Users can communicate through private conversations and group rooms.

Room functionality includes:

* Creating rooms
* Starting private conversations
* Adding members
* Removing members
* Changing member roles
* Kicking users
* Updating room descriptions
* Updating room avatars
* Managing room permissions
* Sending and receiving messages

Room-level operations are protected by authentication and authorization middleware.

## Authentication & Authorization

The application uses Laravel's authentication system with **username-based authentication**.

Authentication functionality includes:

* User registration
* Username-based login
* Logout
* Password confirmation
* Password updates
* Protected application routes

Room administration uses authorization middleware to restrict administrative operations to authorized room members.

Broadcasting channels also verify room membership before allowing users to subscribe to private room channels.

## Message Encryption

Chat messages are encrypted before being stored using authenticated encryption.

The application uses:

```text
AES-256-GCM
```

Room-specific encryption keys are derived from the application's master key using **HMAC-SHA256**.

This provides confidentiality and integrity protection for stored message content.

## File Encryption

Uploaded chat files are protected using **Libsodium SecretStream** with:

```text
XChaCha20-Poly1305
```

Files are processed in chunks rather than requiring the entire file to be loaded into memory at once.

The encryption service uses a streaming approach for large file handling and supports decrypting files while they are being streamed to authenticated users.

## File Attachments

Users can attach files to chat messages.

The application provides authenticated attachment access instead of exposing underlying storage paths directly.

Attachment functionality includes:

* File uploads
* File metadata
* Authenticated file access
* Streaming file responses
* Encrypted file storage
* Media viewing

## User Presence

The application provides online and last-seen information for users.

Presence information is handled through Laravel Reverb and broadcasting channels.

The application includes an online-users presence channel for real-time presence updates.

## Database

The project uses **MySQL** as its primary database and **Laravel Eloquent** for database access.

Database configuration is provided through environment variables:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Database structure is managed through Laravel migrations located in:

```text
database/migrations/
```

The migrations define the application's users, rooms, room members, messages, contacts, avatars, and other related data.

## Redis

Redis is used by the application as the cache backend.

The project itself is **not Dockerized**, but Redis can be run locally or separately through Docker.

For example:

```bash
docker run -d --name reverbchat-redis -p 6379:6379 redis:alpine
```

The default Redis configuration is:

```env
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## Queue

The application uses Laravel's **database queue driver** for background jobs.

The queue configuration is:

```env
QUEUE_CONNECTION=database
```

Run the queue worker with:

```bash
php artisan queue:work
```

The queue system is separate from the Redis cache configuration.

## Project Structure

```text
ReverbChat/
├── app/
│   ├── Events/                  # Broadcast and application events
│   ├── Http/
│   │   ├── Controllers/         # HTTP controllers
│   │   ├── Middleware/          # Authentication and authorization
│   │   └── Requests/            # Request validation
│   ├── Models/                  # Eloquent models
│   ├── Providers/               # Service providers
│   ├── Rules/                   # Custom validation rules
│   └── Services/                # Application services
│
├── bootstrap/                   # Application bootstrap
├── config/                      # Laravel configuration
├── database/
│   ├── factories/               # Model factories
│   ├── migrations/              # Database migrations
│   └── seeders/                 # Database seeders
│
├── resources/
│   ├── css/                     # Application styles
│   ├── js/
│   │   ├── Components/          # Vue components
│   │   ├── Layouts/             # Vue layouts
│   │   └── Pages/               # Inertia pages
│   └── views/                   # Blade views
│
├── routes/
│   ├── auth.php                 # Authentication routes
│   ├── channels.php             # Broadcasting channels
│   ├── console.php              # Console routes
│   └── web.php                  # Web application routes
│
├── storage/                     # Application storage
├── tests/                       # Feature and unit tests
├── artisan
├── composer.json
├── package.json
├── tailwind.config.js
└── vite.config.js
```

## Getting Started

### Requirements

Make sure the following are installed:

* PHP 8.2 or higher
* Composer
* Node.js and npm
* Git
* MySQL
* Redis
* A browser

### Installation

Clone the repository:

```bash
git clone <repository-url>
cd ReverbChat
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure your MySQL database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Configure Redis:

```env
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

Configure Laravel Reverb:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

Run the database migrations:

```bash
php artisan migrate
```

Build the frontend:

```bash
npm run build
```

## Development

For local development, the application requires multiple processes.

### Laravel

Start the Laravel development server:

```bash
php artisan serve
```

By default, the application will be available at:

```text
http://localhost:8000
```

### Vite

Start the Vite development server:

```bash
npm run dev
```

### Queue Worker

Start the database queue worker:

```bash
php artisan queue:work
```

### Laravel Reverb

Start the WebSocket server:

```bash
php artisan reverb:start
```

Reverb will use the configured host and port:

```env
REVERB_HOST=localhost
REVERB_PORT=8080
```

### Composer Development Script

The project also provides a Composer development script:

```bash
composer run dev
```

Laravel Reverb should be started separately:

```bash
php artisan reverb:start
```

## Development Architecture

During local development, the main processes are:

```text
                    ┌─────────────────────┐
                    │      Vue.js         │
                    │     Inertia.js      │
                    └──────────┬──────────┘
                               │
                         Laravel Echo
                               │
                               ▼
                    ┌─────────────────────┐
                    │   Laravel Reverb    │
                    │   WebSocket Server  │
                    └──────────┬──────────┘
                               │
                               │
                               ▼
┌─────────────────────────────────────────────────────┐
│                    Laravel 11                       │
│                                                     │
│  Controllers → Services → Models → MySQL            │
│        │                                            │
│        ├── Authentication                            │
│        ├── Rooms & Members                           │
│        ├── Messages                                  │
│        ├── File Attachments                          │
│        └── Encryption                                │
│                                                     │
└───────────────────────┬─────────────────────────────┘
                        │
             ┌──────────┴──────────┐
             ▼                     ▼
          MySQL                  Redis
        Database                Cache
```

## Environment Variables

The repository provides an `.env.example` file containing the required environment configuration structure.

Important configuration includes:

### Application

```text
APP_NAME
APP_ENV
APP_KEY
APP_DEBUG
APP_URL
```

### Database

```text
DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

### Cache

```text
CACHE_STORE
REDIS_CLIENT
REDIS_HOST
REDIS_PORT
```

### Queue

```text
QUEUE_CONNECTION
```

### Broadcasting

```text
BROADCAST_CONNECTION
```

### Reverb

```text
REVERB_APP_ID
REVERB_APP_KEY
REVERB_APP_SECRET
REVERB_HOST
REVERB_PORT
REVERB_SCHEME
```

### Frontend Reverb Configuration

```text
VITE_REVERB_APP_KEY
VITE_REVERB_HOST
VITE_REVERB_PORT
VITE_REVERB_SCHEME
```

Never commit the `.env` file or real credentials to the repository.

## Broadcasting Channels

Private broadcasting channels are defined in:

```text
routes/channels.php
```

Room message channels verify that the authenticated user is a member of the requested room before allowing access.

The application also provides an online-users presence channel for real-time user presence information.

## Routes

Main application routes are defined in:

```text
routes/web.php
```

Authentication routes are defined in:

```text
routes/auth.php
```

Broadcasting authorization is defined in:

```text
routes/channels.php
```

The application primarily uses Laravel web routes with Inertia.js rather than exposing a separate REST API layer.

## Security

The application includes several security mechanisms:

* Laravel authentication
* Authentication middleware
* Authorization middleware
* Protected broadcasting channels
* Room-level access control
* Request validation
* CSRF protection
* Password hashing
* Encrypted chat messages
* Encrypted file attachments
* Authenticated file access
* Environment-based configuration

Sensitive configuration values such as application keys and Reverb credentials should always be provided through environment variables.
