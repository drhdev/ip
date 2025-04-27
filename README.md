# IP

**A secure, lightweight PHP API to retrieve visitor IP information and store a history of the last 10 visits.**  
No external services required. Fully self-hosted. Works on any PHP-enabled server.

---

## Features

- 📡 Returns IP address, User-Agent, and timestamp
- 🛡️ Secure input validation with no external dependencies
- 📝 Stores the **last 10 visitor entries** locally in a JSON file
- 🌐 Simple HTML frontends:
  - **IP Info** (current visitor)
  - **History** (last 10 visits)
- ⚡ Extremely fast, minimal, and privacy-friendly
- 🎯 Ideal for webhook systems, dashboards, monitoring, and internal tools

---

## Installation

1. Clone or download the repository:
   ```bash
   git clone https://github.com/drhdev/ip.git
   ```
2. Upload the files (`ip.php`, `index.html`, `history.html`) to your PHP-enabled web server.
3. Ensure the server has permission to create and write `history.json` (this file will be created automatically).

No database setup is needed.

---

## How to Use

### Accessing the API directly

To retrieve the current visitor’s information, send a `GET` request to:

```text
https://yourdomain.com/ip.php
```

#### Response

The API returns a JSON object:

```json
{
  "ip": "203.0.113.1",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)...",
  "timestamptz": "2025-04-27T12:34:56+00:00"
}
```

Fields:

| Field | Description |
|:---|:---|
| `ip` | Visitor’s IP address (validated) |
| `user_agent` | Visitor’s browser User-Agent |
| `timestamptz` | Current UTC timestamp in ISO 8601 format |

---

### Retrieve the visit history

To retrieve the last 10 visitor entries, send a `GET` request to:

```text
https://yourdomain.com/ip.php?history
```

#### Response

Returns a JSON array containing up to 10 entries:

```json
[
  {
    "ip": "203.0.113.1",
    "user_agent": "...",
    "timestamptz": "2025-04-27T12:34:56+00:00"
  },
  {
    "ip": "198.51.100.2",
    "user_agent": "...",
    "timestamptz": "2025-04-27T11:22:10+00:00"
  }
]
```

---

### Using the HTML Frontends

- **IP Info Page**:  
  `index.html` fetches and displays the current visitor’s information.

- **History Page**:  
  `history.html` fetches and displays the last 10 visitor entries.

Both pages dynamically show the server's domain name in the page title.

---

## Security and Best Practices

- Only allowed operations (`history` GET parameter) are processed.
- IP addresses are validated using PHP’s `FILTER_VALIDATE_IP`.
- User input is sanitized and strictly validated.
- Errors are hidden from users (`display_errors=0`).
- All responses are UTF-8 encoded and properly formatted as JSON.

---

## Requirements

- PHP 7.4 or newer
- A PHP-enabled web server (Apache, Nginx, etc.)
- Write permissions for the `history.json` file

---

## License

This project is licensed under the MIT License.  
Feel free to use, modify, and distribute. Attribution is appreciated.

---

## Author

**[drhdev](https://github.com/drhdev)**

---

# ➡️ Project Structure Overview

| File | Purpose |
|:---|:---|
| `ip.php` | Main API endpoint |
| `index.html` | Displays current visitor information |
| `history.html` | Displays history of the last 10 visits |
| `history.json` | Stores visitor history (auto-created, not manually edited) |
