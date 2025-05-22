# PostRocket

A powerful WordPress plugin for job duplication and management with advanced features. Optimized for performance and background processing.

## Features

### Job Duplicator
- Duplicate jobs across multiple locations with either manual or auto mode
- Process up to 50 locations immediately
- Process up to 500 locations in the background
- Schedule duplicated jobs for future publishing

### Background Processing
- Robust background processing using WordPress cron
- Process jobs in batches (25 per batch) to prevent server overload
- Real-time status updates on queue progress
- Manual triggering of background processing
- Error handling and reporting

### Location Manager
- Create, edit, and delete named lists of locations
- Store up to 50 locations per list
- Auto-trim and deduplicate locations
- Clean interface for managing location lists

### Dashboard
- View key metrics like total job count, active job count, jobs across all locations, etc.
- See recent jobs in a table
- Visualize location distribution in a pie chart

### Visibility Settings
- Option to hide duplicate jobs from frontend
- Option to add noindex meta tags to duplicated jobs

## Installation

1. Upload the `postrocket` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the PostRocket settings page to configure your API key and preferences

## Usage

### Job Duplication

1. Go to the Job Duplicator page in the PostRocket menu
2. Select a job and company
3. Choose either Manual Mode (enter locations directly) or Auto Mode (select a saved location list)
4. Optionally set a future publish date
5. Click "Duplicate Job" to start the process

### Location Management

1. Go to the Location Manager page in the PostRocket menu
2. Create a new location list by entering a name and list of locations
3. View, edit, or delete existing location lists

### Background Processing

1. Go to the Background Processing page in the PostRocket menu
2. View the status of current processing tasks
3. Manually trigger the queue processing if needed

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, please contact us at support@example.com or visit our website at https://example.com/support.

## Changelog

### 1.0.0
- Initial release