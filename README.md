# TicketHub

TicketHub is a comprehensive WordPress plugin for managing support tickets, FAQs, documentation, and change logs. Streamline your customer support process with ease.

## Description

TicketHub is a powerful and user-friendly plugin designed to help you efficiently manage your support system. It provides a range of features to handle tickets, FAQs, documentation, and change logs directly from your WordPress site.

## Features

- Support ticket management
- FAQ system
- Documentation management
- Change log tracking
- Custom form fields
- File attachments
- User roles and permissions
- Email notifications
- Responsive design

## Installation

1. Upload the plugin files to the `/wp-content/plugins/ticket-hub` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the TicketHub->Settings screen to configure the plugin.

## Usage

TicketHub provides several shortcodes to embed functionality on your WordPress pages:

- `[thub_form]` - Embed the ticket submission form
- `[thub_tickets]` - Display a list of all tickets
- `[thub_faqs]` - Display the FAQ section
- `[thub_documentation]` - Display the documentation section
- `[thub_changelog]` - Display the change log section
- `[thub_profile]` - Display the user's profile and their tickets

## Development

### Setting up the development environment

1. Clone this repository
2. Install Docker and Docker Compose
3. Run `docker-compose up -d` to start the WordPress development environment
4. Access the WordPress site at `http://localhost:8000`

The plugin files are mounted in the Docker container, allowing for real-time development and testing.

### Installing npm packages and using Gulp

TicketHub uses npm for package management and Gulp for asset compilation. Follow these steps to set up the development environment:

1. Ensure you have Node.js and npm installed on your system.
2. Navigate to the plugin directory in your terminal.
3. Run `npm install` to install the required packages.

Once the packages are installed, you can use the following Gulp commands:

- `gulp admin-js`: Concatenate and minify admin JavaScript files
- `gulp public-js`: Concatenate and minify public JavaScript files
- `gulp admin-css`: Concatenate and minify admin CSS files
- `gulp public-css`: Concatenate and minify public CSS files
- `gulp`: Run all of the above tasks

To watch for changes and automatically recompile assets during development, you can add a watch task to the gulpfile.js and run `gulp watch`.

### Gulp tasks

The gulpfile.js includes the following tasks:

- `admin-js`: Processes JavaScript files in `ticket-hub/js/admin/`
- `public-js`: Processes JavaScript files in `ticket-hub/js/public/`
- `admin-css`: Processes CSS files in `ticket-hub/css/admin/`
- `public-css`: Processes CSS files in `ticket-hub/css/public/`

Each task concatenates the source files, creates both unminified and minified versions, and places them in the `ticket-hub/dist/` directory.

### Checking outgoing emails with MailHog

TicketHub uses MailHog for email testing during development. MailHog is a tool that sets up a fake SMTP server and provides a web interface to view outgoing emails.

To use MailHog:

1. MailHog is already configured in the `docker-compose.yml` file and will start automatically when you run `docker-compose up -d`.

2. Access the MailHog web interface at `http://localhost:8025`. This interface allows you to view all emails sent by the application during development.

3. Any emails sent by WordPress or the TicketHub plugin will be captured by MailHog and displayed in this interface, rather than being actually sent to the recipient.

4. You can view the content of the emails, including HTML structure, plain text content, and headers.

5. MailHog also allows you to release emails to real SMTP servers if needed, though this should be used cautiously in a development environment.

Using MailHog ensures that no test emails are accidentally sent to real email addresses during development and provides an easy way to verify the content and formatting of outgoing emails.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL2 License - see the [LICENSE](LICENSE) file for details.

## Author

Mondula GmbH - [https://mondula.com](https://mondula.com)
