<?php

namespace App\DataFixtures;

use App\Entity\Exercise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $exercises = $this->getExercisesData();

        foreach ($exercises as $exerciseData) {
            $exercise = new Exercise();
            $exercise->setTitle($exerciseData['title']);
            $exercise->setDescription($exerciseData['description']);
            $exercise->setInstructions($exerciseData['instructions']);
            $exercise->setDatabaseType($exerciseData['database_type']);
            $exercise->setInitialSchema($exerciseData['initial_schema']);
            $exercise->setExpectedResult($exerciseData['expected_result']);
            $exercise->setDifficulty($exerciseData['difficulty']);
            $exercise->setOrderNumber($exerciseData['order_number']);

            $manager->persist($exercise);
        }

        $manager->flush();
    }

    private function getExercisesData(): array
    {
        return [
            // MySQL Exercises
            [
                'title' => 'MySQL Basic SELECT',
                'description' => 'Learn basic SELECT queries in MySQL',
                'instructions' => 'Write a SQL query to select all columns from the employees table where department is "Sales"',
                'database_type' => 'mysql',
                'initial_schema' => "CREATE TABLE employees (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    department VARCHAR(50) NOT NULL,
                    salary DECIMAL(10,2),
                    hire_date DATE
                );

                INSERT INTO employees (name, department, salary, hire_date) VALUES
                ('John Doe', 'Sales', 50000.00, '2023-01-15'),
                ('Jane Smith', 'Marketing', 55000.00, '2023-02-20'),
                ('Bob Johnson', 'Sales', 48000.00, '2023-03-10'),
                ('Alice Brown', 'HR', 52000.00, '2023-04-05'),
                ('Charlie Wilson', 'Sales', 51000.00, '2023-05-12');",
                'expected_result' => '[{"id":1,"name":"John Doe","department":"Sales","salary":"50000.00","hire_date":"2023-01-15"},{"id":3,"name":"Bob Johnson","department":"Sales","salary":"48000.00","hire_date":"2023-03-10"},{"id":5,"name":"Charlie Wilson","department":"Sales","salary":"51000.00","hire_date":"2023-05-12"}]',
                'difficulty' => 1,
                'order_number' => 1,
            ],
            [
                'title' => 'MySQL Aggregate Functions',
                'description' => 'Practice using aggregate functions in MySQL',
                'instructions' => 'Calculate the average salary for each department',
                'database_type' => 'mysql',
                'initial_schema' => "CREATE TABLE employees (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    department VARCHAR(50) NOT NULL,
                    salary DECIMAL(10,2)
                );

                INSERT INTO employees (name, department, salary) VALUES
                ('John Doe', 'Sales', 50000.00),
                ('Jane Smith', 'Marketing', 55000.00),
                ('Bob Johnson', 'Sales', 48000.00),
                ('Alice Brown', 'HR', 52000.00),
                ('Charlie Wilson', 'Marketing', 53000.00),
                ('David Lee', 'HR', 51000.00);",
                'expected_result' => '[{"department":"Sales","avg_salary":"49000.000000"},{"department":"Marketing","avg_salary":"54000.000000"},{"department":"HR","avg_salary":"51500.000000"}]',
                'difficulty' => 2,
                'order_number' => 2,
            ],
            [
                'title' => 'MySQL JOIN Operation',
                'description' => 'Learn to join multiple tables in MySQL',
                'instructions' => 'Write a query to get employee names along with their project names. Include all employees even if they don\'t have a project.',
                'database_type' => 'mysql',
                'initial_schema' => "CREATE TABLE employees (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL
                );

                CREATE TABLE projects (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    employee_id INT,
                    FOREIGN KEY (employee_id) REFERENCES employees(id)
                );

                INSERT INTO employees (name) VALUES
                ('John Doe'),
                ('Jane Smith'),
                ('Bob Johnson');

                INSERT INTO projects (name, employee_id) VALUES
                ('Website Redesign', 1),
                ('Mobile App', 2),
                ('Database Migration', 1);",
                'expected_result' => '[{"employee_name":"John Doe","project_name":"Website Redesign"},{"employee_name":"John Doe","project_name":"Database Migration"},{"employee_name":"Jane Smith","project_name":"Mobile App"},{"employee_name":"Bob Johnson","project_name":null}]',
                'difficulty' => 3,
                'order_number' => 3,
            ],

            // PostgreSQL Exercises
            [
                'title' => 'PostgreSQL Basic SELECT',
                'description' => 'Learn basic SELECT queries in PostgreSQL',
                'instructions' => 'Select all customers from London ordered by name',
                'database_type' => 'postgresql',
                'initial_schema' => "CREATE TABLE customers (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    city VARCHAR(50) NOT NULL,
                    country VARCHAR(50) NOT NULL
                );

                INSERT INTO customers (name, city, country) VALUES
                ('John Smith', 'London', 'UK'),
                ('Maria Garcia', 'Madrid', 'Spain'),
                ('David Brown', 'London', 'UK'),
                ('Sarah Johnson', 'Paris', 'France'),
                ('Robert Wilson', 'London', 'UK');",
                'expected_result' => '[{"id":1,"name":"John Smith","city":"London","country":"UK"},{"id":3,"name":"David Brown","city":"London","country":"UK"},{"id":5,"name":"Robert Wilson","city":"London","country":"UK"}]',
                'difficulty' => 1,
                'order_number' => 4,
            ],
            [
                'title' => 'PostgreSQL Window Functions',
                'description' => 'Practice using window functions in PostgreSQL',
                'instructions' => 'Rank products by price within each category',
                'database_type' => 'postgresql',
                'initial_schema' => "CREATE TABLE products (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    category VARCHAR(50) NOT NULL,
                    price DECIMAL(10,2) NOT NULL
                );

                INSERT INTO products (name, category, price) VALUES
                ('Laptop', 'Electronics', 999.99),
                ('Smartphone', 'Electronics', 699.99),
                ('Tablet', 'Electronics', 499.99),
                ('Chair', 'Furniture', 149.99),
                ('Desk', 'Furniture', 299.99),
                ('Monitor', 'Electronics', 249.99);",
                'expected_result' => '[{"id":1,"name":"Laptop","category":"Electronics","price":"999.99","rank":1},{"id":2,"name":"Smartphone","category":"Electronics","price":"699.99","rank":2},{"id":3,"name":"Tablet","category":"Electronics","price":"499.99","rank":3},{"id":6,"name":"Monitor","category":"Electronics","price":"249.99","rank":4},{"id":5,"name":"Desk","category":"Furniture","price":"299.99","rank":1},{"id":4,"name":"Chair","category":"Furniture","price":"149.99","rank":2}]',
                'difficulty' => 3,
                'order_number' => 5,
            ],
            [
                'title' => 'PostgreSQL Common Table Expressions',
                'description' => 'Learn to use CTEs in PostgreSQL',
                'instructions' => 'Find the total sales amount for each employee using a CTE',
                'database_type' => 'postgresql',
                'initial_schema' => "CREATE TABLE employees (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(100) NOT NULL
                );

                CREATE TABLE sales (
                    id SERIAL PRIMARY KEY,
                    employee_id INT NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    sale_date DATE NOT NULL,
                    FOREIGN KEY (employee_id) REFERENCES employees(id)
                );

                INSERT INTO employees (name) VALUES
                ('John Doe'),
                ('Jane Smith');

                INSERT INTO sales (employee_id, amount, sale_date) VALUES
                (1, 1000.00, '2024-01-15'),
                (1, 1500.00, '2024-01-20'),
                (2, 800.00, '2024-01-18'),
                (1, 1200.00, '2024-02-05'),
                (2, 900.00, '2024-02-10');",
                'expected_result' => '[{"employee_id":1,"total_sales":"3700.00"},{"employee_id":2,"total_sales":"1700.00"}]',
                'difficulty' => 2,
                'order_number' => 6,
            ],

            // SQLite Exercises
            [
                'title' => 'SQLite Basic CRUD',
                'description' => 'Learn basic CRUD operations in SQLite',
                'instructions' => 'Update the price of all books in the "Fiction" category by 10%',
                'database_type' => 'sqlite',
                'initial_schema' => "CREATE TABLE books (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    author TEXT NOT NULL,
                    category TEXT NOT NULL,
                    price REAL NOT NULL
                );

                INSERT INTO books (title, author, category, price) VALUES
                ('The Great Gatsby', 'F. Scott Fitzgerald', 'Fiction', 10.99),
                ('To Kill a Mockingbird', 'Harper Lee', 'Fiction', 12.99),
                ('1984', 'George Orwell', 'Fiction', 9.99),
                ('The Selfish Gene', 'Richard Dawkins', 'Science', 15.99),
                ('A Brief History of Time', 'Stephen Hawking', 'Science', 14.99);",
                'expected_result' => '[{"id":1,"title":"The Great Gatsby","author":"F. Scott Fitzgerald","category":"Fiction","price":12.089},{"id":2,"title":"To Kill a Mockingbird","author":"Harper Lee","category":"Fiction","price":14.289},{"id":3,"title":"1984","author":"George Orwell","category":"Fiction","price":10.989}]',
                'difficulty' => 2,
                'order_number' => 7,
            ],
            [
                'title' => 'SQLite Date Functions',
                'description' => 'Practice using date functions in SQLite',
                'instructions' => 'Find orders placed in the last 30 days',
                'database_type' => 'sqlite',
                'initial_schema' => "CREATE TABLE orders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_name TEXT NOT NULL,
                    order_date TEXT NOT NULL,
                    amount REAL NOT NULL
                );

                INSERT INTO orders (customer_name, order_date, amount) VALUES
                ('John Smith', '2024-01-01', 100.00),
                ('Jane Doe', '2024-01-15', 150.00),
                ('Bob Johnson', '2024-01-20', 200.00),
                ('Alice Brown', '2023-12-15', 120.00),
                ('Charlie Wilson', '2024-01-25', 180.00);",
                'expected_result' => '[{"id":2,"customer_name":"Jane Doe","order_date":"2024-01-15","amount":150.0},{"id":3,"customer_name":"Bob Johnson","order_date":"2024-01-20","amount":200.0},{"id":5,"customer_name":"Charlie Wilson","order_date":"2024-01-25","amount":180.0}]',
                'difficulty' => 2,
                'order_number' => 8,
            ],
            [
                'title' => 'SQLite Subqueries',
                'description' => 'Learn to use subqueries in SQLite',
                'instructions' => 'Find products with price above the average price',
                'database_type' => 'sqlite',
                'initial_schema' => "CREATE TABLE products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    price REAL NOT NULL,
                    category TEXT NOT NULL
                );

                INSERT INTO products (name, price, category) VALUES
                ('Laptop', 999.99, 'Electronics'),
                ('Mouse', 25.50, 'Electronics'),
                ('Keyboard', 75.00, 'Electronics'),
                ('Chair', 149.99, 'Furniture'),
                ('Desk', 299.99, 'Furniture'),
                ('Monitor', 249.99, 'Electronics');",
                'expected_result' => '[{"id":1,"name":"Laptop","price":999.99,"category":"Electronics"},{"id":4,"name":"Chair","price":149.99,"category":"Furniture"},{"id":5,"name":"Desk","price":299.99,"category":"Furniture"},{"id":6,"name":"Monitor","price":249.99,"category":"Electronics"}]',
                'difficulty' => 3,
                'order_number' => 9,
            ],
            [
                'title' => 'SQLite Transactions',
                'description' => 'Learn transaction management in SQLite',
                'instructions' => 'Transfer $100 from account 1 to account 2 in a single transaction',
                'database_type' => 'sqlite',
                'initial_schema' => "CREATE TABLE accounts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    account_number TEXT NOT NULL UNIQUE,
                    balance REAL NOT NULL DEFAULT 0.0
                );

                INSERT INTO accounts (account_number, balance) VALUES
                ('ACC001', 1000.00),
                ('ACC002', 500.00);",
                'expected_result' => '[{"id":1,"account_number":"ACC001","balance":900.0},{"id":2,"account_number":"ACC002","balance":600.0}]',
                'difficulty' => 3,
                'order_number' => 10,
            ],
        ];
    }
}
