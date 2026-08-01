PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('Student', 'Employer', 'Coordinator', 'Admin')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS students (
    student_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    degree TEXT NOT NULL,
    gpa REAL DEFAULT 0.0,
    cv_path TEXT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS employers (
    employer_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    company_name TEXT NOT NULL,
    industry TEXT NOT NULL,
    company_website TEXT DEFAULT NULL,
    is_verified INTEGER DEFAULT 0 CHECK (is_verified IN (0, 1)),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS skills (
    skill_id INTEGER PRIMARY KEY AUTOINCREMENT,
    skill_name TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS student_skills (
    student_id INTEGER NOT NULL,
    skill_id INTEGER NOT NULL,
    PRIMARY KEY (student_id, skill_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(skill_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS jobs (
    job_id INTEGER PRIMARY KEY AUTOINCREMENT,
    employer_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    job_type TEXT NOT NULL CHECK (job_type IN ('Full-time', 'Internship', 'Part-time')),
    location TEXT NOT NULL,
    salary_range TEXT DEFAULT 'Unpaid / Negotiable',
    deadline DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES employers(employer_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS job_skills (
    job_id INTEGER NOT NULL,
    skill_id INTEGER NOT NULL,
    PRIMARY KEY (job_id, skill_id),
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(skill_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS applications (
    application_id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    job_id INTEGER NOT NULL,
    match_score REAL DEFAULT 0.0,
    status TEXT DEFAULT 'Pending' CHECK (status IN ('Pending', 'Shortlisted', 'Interview Scheduled', 'Rejected', 'Accepted')),
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE,
    UNIQUE(student_id, job_id)
);

CREATE TABLE IF NOT EXISTS interviews (
    interview_id INTEGER PRIMARY KEY AUTOINCREMENT,
    application_id INTEGER NOT NULL UNIQUE,
    interview_date DATETIME NOT NULL,
    location_or_link TEXT NOT NULL,
    notes TEXT DEFAULT NULL,
    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE
);

-- Seed Initial Data
INSERT OR IGNORE INTO skills (skill_name) VALUES 
('PHP'), ('HTML5'), ('CSS3'), ('JavaScript'), ('Bootstrap'), 
('SQLite'), ('MySQL'), ('Python'), ('Java'), ('Git'), 
('Graphic Design'), ('Data Analysis'), ('Communication');

-- Default Admin Account (Password: admin123)
INSERT OR REPLACE INTO users (user_id, name, email, password, role) VALUES 
(1, 'System Admin', 'admin@careerconnect.ac.lk', '$2y$10$8K1p/b02Y./2j/vR9n78u.1O0R7j427k9k5uO7e67h0R7j427k9k5', 'Admin');