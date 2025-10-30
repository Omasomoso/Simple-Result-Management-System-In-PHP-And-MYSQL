## Unified School Management Database Schema (SQLite)

```sql
-- Unified School Management System Database
-- Combines Fee Management and Result Management

-- Academic Structure Tables
CREATE TABLE academic_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    start_date TEXT NOT NULL,
    end_date TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE terms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    session_id INTEGER NOT NULL,
    start_date TEXT NOT NULL,
    end_date TEXT NOT NULL,
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id)
);

CREATE TABLE classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    level TEXT, -- e.g., Primary, Secondary, etc.
    stream TEXT -- e.g., Science, Arts, etc.
);

-- Family and Student Management
CREATE TABLE families (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    parent_name TEXT NOT NULL,
    contact_phone TEXT,
    contact_email TEXT,
    address TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    family_id INTEGER,
    academic_session_id INTEGER NOT NULL,
    full_name TEXT NOT NULL,
    reg_number TEXT UNIQUE NOT NULL,
    current_class_id INTEGER NOT NULL,
    enrollment_date TEXT NOT NULL,
    date_of_birth TEXT,
    gender TEXT,
    password TEXT NOT NULL DEFAULT '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (family_id) REFERENCES families(id),
    FOREIGN KEY (academic_session_id) REFERENCES academic_sessions(id),
    FOREIGN KEY (current_class_id) REFERENCES classes(id)
);

-- Fee Management Tables
CREATE TABLE fee_declarations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    term_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    fee_type TEXT NOT NULL,
    amount REAL NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
    FOREIGN KEY (term_id) REFERENCES terms(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

CREATE TABLE payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    session_id INTEGER NOT NULL,
    term_id INTEGER NOT NULL,
    fee_type TEXT NOT NULL,
    amount REAL NOT NULL,
    payment_date TEXT NOT NULL,
    receipt_no TEXT NOT NULL,
    payment_method TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
    FOREIGN KEY (term_id) REFERENCES terms(id)
);

-- Result Management Tables
CREATE TABLE subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    code TEXT UNIQUE,
    category TEXT, -- e.g., Core, Elective
    max_score INTEGER DEFAULT 100,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    session_id INTEGER NOT NULL,
    term_id INTEGER NOT NULL,
    test1 REAL DEFAULT 0,
    test2 REAL DEFAULT 0,
    exam REAL DEFAULT 0,
    total REAL DEFAULT 0,
    grade TEXT,
    remark TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
    FOREIGN KEY (term_id) REFERENCES terms(id),
    UNIQUE(student_id, subject_id, session_id, term_id)
);

CREATE TABLE affective_skills (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    session_id INTEGER NOT NULL,
    term_id INTEGER NOT NULL,
    trait1 TEXT DEFAULT '0',
    trait2 TEXT DEFAULT '0',
    trait3 TEXT DEFAULT '0',
    trait4 TEXT DEFAULT '0',
    trait5 TEXT DEFAULT '0',
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
    FOREIGN KEY (term_id) REFERENCES terms(id),
    UNIQUE(student_id, session_id, term_id)
);

CREATE TABLE psychomotor_skills (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    session_id INTEGER NOT NULL,
    term_id INTEGER NOT NULL,
    skill1 TEXT DEFAULT '0',
    skill2 TEXT DEFAULT '0',
    skill3 TEXT DEFAULT '0',
    skill4 TEXT DEFAULT '0',
    skill5 TEXT DEFAULT '0',
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
    FOREIGN KEY (term_id) REFERENCES terms(id),
    UNIQUE(student_id, session_id, term_id)
);

CREATE TABLE gpa_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    session_id INTEGER NOT NULL,
    term_id INTEGER NOT NULL,
    gpa REAL DEFAULT 0,
    cgpa REAL DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
    FOREIGN KEY (term_id) REFERENCES terms(id),
    UNIQUE(student_id, session_id, term_id)
);

-- User Management Tables
CREATE TABLE admin (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    email TEXT,
    full_name TEXT,
    role TEXT DEFAULT 'admin',
    permissions TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE teachers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER,
    full_name TEXT NOT NULL,
    contact_phone TEXT,
    contact_email TEXT,
    qualifications TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(id)
);

CREATE TABLE class_teachers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    teacher_id INTEGER NOT NULL,
    session_id INTEGER NOT NULL,
    is_main_teacher BOOLEAN DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
    UNIQUE(class_id, session_id, teacher_id)
);

CREATE TABLE teacher_subjects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    teacher_id INTEGER NOT NULL,
    subject_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    session_id INTEGER NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (session_id) REFERENCES academic_sessions(id),
    UNIQUE(teacher_id, subject_id, class_id, session_id)
);

-- Audit Log Table
CREATE TABLE audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT,
    user_agent TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(id)
);

-- Grading System Table
CREATE TABLE grading_system (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    min_score REAL NOT NULL,
    max_score REAL NOT NULL,
    grade TEXT NOT NULL,
    points REAL NOT NULL,
    remark TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Data
INSERT INTO academic_sessions (name, start_date, end_date) VALUES 
('2023/2024', '2023-09-01', '2024-07-31'),
('2024/2025', '2024-09-01', '2025-07-31');

INSERT INTO terms (name, session_id, start_date, end_date) VALUES 
('First Term', 1, '2023-09-01', '2023-12-15'),
('Second Term', 1, '2024-01-08', '2024-04-05'),
('Third Term', 1, '2024-04-23', '2024-07-31'),
('First Term', 2, '2024-09-01', '2024-12-15');

INSERT INTO classes (name, level, stream) VALUES 
('JSS1', 'Junior Secondary', NULL),
('JSS2', 'Junior Secondary', NULL),
('JSS3', 'Junior Secondary', NULL),
('SS1', 'Senior Secondary', 'Science'),
('SS2', 'Senior Secondary', 'Science'),
('SS3', 'Senior Secondary', 'Science'),
('SS1', 'Senior Secondary', 'Arts'),
('SS2', 'Senior Secondary', 'Arts'),
('SS3', 'Senior Secondary', 'Arts');

INSERT INTO subjects (name, code, category) VALUES 
('Mathematics', 'MATH', 'Core'),
('English', 'ENG', 'Core'),
('Physics', 'PHY', 'Core'),
('Chemistry', 'CHEM', 'Core'),
('Biology', 'BIO', 'Core'),
('Geography', 'GEOG', 'Elective'),
('Economics', 'ECON', 'Elective'),
('Further Maths', 'FMAT', 'Elective'),
('Literature', 'LIT', 'Elective'),
('Government', 'GOV', 'Elective'),
('CRK', 'CRK', 'Elective'),
('IRK', 'IRK', 'Elective'),
('Agric Science', 'AGRIC', 'Elective'),
('Home Economics', 'HEC', 'Elective'),
('Business Studies', 'BUS', 'Elective'),
('Accounting', 'ACC', 'Elective'),
('Commerce', 'COMM', 'Elective'),
('Computer Science', 'COMP', 'Elective'),
('Physical Education', 'PE', 'Elective'),
('Music', 'MUSIC', 'Elective'),
('Fine Arts', 'ART', 'Elective'),
('French', 'FREN', 'Elective'),
('Yoruba', 'YOR', 'Elective'),
('Hausa', 'HAU', 'Elective'),
('Igbo', 'IGB', 'Elective'),
('Civic Education', 'CIVIC', 'Core'),
('Social Studies', 'SOC', 'Core');

INSERT INTO grading_system (min_score, max_score, grade, points, remark) VALUES 
(75, 100, 'A', 5.0, 'Excellent'),
(70, 74, 'B', 4.0, 'Very Good'),
(65, 69, 'C', 3.0, 'Good'),
(60, 64, 'D', 2.0, 'Fair'),
(50, 59, 'E', 1.0, 'Pass'),
(0, 49, 'F', 0.0, 'Fail');

INSERT INTO admin (username, password, email, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@school.com', 'System Administrator', 'admin');

-- Create Triggers for Automatic Calculations
CREATE TRIGGER calculate_result_total 
BEFORE INSERT ON results
BEGIN
    SET NEW.total = COALESCE(NEW.test1, 0) + COALESCE(NEW.test2, 0) + COALESCE(NEW.exam, 0);
    
    UPDATE results 
    SET grade = (SELECT grade FROM grading_system 
                WHERE NEW.total BETWEEN min_score AND max_score),
        remark = (SELECT remark FROM grading_system 
                 WHERE NEW.total BETWEEN min_score AND max_score)
    WHERE id = NEW.id;
END;

CREATE TRIGGER update_result_total 
AFTER UPDATE OF test1, test2, exam ON results
BEGIN
    UPDATE results 
    SET total = COALESCE(NEW.test1, 0) + COALESCE(NEW.test2, 0) + COALESCE(NEW.exam, 0),
        grade = (SELECT grade FROM grading_system 
                WHERE (COALESCE(NEW.test1, 0) + COALESCE(NEW.test2, 0) + COALESCE(NEW.exam, 0)) BETWEEN min_score AND max_score),
        remark = (SELECT remark FROM grading_system 
                 WHERE (COALESCE(NEW.test1, 0) + COALESCE(NEW.test2, 0) + COALESCE(NEW.exam, 0)) BETWEEN min_score AND max_score)
    WHERE id = NEW.id;
END;
```

