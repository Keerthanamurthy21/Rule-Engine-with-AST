# Rule-Engine-with-AST
Simple 3 Tier rule engine application

Rule Engine Project:
The Rule Engine Project is an application designed to manage rules dynamically. It allows users to create, combine, and evaluate rules using logical operators, offering a robust framework for rule-based operations.

Setup Instructions
1. Download and Install XAMPP/WAMP/MAMP:

2. Make sure you have a local server setup with PHP and MySQL.
3. Clone or Download the Repository:
Extract the contents into the htdocs directory (for XAMPP) or the equivalent for other setups.
Database Setup:

4. Create a MySQL database for the project.
Import the SQL file (provided in the project) to create necessary tables.
Configure the database.php file in the api/ directory with your database credentials.

5. Run the Application:
Start the local server.
Open the browser and navigate to http://localhost/rule_engine_project/public/.
Dependencies
PHP: Ensure PHP is installed and configured in the server.
MySQL: Required for database operations.
jQuery: The project uses jQuery for some JavaScript operations.
Bootstrap: For front-end styling (add the appropriate Bootstrap CSS/JS files if missing).

6. Backend Configuration:
Update the database configuration in api/database.php to match your local database settings.
The PHP scripts in the api/ directory handle server-side operations. Make sure the paths in the JavaScript files are correctly mapped to these scripts.
Front-end Configuration:

7. The front-end code is located in the public/ directory.
The main entry point is index.html, which uses JavaScript to interact with the backend.

Design Choices
Separation of Concerns: The project follows a structured approach with separate directories for API, configuration, and public resources.
RESTful API: The backend scripts use RESTful practices for performing operations.
Dynamic Rule Management: Rules are stored in the database and managed dynamically, providing flexibility.

Known Issues
Ensure all API endpoints are correctly configured in the front-end JavaScript.
If rules are not displayed, check the database connection and table contents.
