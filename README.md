
# 🏃‍♂️ L'Embuscade - Raid Management Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.1-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind">
  <img src="https://img.shields.io/badge/Alpine.js-3.15-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js">
</p>

## 📖 Overview

**L'Embuscade** is a comprehensive web platform for managing outdoor sporting events (raids), team registrations, race logistics, and participant tracking. Developed as a SAE (Situation d'Apprentissage et d'Évaluation) project by a team of 9 students in BUT Informatique (2nd year) over 4 intensive days.

The platform digitizes the entire lifecycle of raid events - from event creation and registration management to team tracking, payment validation, and results publication.

---

## 🎯 Project Context

- **Academic Project**: SAE 3 - BUT Informatique (2nd Year)
- **Team Size**: 9 students
- **Duration**: 4 days sprint
- **Institution**: IUT Caen (University of Caen Normandy)
- **Objective**: Build a real-world application managing sporting events with complex team-based registrations

---

## ✨ Key Features

### 🏔️ For Event Organizers
- **Raid Management**: Create and manage multi-race outdoor events (raids)
- **Race Configuration**: Define race types, difficulty levels, participant limits, age categories
- **Team Administration**: Manage team registrations, validate payments, assign bib numbers
- **Member Management**: Add/remove team members, update participant information
- **Results Tracking**: Upload race results, record finish times and rankings
- **Data Export**: Export participant lists and results for processing

### 👥 For Participants
- **Event Discovery**: Browse upcoming and past raids with advanced filtering
- **Team Registration**: Create teams and register for races
- **Dashboard**: View registered races, team information, and payment status
- **Club Management**: Users can manage club memberships and affiliations
- **Profile Management**: Update personal information and preferences

### 🔐 Authentication & Security
- Complete user authentication system (login, registration, password reset)
- Role-based access control (participants, organizers, administrators)
- Email notifications for team registrations
- Secure payment status tracking

### 📊 Advanced Features
- **Real-time Validation**: AJAX-powered team eligibility checking
- **Age Category Management**: Automatic validation based on participant ages
- **Club Affiliations**: Manage club memberships with license discounts
- **Registration Windows**: Automatic enforcement of registration deadlines
- **Interactive Maps**: Raid location display with coordinates
- **Contact System**: Integrated contact form for inquiries

---

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 12 (PHP 8.2+)
- **Authentication**: Laravel's built-in auth system with custom password reset
- **Email**: Laravel Mail with custom Mailable classes
- **Database**: SQLite (default) / MySQL support
- **Testing**: PHPUnit with Feature and Unit tests

### Frontend
- **CSS Framework**: Tailwind CSS 4.1
- **JavaScript**: Alpine.js 3.15 for reactive components
- **Build Tool**: Vite 7.0
- **HTTP Client**: Axios for AJAX requests

### DevOps
- **CI/CD**: GitLab CI/CD pipeline
- **Deployment**: Automated deployment to production server
- **Proxy Configuration**: Unicaen proxy support
- **Process Management**: Composer & NPM scripts with Concurrently

---

## 📁 Project Structure