## Key Integration Points:

### 1. **Student Data Integration**
- Combined `students` table includes both fee management and result management fields
- Added `reg_number` from result system to fee management students
- Maintained `family_id` relationship for fee management

### 2. **Academic Structure Unification**
- Enhanced `terms` table to include `session_id` and date ranges
- Added `level` and `stream` to classes for better categorization
- Maintained consistent academic session structure

### 3. **Result Management Features**
- Preserved all result tables: `results`, `affective_skills`, `psychomotor_skills`, `gpa_records`
- Added automatic grade calculation using triggers
- Included comprehensive subject catalog

### 4. **Fee Management Preservation**
- Kept all original fee management tables: `fee_declarations`, `payments`
- Maintained relationships with academic sessions and terms

### 5. **User Management Enhancement**
- Combined admin systems with role-based permissions
- Added teacher management capabilities
- Maintained audit logging

### 6. **Data Integrity**
- Added proper foreign key constraints
- Implemented unique constraints to prevent duplicates
- Included automatic timestamping

## Migration Script (MySQL to SQLite):

To migrate your existing MySQL result data to this new unified SQLite database, you would:

1. Export data from MySQL tables
2. Transform data to match the new schema
3. Import into SQLite using appropriate tools or scripts

## Sample Queries for Integrated System:

