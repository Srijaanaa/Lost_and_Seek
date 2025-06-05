# Lost & Seek: Reuniting Lost Items with Their Owners

## Overview
"Lost & Seek" is a web-based platform designed to reconnect lost items with their rightful owners through an accessible and streamlined system. This repository contains the source code for the platform, along with a presentation (`Lost-and-Seek.pptx`) and related media outlining the project’s purpose, features, and implementation.

## Folder Structure
The repository is organized as follows:
- **`src/`**: Core source code for the web platform.
  - `index.html`: Main entry point for the front-end.
  - `css/`: Stylesheets (e.g., `styles.css`) for the user interface.
  - `js/`: JavaScript files for front-end interactivity (e.g., `app.js`).
  - `php/`: Back-end scripts for handling user authentication, item reporting, and database operations.
- **`tools/`**: Configuration files for tools like VS Code, Draw.io, or GitHub.

## Prerequisites
To use the code:
- **Software**:
  - PHP 7.4+ (for back-end)
  - MySQL 5.7+ (for database)
  - VS Code (recommended for development)
  - Web browser (e.g., Chrome, Firefox)
- **Knowledge**: Basic understanding of HTML, CSS, JavaScript, PHP, and MySQL.

## Setup Instructions
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/Srijaanaa/lost-and-seek.git
   ```
2. **Navigate to the Project Directory**:
   ```bash
   cd lost-and-seek
   ```
3. **Set Up the Database**
4. 
5. **Install Dependencies**:
   ```bash
   composer install  # For PHP dependencies
   npm install       # For front-end dependencies, if applicable
   ```
6. **Run the Application**:
   - Configure a web server (e.g., Apache) to serve the `src/` directory.
   - Start the server and access the platform at `http://localhost`.

## Usage
- **Users**:
  - Register or log in (via credentials ).
  - Use the dashboard to report lost or found items and track progress.
  - Search the database for matching items.
- **Admins**:
  - Log in to the admin panel to manage items and confirm matches.
  - Monitor platform statistics via the admin dashboard.

## Target Audience
- **Developers**: Interested in building or contributing to a lost-and-found platform.
- **Students**: Learning web development with HTML, CSS, JavaScript, PHP, and MySQL.
- **Stakeholders**: Exploring the platform’s features and potential.

## Database Design
The platform uses a MySQL database with the following tables:
- `lost_and_found`: Main database.
- `users`: Stores user and admin details (id, username, email, password).
- `items`: Stores item details (id, user_id, name, description).
- `notifications`: Stores notification details (item_id, user_id, message).
- `matched_items`: Tracks matched items (lost_id, found_id, admin_id, status).

## System Architecture
The platform follows a client-server architecture:
- **Front-end**: Built with HTML, CSS, and JavaScript for user and admin interfaces.
- **Back-end**: PHP handles requests, authentication, and database operations.
- **Database**: MySQL stores user and item data.

## Tools and Technologies
- **Development**: VS Code, Draw.io (for diagrams), GitHub (for version control).
- **Front-end**: HTML, CSS, JavaScript.
- **Back-end**: PHP.
- **Database**: MySQL.

## Future Directions
- Integrate AI for image recognition and automated item matching.
- Develop a mobile app for broader accessibility.
- Expand the platform to new regions and communities.

## Contributing
Contributions are welcome! To contribute:
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature-name`).
3. Commit changes (`git commit -m "Add feature"`).
4. Push to the branch (`git push origin feature-name`).
5. Open a pull request.

## Credits
- **Presenters**: Ranjita Rai, Srijana Lohani

## License
[MIT License](LICENSE)

## Contact
For questions or support, contact [srijanalohani02@gmail.com] or open an issue on GitHub.
