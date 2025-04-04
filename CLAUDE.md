# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build/Lint/Test Commands
- No build process required - this is a WordPress child theme
- To test: Install in wp-content/themes and activate through WordPress admin
- Verify changes directly in browser by refreshing the relevant pages
- Use WordPress debugging: define('WP_DEBUG', true); in wp-config.php

## Code Style Guidelines
- **Naming**: Use snake_case for functions with prefix `minimog_child_` or `inksplosion_`
- **Hooks**: Follow WordPress naming: `{theme}_{action}_{component}`
- **Templates**: Match parent theme structure for overrides
- **Security**: Always include ABSPATH checks, nonce verification, capability checks
- **Escaping**: Use WordPress esc_* functions for all output
- **CSS**: Follow BEM-like naming conventions
- **WooCommerce**: Use proper hooks and endpoint registration patterns
- **PHP**: WordPress coding standards (https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- **Error Handling**: Use WooCommerce notice system for user messages