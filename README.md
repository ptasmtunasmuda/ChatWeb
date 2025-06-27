# ChatWeb - Real-time Chat Application

A modern, full-featured real-time chat application built with Laravel 12 and Vue 3, featuring WebSocket integration, admin panel, and beautiful UI design.

## 🚀 Features

### 🔐 Authentication & User Management
- **User Registration & Login** with email verification
- **Password Reset** functionality
- **Role-based Access Control** (Admin/User)
- **Profile Management** with avatar support
- **Admin User Management** with CRUD operations
- **🆕 IP Whitelist Management** - Control user access by IP address

### 💬 Real-time Chat System
- **Real-time Messaging** with WebSocket (Laravel Reverb)
- **Group & Private Chats** support
- **File Upload & Sharing** with Spatie Media Library
- **Message Status** (sent, delivered, read)
- **Typing Indicators** and online status
- **Chat Room Management** with participants

### 👑 Admin Panel
- **Dashboard Analytics** with charts and statistics
- **User Management** with advanced filtering
- **🆕 IP Access Control** - Visual indicators and management
- **Chat Room Monitoring** and moderation
- **Message Management** and content moderation
- **System Health Monitoring**
- **Data Export** functionality (CSV/JSON)

### 🎨 Modern UI/UX
- **Responsive Design** for all devices
- **Glass Morphism** design with backdrop blur
- **Purple Gradient Theme** with smooth animations
- **Dark/Light Mode** support
- **Real-time Notifications** with toast messages
- **Loading States** and error handling

## 🛠 Tech Stack

### Backend
- **Laravel 12** - PHP Framework
- **MySQL** - Database
- **Laravel Sanctum** - API Authentication
- **Laravel Reverb** - WebSocket Server
- **Spatie Media Library** - File Management
- **Laravel Queue** - Background Jobs

### Frontend
- **Vue 3** - JavaScript Framework
- **Pinia** - State Management
- **Vue Router** - Client-side Routing
- **Tailwind CSS v4** - Utility-first CSS
- **Vite** - Build Tool
- **Axios** - HTTP Client
- **Chart.js** - Data Visualization
- **Laravel Echo** - WebSocket Client

## 📋 Requirements

- **PHP** >= 8.2
- **Node.js** >= 18.0
- **MySQL** >= 8.0
- **Composer** >= 2.0
- **NPM** or **Yarn**

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/ptasmtunasmuda/WebChat.git
cd chatweb
```

### 2. Backend Setup
```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatweb
DB_USERNAME=root
DB_PASSWORD=

# Configure broadcasting
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

# Create database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS chatweb;"

# Run migrations and seeders
php artisan migrate:fresh --seed

# Create storage link
php artisan storage:link
```

### 3. Frontend Setup
```bash
# Install Node.js dependencies
npm install

# Build assets for development
npm run dev

# Or build for production
npm run build
```

### 4. Start Services
```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start WebSocket server
php artisan reverb:start

# Terminal 3: Start queue worker (optional)
php artisan queue:work

# Terminal 4: Start Vite dev server (development only)
npm run dev
```

## 🔑 Default Credentials

### Admin Account
- **Email:** admin@chatweb.com
- **Password:** password
- **Features:** Full access including IP whitelist management

### User Account
- **Email:** user@chatweb.com
- **Password:** password
- **Features:** Standard user access (no IP restrictions by default)

> **Note:** For testing IP whitelist features, additional test users are available via the `IpWhitelistTestSeeder`. See [documentation](./docs/IP_WHITELIST_COMPLETE_GUIDE.md#testing) for details.

## 📚 API Documentation

### Authentication Endpoints

#### Register User
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

#### Login User
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password"
}
```

#### Logout User
```http
POST /api/logout
Authorization: Bearer {token}
```

#### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

### Chat Room Endpoints

#### Get User's Chat Rooms
```http
GET /api/chat-rooms
Authorization: Bearer {token}
```

#### Create Chat Room
```http
POST /api/chat-rooms
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "General Discussion",
    "description": "General chat room",
    "type": "group",
    "is_private": false
}
```

#### Get Chat Room Details
```http
GET /api/chat-rooms/{id}
Authorization: Bearer {token}
```

#### Update Chat Room
```http
PUT /api/chat-rooms/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Room Name",
    "description": "Updated description"
}
```

#### Delete Chat Room
```http
DELETE /api/chat-rooms/{id}
Authorization: Bearer {token}
```

### Message Endpoints

#### Get Messages
```http
GET /api/chat-rooms/{roomId}/messages?page=1&per_page=50
Authorization: Bearer {token}
```

#### Send Message
```http
POST /api/chat-rooms/{roomId}/messages
Authorization: Bearer {token}
Content-Type: application/json

{
    "content": "Hello everyone!",
    "type": "text"
}
```

#### Send File Message
```http
POST /api/chat-rooms/{roomId}/messages
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "content": "Check this file",
    "type": "file",
    "file": [binary file data]
}
```

#### Update Message
```http
PUT /api/chat-rooms/{roomId}/messages/{messageId}
Authorization: Bearer {token}
Content-Type: application/json

{
    "content": "Updated message content"
}
```

#### Delete Message
```http
DELETE /api/chat-rooms/{roomId}/messages/{messageId}
Authorization: Bearer {token}
```

### Admin Endpoints

#### Get Dashboard Stats
```http
GET /api/admin/dashboard
Authorization: Bearer {admin-token}
```