```
laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Application controllers
│   │   │   ├── AuthController.php       # User authentication
│   │   │   ├── RaidController.php       # Raid management
│   │   │   ├── RaceController.php       # Race management
│   │   │   ├── inscFormController.php   # Team registration
│   │   │   ├── ClubController.php       # Club management
│   │   │   └── DashboardController.php  # User dashboard
│   │   └── Requests/             # Form validation requests
│   ├── Mail/                     # Email templates (Mailables)
│   ├── Models/                   # Eloquent models
│   │   ├── VikRaid.php          # Raid events
│   │   ├── VikRace.php          # Individual races
│   │   ├── VikEquipe.php        # Teams
│   │   ├── VikClub.php          # Clubs
│   │   └── User.php             # Users/Participants
│   └── Notifications/            # Custom notifications
├── database/
│   ├── factories/                # Model factories for testing
│   └── seeders/                  # Database seeders
├── resources/
│   ├── views/                    # Blade templates
│   │   ├── pages/               # Main pages
│   │   ├── components/          # Reusable components
│   │   ├── emails/              # Email templates
│   │   └── layouts/             # Page layouts
│   ├── js/                      # JavaScript files
│   └── css/                     # Stylesheets
├── routes/
│   └── web.php                  # Application routes
├── tests/
│   ├── Feature/                 # Feature tests
│   └── Unit/                    # Unit tests
└── public/                      # Public assets
```

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQLite or MySQL database

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Embuscade
   ```

2. **Navigate to Laravel directory**
   ```bash
   cd laravel
   ```

3. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   - Edit `.env` file
   - Default: SQLite (`database/database.sqlite`)
   - Optional: Switch to MySQL by updating `DB_*` variables

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Start development server**
   ```bash
   php artisan serve
   ```

### Quick Setup (All-in-One)
```bash
composer run setup
```

### Development Mode
Run all services concurrently (server, queue, logs, vite):
```bash
composer run dev
```

---

## 🌐 Routes Overview

### Public Routes
- `/` - Homepage with raid listings
- `/raid/{raid_num}` - Raid details
- `/course/{cou_num}` - Race details
- `/inscForm` - Team registration form
- `/contact` - Contact page
- `/a-propos` - About page

### Authentication Routes
- `/login` - User login
- `/register` - User registration
- `/forgot-password` - Password reset request
- `/reset-password/{token}` - Password reset form

### Authenticated Routes
- `/dashboard` - User dashboard
- `/profil` - User profile management
- `/raids/manage` - Raid management (organizers)
- `/my-races` - My organized races
- `/course/{cou_num}/manage` - Race management panel
- `/clubs` - Club management

### API Endpoints
- `/validate-equipe/{equ}/{cou}` - Team validation (JSON)
- `/api/users/search` - User search (AJAX)
- `/inscrits/search` - Participant search

---

## 🧪 Testing

The project includes comprehensive test coverage:

### Run all tests
```bash
php artisan test
```

### Test suites included
- **Feature Tests**: Integration tests for major features
  - `RaidCreationTest.php` - Raid creation workflow
  - `RaidShowTest.php` - Raid display and filtering
  - `RaceDateValidationTest.php` - Registration window validation
  - `RaceTeamsCountTest.php` - Team limit enforcement
  - `PpsUpdateMethodTest.php` - Participant data updates
  - `LogsRedirectTest.php` - Log viewing permissions

- **Unit Tests**: Isolated component testing

---

## 📧 Email Features

The platform sends automated emails for:
- **Team Leader**: Confirmation email when creating a team
- **Team Members**: Welcome email when joining a team
- Custom email templates with raid and race information

Templates located in: `resources/views/emails/`

---

## 🔀 Git Workflow

### Protected Branches
- **main**: Initial project state (protected, no direct pushes)
- **dev**: Staging branch for testing (protected from deletion)
- **stable**: Production branch (auto-deployed to web server)

### Development Process
1. Checkout `dev` branch
2. Create feature branch
3. Develop and commit changes
4. Push feature branch
5. Merge into `dev` and test
6. Merge `dev` into `stable` for deployment

---

## 🚢 CI/CD Pipeline

Automated deployment via GitLab CI (``.gitlab-ci.yml`):

### Deployment Steps
1. Copy files to production server (excluding vendor, node_modules, .env)
2. Install Composer dependencies (if `composer.json` changed)
3. Install NPM packages (if `package.json` changed)
4. Convert `.env.prod` to `.env`
5. Generate application key
6. Build frontend assets (`npm run build`)
7. Run database migrations
8. Clear caches and optimize

### Deployment Triggers
- Push to `stable` branch
- Runs on GitLab runner tagged: `devc3 g1`

---

## 🗄️ Database Schema

### Main Tables (Viking Schema)
- **vik_raid**: Raid events
- **vik_course**: Individual races within raids
- **vik_equipe**: Teams registered for races
- **vik_participate**: Team member participation
- **vik_club**: Clubs/Organizations
- **vik_type_course**: Race type definitions
- **vik_tranche_age**: Age category definitions
- **vik_inscrit**: Users infos

---

## 👥 Team Contributions

This project was collaboratively developed by 9 students, showcasing:
- **Full-stack development** (Laravel + Tailwind + Alpine.js)
- **Database design** and optimization
- **Git workflow** and version control
- **Agile methodology** (4-day sprint)
- **Team collaboration** and code review
- **CI/CD implementation**
- **Testing practices** (Feature + Unit tests)

---

## 📝 License

This is an educational project developed as part of the BUT Informatique curriculum at IUT Caen.

---

## 🙏 Acknowledgments

- **IUT Caen** - University of Caen Normandy
- **BUT Informatique Program** - 2nd Year
- **Project Supervisors** and instructors
- **Laravel Community** for excellent documentation

---

## 📞 Support

For issues, questions, or contributions related to this academic project, please use the contact form within the application or refer to the project documentation.

---

<p align="center">
  <strong>Developed with ❤️ by 9 BUT Informatique students in 4 days</strong>
</p>