```sql
-- Get student results with fee status
SELECT 
    s.full_name,
    s.reg_number,
    c.name as class_name,
    r.total as total_score,
    r.grade,
    (SELECT SUM(amount) FROM payments p 
     WHERE p.student_id = s.id AND p.session_id = 1 AND p.term_id = 1) as fees_paid,
    (SELECT SUM(amount) FROM fee_declarations fd 
     WHERE fd.class_id = s.current_class_id AND fd.session_id = 1 AND fd.term_id = 1) as total_fees
FROM results r
JOIN students s ON r.student_id = s.id
JOIN classes c ON s.current_class_id = c.id
WHERE r.session_id = 1 AND r.term_id = 1
GROUP BY s.id;

-- Get class performance summary with fee compliance
SELECT 
    c.name as class_name,
    COUNT(DISTINCT s.id) as total_students,
    AVG(r.total) as average_score,
    SUM(CASE WHEN p.amount IS NOT NULL THEN 1 ELSE 0 END) as students_with_fees_paid,
    (SUM(CASE WHEN p.amount IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(DISTINCT s.id)) as fee_compliance_percentage
FROM classes c
LEFT JOIN students s ON c.id = s.current_class_id
LEFT JOIN results r ON s.id = r.student_id AND r.session_id = 1 AND r.term_id = 1
LEFT JOIN payments p ON s.id = p.student_id AND p.session_id = 1 AND p.term_id = 1
GROUP BY c.id;
```