#### Get All Users (Admin)
```http
GET /api/admin/users?page=1&search=john&role=user&status=active
Authorization: Bearer {admin-token}
```

#### Get User Details (Admin)
```http
GET /api/admin/users/{id}
Authorization: Bearer {admin-token}
```

#### Create User (Admin)
```http
POST /api/admin/users
Authorization: Bearer {admin-token}
Content-Type: application/json

{
    "name": "New User",
    "email": "newuser@example.com",
    "password": "password",
    "role": "user",
    "is_active": true
}
```

#### Update User (Admin)
```http
PUT /api/admin/users/{id}
Authorization: Bearer {admin-token}
Content-Type: application/json

{
    "name": "Updated Name",
    "email": "updated@example.com",
    "role": "admin",
    "is_active": false
}
```

#### Delete User (Admin)
```http
DELETE /api/admin/users/{id}
Authorization: Bearer {admin-token}
```

#### Bulk User Actions (Admin)
```http
POST /api/admin/users/bulk-action
Authorization: Bearer {admin-token}
Content-Type: application/json

{
    "action": "activate",
    "user_ids": [1, 2, 3, 4]
}
```

#### Get Chat Rooms (Admin)
```http
GET /api/admin/chat-rooms?page=1&search=general&type=group&status=active
Authorization: Bearer {admin-token}
```

#### Update Chat Room (Admin)
```http
PUT /api/admin/chat-rooms/{id}
Authorization: Bearer {admin-token}
Content-Type: application/json

{
    "name": "Updated Room",
    "description": "Updated description",
    "is_active": true
}
```

#### Delete Chat Room (Admin)
```http
DELETE /api/admin/chat-rooms/{id}
Authorization: Bearer {admin-token}
```

#### Export Data (Admin)
```http
POST /api/admin/export-data
Authorization: Bearer {admin-token}
Content-Type: application/json

{
    "type": "users",
    "format": "csv",
    "date_from": "2024-01-01",
    "date_to": "2024-12-31"
}
```

## 🔄 WebSocket Events

### Client Events (Send to Server)
```javascript
// Join chat room
Echo.join(`chat.${roomId}`)

// Send typing indicator
Echo.private(`chat.${roomId}`)
    .whisper('typing', {
        user: user,
        typing: true
    });
```

### Server Events (Receive from Server)
```javascript
// Listen for new messages
Echo.private(`chat.${roomId}`)
    .listen('MessageSent', (e) => {
        console.log('New message:', e.message);
    });

// Listen for typing indicators
Echo.private(`chat.${roomId}`)
    .listenForWhisper('typing', (e) => {
        console.log('User typing:', e.user);
    });

// Listen for user joined/left
Echo.private(`chat.${roomId}`)
    .listen('UserJoined', (e) => {
        console.log('User joined:', e.user);
    })
    .listen('UserLeft', (e) => {
        console.log('User left:', e.user);
    });
```

## 📁 Project Structure

```
chatweb/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── ChatRoomController.php
│   │   │   └── MessageController.php
│   │   └── Admin/
│   │       ├── AdminDashboardController.php
│   │       ├── AdminUserController.php
│   │       └── AdminChatController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── ChatRoom.php
│   │   ├── Message.php
│   │   └── ChatRoomParticipant.php
│   └── Events/
│       ├── MessageSent.php
│       ├── UserJoined.php
│       └── UserLeft.php
├── resources/js/
│   ├── components/
│   │   ├── auth/
│   │   ├── admin/
│   │   └── chat/
│   ├── stores/
│   │   ├── auth.js
│   │   ├── chat.js
│   │   ├── admin.js
│   │   └── notifications.js
│   └── router/index.js
└── routes/
    ├── api.php
    └── web.php
```

## 🎯 Usage Examples

### Frontend Integration

#### Authentication
```javascript
// Login
const response = await axios.post('/api/login', {
    email: 'user@example.com',
    password: 'password'
});
localStorage.setItem('auth_token', response.data.token);
```

#### Real-time Chat
```javascript
// Join chat room
Echo.private(`chat.${roomId}`)
    .listen('MessageSent', (e) => {
        messages.value.push(e.message);
    });

// Send message
await axios.post(`/api/chat-rooms/${roomId}/messages`, {
    content: 'Hello World!',
    type: 'text'
});
```

## 📚 Documentation

Comprehensive documentation is available in the [`docs/`](./docs/) directory:

- **📖 [Complete Documentation Index](./docs/README.md)** - Overview of all available documentation
- **🔒 [IP Whitelist Management Guide](./docs/IP_WHITELIST_COMPLETE_GUIDE.md)** - Complete guide for IP access control feature

### Quick Links
- **For Administrators:** [IP Whitelist Usage Examples](./docs/IP_WHITELIST_COMPLETE_GUIDE.md#usage-examples)
- **For Developers:** [API Documentation](./docs/IP_WHITELIST_COMPLETE_GUIDE.md#api-documentation)
- **For Troubleshooting:** [Common Issues & Solutions](./docs/IP_WHITELIST_COMPLETE_GUIDE.md#troubleshooting)

## 🚀 Deployment

### Production Setup
```bash
# Build for production
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set up supervisor for queue workers
# Set up nginx/apache configuration
# Configure SSL certificates
```

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

For support, email info@asmtunasmuda.com

## 🎉 Acknowledgments

- Laravel Framework
- Vue.js Community
- Tailwind CSS
- All contributors and testers
