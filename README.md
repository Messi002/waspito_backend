# Waspito Rewards System

A simple API for rewarding users when they comment on or like posts. Earn points and badges as you hit milestones.

---

## What You Need

* **PHP 8.1+**
* **Composer**
* **PostgreSQL** (or MySQL)
* **Laravel CLI**

---

## Getting Started

1. **Clone** this repo

   ```bash
   git clone https://github.com/Messi002/waspito_backend.git
   cd Backend
   ```

2. **Install** PHP packages

   ```bash
   composer install
   ```

3. **Copy** and configure your environment file

   ```bash
   cp .env.example .env
   # then open .env in a text editor and set your DB
   ```

4. **Generate** an app key

   ```bash
   php artisan key:generate
   ```

5. **Migrate** your database

   ```bash
   php artisan migrate
   ```

6. **Seed** some sample data (optional)

   ```bash
   php artisan db:seed
   ```

7. **Run** the server

   ```bash
   php artisan serve
   ```

   Visit `http://localhost:8000/api` in your browser or API client.

---

## What's Inside

* **Users**

  * You can list users and see their points and badges.
* **Comments**

  * Users can add, update, or delete comments on posts.
* **Likes**

  * Users can like or unlike comments (and posts).
* **Rewards**

  * First comment → +50 points + beginner badge
  * 30 comments → +2500 points + top‑fan badge
  * 50+ comments → +5000 points + super‑fan badge
  * First 10 likes → +500 points + beginner badge

---

## Base URL

All endpoints start with `/api`. For example:

```
http://localhost:8000/api/login
```

---

## Authentication

### Login

**POST** `/api/login`
Body (JSON):

```json
{
  "email": "user@example.com",
  "password": "password"
}
```

Response:

```json
{
  "access_token": "your-token-here",
  "token_type": "Bearer",
  "user": { ... }
}
```

### Logout

**POST** `/api/logout`
Headers:

```
Authorization: Bearer your-token-here
```

---

## User Endpoints

### Get All Users

**GET** `/api/users`
Headers:

```
Authorization: Bearer test_token_123
```

Optional query:

* `type=beginner-badge`
* `points=100`

Example:

```
GET /api/users
```
or 

```
GET /api/users?type=beginner-badge&points=100
```

---

## Comment Endpoints

All require:

```
Authorization: Bearer your-token-here
```

* **GET** `/api/comments`
  List every comment.

* **POST** `/api/comments`
  Create a comment.
  Body:

  ```json
  {
    "post_id": 1,
    "text": "Nice post!"
  }
  ```

* **PUT** `/api/comments/{id}`
  Update a comment.
  Body:

  ```json
  {
    "text": "Updated text"
  }
  ```

* **DELETE** `/api/comments/{id}`
  Remove a comment.

---

## Like Endpoints

All require:

```
Authorization: Bearer your-token-here
```

* **POST** `/api/comments/{comment_id}/likes`
  Like a comment.

* **DELETE** `/api/comments/{comment_id}/likes/{like_id}`
  Unlike a comment.

---

## Seed Data

When you run `php artisan db:seed`, with password `password` you get:

1. **Alice** (`alice@example.com`)

   * 8 likes across 8 posts
   * **0 points**, badge `none`

2. **Bob** (`bob@example.com`)

   * No likes, no comments
   * **0 points**, badge `none`

3. **Charlie** (`charlie@example.com`)

   * 28 comments
   * **50 points**, badge `beginner-badge`

4. **Diana** (`diana@example.com`)

   * 48 comments
   * **2,550 points**, badge `top-fan-badge`

Use these users to test rewards.