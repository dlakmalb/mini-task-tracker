<div align="center">
    <h1>
        📋 Mini Task Tracker<br/>
        <sub><sup><sub>Track projects and tasks in one place.</sub></sup></sub><br/>
    </h1>
</div>
<br/>

## 📝 Summary
A full-stack Mini Task Tracker application built with a Symfony backend and a modern frontend, fully containerized using Docker for easy local development and review.

This project demonstrates a clean backend–frontend separation, proper database handling, and a reproducible Docker setup.

## 📌 Project Overview
The Mini Task Tracker allows users to:
- Create and manage projects
- Create and manage tasks under projects
- Persist data using a relational database
- Interact through a frontend UI backed by a REST API
  
The application is designed with a clear separation between frontend and backend.

## 🧱 Tech Stack
### Backend
- PHP 8.4
- Symfony 8.0
- Doctrine ORM
- MySQL 8.4

### Frontend
- React 19
- Next.js 16

### Infrastructure & Tooling
- Docker & Docker Compose
- Nginx (reverse proxy)
- MySQL Docker image
- Named Docker volumes for persistence

## 📂 Project Structure
<img width="305" height="538" alt="image" src="https://github.com/user-attachments/assets/64b9dd95-7099-4d15-b8a9-dd2087ed6972" />

## 🚀 Running the Project
**Option 1: Run Everything with Docker (Recommended)**

Prerequisites
- Docker Desktop installed
- Docker Compose enabled

1️⃣ Start all services
From the project root directory:
```
docker compose up -d --build
```

This command will start:
- Symfony backend
- Frontend application
- MySQL database
- Nginx reverse proxy

2️⃣ Access URLs
```
Service               |    URL
───────────────────────────────────────────────
Frontend              | http://localhost:3000
───────────────────────────────────────────────
Backend (via Nginx)	  | http://localhost:8080
───────────────────────────────────────────────
MySQL (host access)	  | localhost:3307
───────────────────────────────────────────────
```

🧪 Database Setup & Migrations

Run database migrations:
```
docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction
```

Validate schema:
```
docker compose exec backend php bin/console doctrine:schema:validate
```

Check migration status:
```
docker compose exec backend php bin/console doctrine:migrations:status
```

🔄 Reset the Environment (Fresh Start)

⚠️ This will delete all database data.
```
docker compose down -v
docker compose up -d --build
```

**Option 2: Run without Docker (This is optional. Docker is the recommended way)**

🧠 Backend - How to Run (Without Docker)

1️⃣ Install dependencies
```
cd backend
composer install
```

2️⃣ Configure environment (Create .env.local)
```
cp .env .env.local
DATABASE_URL="mysql://user:password@127.0.0.1:3306/mini_task_tracker"
```

3️⃣ Create DB & run migrations
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

4️⃣ Start backend server
```
symfony serve
```
Or
```
php -S localhost:8000 -t public
```

🎨 Frontend - How to Run (Without Docker)

1️⃣ Install dependencies
```
cd frontend
npm install
```

2️⃣ Configure environment
```
cp .env.example .env
NEXT_PUBLIC_API_BASE_URL=
```

3️⃣ Start frontend
```
npm run dev
```
Frontend will be available at:
```
http://localhost:3000
```

🔌 Sample API Requests (Optional)

**Create a Project**
```
curl -X POST http://localhost:8080/api/projects \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Demo Project",
    "description": "Sample project"
  }'
```

**Get Projects**
```
curl http://localhost:8080/api/projects
```

**Create a Task**
```
curl -X POST http://localhost:8000/api/projects/1/taskss \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Initial task",
    "status": "in_progress",
    "priority": "low"
  }'
```

**Get Tasks**
```
curl http://localhost:8080/api/projects/1/tasks
```

**Update a Task**
```
curl -X PATCH "http://localhost:8080/api/projects/1/tasks/1" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated task title",
    "status": "done"
  }'
```

**Delete a Task**
```
curl -X DELETE "http://localhost:8080/api/projects/1/tasks/1"
```

## 🏗 Architecture Decisions
- API-first architecture: frontend and backend are decoupled.
- Symfony + Doctrine for structured domain modeling and data persistence.
- Docker Compose used to standardize local development.
- Nginx acts as a reverse proxy to backend services.
- Database persistence handled via Docker volumes.

📝 Notes & Design Decisions
- MySQL is exposed on port 3307 to avoid conflicts with local MySQL installations.
- Database data is persisted using a Docker named volume.
- Backend runs using PHP-FPM behind Nginx.
- Composer scripts are disabled during Docker build for stability.
- The setup is optimized for local development and code review.

## ⏱ Time-Aware Trade-Offs
Due to time constraints:
- Authentication/authorization was not implemented.
- Basic validation only (no advanced error handling).
- Minimal UI styling, focus was on functionality.
- Limited automated tests.

## 🚀 What I Would Do Next With More Time
- Add authentication (JWT/session-based)
- Add role-based permissions
- Improve frontend UX and validation
- Add automated tests (unit + integration)
- Add more filtering, and search
- Improve logging and error handling
- Add CI pipeline

## ✅ Current Status
- Backend API running and reachable
- Frontend connected to backend
- MySQL database initialized and stable
- Fully Dockerized, reproducible environment
- Ready for review and extension
