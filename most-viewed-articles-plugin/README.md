# Most Viewed Articles WordPress Plugin

A WordPress plugin that creates a widget displaying the most viewed articles with a tabbed interface for "This Week" and "This Month" timeframes.

## Features

- **Tabbed Interface**: Switch between "This Week" and "This Month" views without page reload
- **View Tracking**: Accurately tracks post views with cache compatibility
- **IP-based Limiting**: Prevents double-counting by limiting to 1 view per hour per IP address
- **Rolling Windows**: Uses 7-day and 30-day rolling windows for calculations  
- **AJAX Loading**: Smooth tab switching with AJAX content loading
- **Responsive Design**: Mobile-friendly interface
- **WordPress Standards**: Follows WordPress coding standards and best practices
- **Cache Compatible**: Works with full-page caching solutions like WP Rocket

## Installation

### Manual Installation

1. **Download the plugin files**:
   - `most-viewed-articles.php` (main plugin file)
   - `assets/js/most-viewed-articles.js` (JavaScript file)
   - `assets/css/most-viewed-articles.css` (CSS file)

2. **Create plugin directory**:
   ```
   wp-content/plugins/most-viewed-articles/
   ```

3. **Upload files with this structure**:
   ```
   most-viewed-articles/
   ├── most-viewed-articles.php
   ├── assets/
   │   ├── js/
   │   │   ├── most-viewed-articles.js
   │   │   └── most-viewed-articles-mobile.js
   │   └── css/
   │       ├── most-viewed-articles.css
   │       └── most-viewed-articles-mobile.css
   └── README.md
   ```

4. **Activate the plugin**:
   - Go to WordPress Admin → Plugins
   - Find "Most Viewed Articles" and click "Activate"

### Using the Widget

1. **Add to sidebar**:
   - Go to Appearance → Widgets
   - Find "Most Viewed Articles" widget
   - Drag to desired widget area (sidebar, footer, etc.)

2. **Configure widget**:
   - Set custom title (optional)
   - Save widget settings

## Technical Implementation

### View Tracking System

The plugin implements a sophisticated view tracking system that:

- **Tracks only standard posts**: Only counts views for `post` post type
- **Prevents duplicate counting**: Uses transients to limit 1 view per IP per hour
- **Cache compatibility**: Works with full-page caching by tracking on backend
- **Privacy-focused**: IP addresses are hashed using SHA-256 for storage
- **Rolling windows**: Maintains 30 days of view data, automatically purging older entries

### Database Storage

The plugin uses WordPress post meta to store view data:

- `_mva_view_count`: Total view count (for reference)
- `_mva_views_data`: Array of timestamped views for time-based calculations

### AJAX Implementation

- Tab switching uses AJAX to load content without page refresh
- Nonce verification for security
- Error handling for failed requests
- Loading states for better UX

## File Structure

```
   most-viewed-articles/
   ├── most-viewed-articles.php                   # Main Plugin File
   ├── assets/
   │   ├── js/
   │   │   ├── most-viewed-articles.js            # Frontend javascript
   │   │   └── most-viewed-articles-mobile.js     # Frontend js - Mobile optimization
   │   └── css/
   │       ├── most-viewed-articles.css           # Widget Styling
   │       └── most-viewed-articles-mobile.css    # Widget Mobile styling 
   └── README.md                                  # Documentation
```

## Hooks and Filters

### Action Hooks Used
- `init`: Initialize plugin and load text domain
- `wp_enqueue_scripts`: Enqueue scripts and styles
- `wp`: Track post views on frontend
- `widgets_init`: Register widget
- `wp_ajax_*`: Handle AJAX requests

### WordPress Standards Compliance

- **Coding Standards**: Follows WordPress PHP Coding Standards
- **Security**: Uses nonces, sanitization, and escaping
- **Internationalization**: Ready for translation with text domain
- **Accessibility**: Semantic HTML and proper ARIA attributes
- **Performance**: Efficient queries and caching mechanisms

## Browser Compatibility

- Chrome/Chromium (latest)
- Firefox (latest) 
- Safari (latest)
- Edge (latest)
- Internet Explorer 11+

## Performance Considerations

- **Minimal Database Impact**: Uses efficient queries and post meta storage
- **Transient Caching**: Prevents excessive database writes for duplicate views
- **AJAX Loading**: Only loads data when needed
- **Automatic Cleanup**: Purges old view data automatically
- **Cache Friendly**: Compatible with object caching and full-page caching

## Customization

### Styling
Modify `assets/css/most-viewed-articles.css` or `most-viewed-articles-mobile.css` to customize appearance:

```css
/* Custom colors */
.mva-tab.active {
    color: #your-brand-color;
    border-bottom-color: #your-brand-color;
}

/* Custom fonts */
.mva-article-title {
    font-family: 'Your Font', sans-serif;
}
```

### Functionality
Use WordPress filters to modify behavior:

```php
// Modify article count (default: 10)
add_filter('mva_article_limit', function() {
    return 15;
});

// Modify timeframes
add_filter('mva_week_days', function() {
    return 14; // 2 weeks instead of 1
});
```

## Troubleshooting

### Common Issues

**Widget not appearing:**
- Ensure plugin is activated
- Check that widget is added to a widget area
- Verify theme supports widgets

**Views not tracking:**
- Check that you're viewing single posts (not pages)
- Verify JavaScript is enabled
- Check for JavaScript errors in browser console

**AJAX not working:**
- Ensure jQuery is loaded
- Check for JavaScript conflicts
- Verify AJAX URL is correct

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For issues and questions:

1. Check this README for common solutions
2. Enable debug mode to identify specific errors
3. Check browser console for JavaScript errors
4. Verify all files are uploaded correctly

## Changelog

### Version 1.0.0
- Initial release
- Tabbed interface with This Week/This Month views
- View tracking with IP-based duplicate prevention
- AJAX content loading
- Responsive design
- WordPress coding standards compliance

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed following WordPress coding standards and best practices for optimal performance and compatibility.